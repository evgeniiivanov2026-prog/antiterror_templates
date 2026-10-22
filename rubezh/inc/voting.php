<?php
/**
 * Голосование за инициативы: REST, nonce, cookie + hash IP.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', 'rubezh_register_vote_route' );
function rubezh_register_vote_route() {
	register_rest_route(
		'rubezh/v1',
		'/vote',
		array(
			'methods'             => 'POST',
			'callback'            => 'rubezh_handle_vote',
			'permission_callback' => '__return_true',
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

function rubezh_voter_key( $post_id ) {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	return 'rubezh_vote_' . md5( $post_id . '|' . $ip . '|' . wp_salt( 'auth' ) . '|' . substr( $ua, 0, 24 ) );
}

function rubezh_cookie_name( $post_id ) {
	return 'rubezh_v_' . absint( $post_id );
}

function rubezh_handle_vote( WP_REST_Request $request ) {
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'rubezh_nonce', __( 'Сессия устарела. Обновите страницу.', 'rubezh' ), array( 'status' => 403 ) );
	}

	$post_id = absint( $request->get_param( 'id' ) );
	$post    = get_post( $post_id );

	if ( ! $post || 'initiative' !== $post->post_type || 'publish' !== $post->post_status ) {
		return new WP_Error( 'rubezh_missing', __( 'Инициатива не найдена.', 'rubezh' ), array( 'status' => 404 ) );
	}

	$status = get_post_meta( $post_id, '_rubezh_status', true );
	if ( $status && 'open' !== $status ) {
		return new WP_Error( 'rubezh_closed', __( 'Голосование по этой инициативе закрыто.', 'rubezh' ), array( 'status' => 409 ) );
	}

	$cookie = rubezh_cookie_name( $post_id );
	if ( ! empty( $_COOKIE[ $cookie ] ) ) {
		return new WP_REST_Response(
			array(
				'ok'      => false,
				'duplicate' => true,
				'votes'   => (int) get_post_meta( $post_id, '_rubezh_votes', true ),
				'message' => __( 'Вы уже поддержали эту инициативу.', 'rubezh' ),
			),
			409
		);
	}

	$key = rubezh_voter_key( $post_id );
	if ( get_transient( $key ) ) {
		return new WP_REST_Response(
			array(
				'ok'        => false,
				'duplicate' => true,
				'votes'     => (int) get_post_meta( $post_id, '_rubezh_votes', true ),
				'message'   => __( 'Вы уже поддержали эту инициативу.', 'rubezh' ),
			),
			409
		);
	}

	$votes = (int) get_post_meta( $post_id, '_rubezh_votes', true ) + 1;
	update_post_meta( $post_id, '_rubezh_votes', $votes );
	set_transient( $key, 1, MONTH_IN_SECONDS );
	setcookie( $cookie, '1', time() + MONTH_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );

	return array(
		'ok'    => true,
		'votes' => $votes,
		'id'    => $post_id,
	);
}

function rubezh_has_voted( $post_id ) {
	$cookie = rubezh_cookie_name( $post_id );
	if ( ! empty( $_COOKIE[ $cookie ] ) ) {
		return true;
	}
	return (bool) get_transient( rubezh_voter_key( $post_id ) );
}

function rubezh_max_votes() {
	global $wpdb;
	$max = $wpdb->get_var( $wpdb->prepare(
		"SELECT MAX(CAST(meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'",
		'_rubezh_votes',
		'initiative'
	) );
	return max( 1, (int) $max );
}
