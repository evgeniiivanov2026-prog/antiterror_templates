<?php
/**
 * Civic voting for anti-terror initiatives.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', 'atj_register_vote_route' );
function atj_register_vote_route() {
	register_rest_route(
		'antiterror-journal/v1',
		'/vote',
		array(
			'methods'             => 'POST',
			'callback'            => 'atj_rest_cast_vote',
			'permission_callback' => 'atj_vote_permission',
			'args'                => array(
				'id' => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}

function atj_vote_permission( WP_REST_Request $request ) {
	$nonce = $request->get_header( 'X-WP-Nonce' );
	return (bool) wp_verify_nonce( $nonce, 'wp_rest' );
}

function atj_voter_hash() {
	$ip     = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$agent  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	$salt   = wp_salt( 'auth' );
	return hash( 'sha256', $ip . '|' . substr( $agent, 0, 80 ) . '|' . $salt );
}

function atj_get_voted_ids() {
	$ids = array();

	if ( is_user_logged_in() ) {
		$stored = get_user_meta( get_current_user_id(), 'atj_voted_ids', true );
		if ( is_array( $stored ) ) {
			$ids = array_map( 'absint', $stored );
		}
	}

	if ( ! empty( $_COOKIE['atj_voted'] ) ) {
		$cookie = json_decode( sanitize_text_field( wp_unslash( $_COOKIE['atj_voted'] ) ), true );
		if ( is_array( $cookie ) ) {
			$ids = array_merge( $ids, array_map( 'absint', $cookie ) );
		}
	}

	return array_values( array_unique( array_filter( $ids ) ) );
}

function atj_remember_vote( $initiative_id ) {
	$ids   = atj_get_voted_ids();
	$ids[] = (int) $initiative_id;
	$ids   = array_values( array_unique( $ids ) );

	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), 'atj_voted_ids', $ids );
	}

	setcookie(
		'atj_voted',
		wp_json_encode( $ids ),
		array(
			'expires'  => time() + YEAR_IN_SECONDS,
			'path'     => COOKIEPATH,
			'secure'   => is_ssl(),
			'httponly' => false,
			'samesite' => 'Lax',
		)
	);
}

function atj_initiative_is_open( $post_id ) {
	$status   = get_post_meta( $post_id, '_atj_status', true ) ?: 'open';
	$deadline = (string) get_post_meta( $post_id, '_atj_deadline', true );
	if ( 'closed' === $status ) {
		return false;
	}
	if ( $deadline && strtotime( $deadline . ' 23:59:59' ) < time() ) {
		return false;
	}
	return true;
}

function atj_rest_cast_vote( WP_REST_Request $request ) {
	$post_id = (int) $request->get_param( 'id' );

	if ( 'atj_initiative' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
		return new WP_Error( 'atj_invalid', __( 'Инициатива не найдена.', 'antiterror-journal' ), array( 'status' => 404 ) );
	}

	if ( ! atj_initiative_is_open( $post_id ) ) {
		return new WP_Error( 'atj_closed', __( 'Голосование закрыто.', 'antiterror-journal' ), array( 'status' => 409 ) );
	}

	$allow_guests = get_theme_mod( 'atj_allow_guest_votes', true );
	if ( ! $allow_guests && ! is_user_logged_in() ) {
		return new WP_Error( 'atj_auth', __( 'Голосовать могут только авторизованные читатели.', 'antiterror-journal' ), array( 'status' => 401 ) );
	}

	if ( in_array( $post_id, atj_get_voted_ids(), true ) ) {
		return new WP_Error( 'atj_duplicate', __( 'Вы уже голосовали за эту инициативу.', 'antiterror-journal' ), array( 'status' => 409 ) );
	}

	$hash = atj_voter_hash();
	$rate_key = 'atj_vote_rate_' . substr( $hash, 0, 20 );
	$rate     = (int) get_transient( $rate_key );
	if ( $rate >= 20 ) {
		return new WP_Error( 'atj_rate', __( 'Слишком много попыток. Подождите час.', 'antiterror-journal' ), array( 'status' => 429 ) );
	}

	$hashes = get_post_meta( $post_id, '_atj_vote_hashes', true );
	$hashes = is_array( $hashes ) ? $hashes : array();
	if ( in_array( $hash, $hashes, true ) ) {
		return new WP_Error( 'atj_duplicate', __( 'Вы уже голосовали за эту инициативу.', 'antiterror-journal' ), array( 'status' => 409 ) );
	}

	$votes = (int) get_post_meta( $post_id, '_atj_votes', true );
	$votes++;
	update_post_meta( $post_id, '_atj_votes', $votes );

	$hashes[] = $hash;
	update_post_meta( $post_id, '_atj_vote_hashes', array_slice( $hashes, -500 ) );

	atj_remember_vote( $post_id );
	set_transient( $rate_key, $rate + 1, HOUR_IN_SECONDS );

	return rest_ensure_response(
		array(
			'ok'    => true,
			'id'    => $post_id,
			'votes' => $votes,
			'label' => sprintf(
				/* translators: %d: vote count */
				_n( '%d голос', '%d голоса', $votes, 'antiterror-journal' ),
				$votes
			),
		)
	);
}

function atj_votes_label( $count ) {
	$count = (int) $count;
	return sprintf(
		/* translators: %d: vote count */
		_n( '%d голос', '%d голоса', $count, 'antiterror-journal' ),
		$count
	);
}
