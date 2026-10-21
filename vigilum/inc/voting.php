<?php
/**
 * Civic voting for initiatives.
 *
 * One vote per initiative per visitor (cookie + hashed IP).
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', 'vigilum_register_vote_route' );
function vigilum_register_vote_route() {
	register_rest_route( 'vigilum/v1', '/vote', array(
		'methods'             => 'POST',
		'callback'            => 'vigilum_handle_vote',
		'permission_callback' => '__return_true',
		'args'                => array(
			'id' => array(
				'required'          => true,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
		),
	) );
}

/**
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function vigilum_handle_vote( $request ) {
	$id = (int) $request->get_param( 'id' );
	if ( ! $id || 'vigilum_initiative' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
		return new WP_Error( 'vigilum_invalid', __( 'Инициатива не найдена.', 'vigilum' ), array( 'status' => 404 ) );
	}

	$status = (string) get_post_meta( $id, '_vigilum_status', true );
	if ( 'closed' === $status ) {
		return new WP_Error( 'vigilum_closed', __( 'Голосование по этой инициативе закрыто.', 'vigilum' ), array( 'status' => 409 ) );
	}

	$ip      = vigilum_voter_ip();
	$cookie  = 'vigilum_voted_' . $id;
	$has_ck  = ! empty( $_COOKIE[ $cookie ] );
	$ip_key  = 'vigilum_vote_' . $id . '_' . $ip;
	$has_ip  = (bool) get_transient( $ip_key );

	if ( $has_ck || $has_ip ) {
		return new WP_Error( 'vigilum_double', __( 'Вы уже поддержали эту инициативу.', 'vigilum' ), array( 'status' => 409 ) );
	}

	$hour_key = 'vigilum_rate_' . $ip;
	$rate     = (int) get_transient( $hour_key );
	if ( $rate >= 20 ) {
		return new WP_Error( 'vigilum_rate', __( 'Слишком много попыток. Подождите час.', 'vigilum' ), array( 'status' => 429 ) );
	}

	$votes = (int) get_post_meta( $id, '_vigilum_votes', true );
	$votes++;
	update_post_meta( $id, '_vigilum_votes', $votes );

	set_transient( $ip_key, 1, YEAR_IN_SECONDS );
	set_transient( $hour_key, $rate + 1, HOUR_IN_SECONDS );

	if ( ! headers_sent() ) {
		setcookie( $cookie, '1', time() + YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true );
	}

	return rest_ensure_response( array(
		'ok'    => true,
		'id'    => $id,
		'votes' => $votes,
		'label' => vigilum_votes_label( $votes ),
	) );
}

function vigilum_voter_ip() {
	$raw = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	return hash_hmac( 'sha256', $raw, wp_salt( 'auth' ) );
}

function vigilum_votes_noun( $count ) {
	$count  = absint( $count );
	$mod10  = $count % 10;
	$mod100 = $count % 100;
	if ( 1 === $mod10 && 11 !== $mod100 ) {
		return __( 'голос', 'vigilum' );
	}
	if ( $mod10 >= 2 && $mod10 <= 4 && ( $mod100 < 12 || $mod100 > 14 ) ) {
		return __( 'голоса', 'vigilum' );
	}
	return __( 'голосов', 'vigilum' );
}

function vigilum_votes_label( $count ) {
	$count = (int) $count;
	return trim( sprintf( '%s %s', number_format_i18n( $count ), vigilum_votes_noun( $count ) ) );
}

function vigilum_has_voted( $post_id ) {
	$cookie = 'vigilum_voted_' . (int) $post_id;
	if ( ! empty( $_COOKIE[ $cookie ] ) ) {
		return true;
	}
	return (bool) get_transient( 'vigilum_vote_' . (int) $post_id . '_' . vigilum_voter_ip() );
}
