<?php
/**
 * Civic voting and initiative proposals.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', 'aegis_register_rest' );
function aegis_register_rest() {
	register_rest_route(
		'aegis/v1',
		'/vote',
		array(
			'methods'             => 'POST',
			'callback'            => 'aegis_rest_vote',
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

	register_rest_route(
		'aegis/v1',
		'/propose',
		array(
			'methods'             => 'POST',
			'callback'            => 'aegis_rest_propose',
			'permission_callback' => '__return_true',
		)
	);
}

function aegis_voter_hash() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	return hash( 'sha256', $ip . '|' . $ua . '|' . wp_salt( 'auth' ) );
}

function aegis_cookie_votes() {
	if ( empty( $_COOKIE['aegis_votes'] ) ) {
		return array();
	}
	$raw = sanitize_text_field( wp_unslash( $_COOKIE['aegis_votes'] ) );
	$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
	return array_values( array_unique( $ids ) );
}

function aegis_has_voted( $post_id ) {
	if ( in_array( (int) $post_id, aegis_cookie_votes(), true ) ) {
		return true;
	}
	$store = get_transient( 'aegis_voter_' . aegis_voter_hash() );
	return is_array( $store ) && in_array( (int) $post_id, $store, true );
}

function aegis_mark_voted( $post_id ) {
	$ids   = aegis_cookie_votes();
	$ids[] = (int) $post_id;
	$ids   = array_values( array_unique( $ids ) );
	setcookie( 'aegis_votes', implode( ',', $ids ), time() + MONTH_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );

	$key   = 'aegis_voter_' . aegis_voter_hash();
	$store = get_transient( $key );
	if ( ! is_array( $store ) ) {
		$store = array();
	}
	$store[] = (int) $post_id;
	set_transient( $key, array_values( array_unique( $store ) ), MONTH_IN_SECONDS );
}

function aegis_rest_vote( WP_REST_Request $request ) {
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'aegis_bad_nonce', __( 'Сессия истекла. Обновите страницу.', 'aegis' ), array( 'status' => 403 ) );
	}

	$post_id = absint( $request->get_param( 'id' ) );
	$post    = get_post( $post_id );

	if ( ! $post || 'aegis_initiative' !== $post->post_type || 'publish' !== $post->post_status ) {
		return new WP_Error( 'aegis_missing', __( 'Инициатива не найдена.', 'aegis' ), array( 'status' => 404 ) );
	}

	$status = get_post_meta( $post_id, '_aegis_status', true );
	if ( in_array( $status, array( 'closed' ), true ) ) {
		return new WP_Error( 'aegis_closed', __( 'Голосование по этой инициативе закрыто.', 'aegis' ), array( 'status' => 409 ) );
	}

	if ( aegis_has_voted( $post_id ) ) {
		return new WP_REST_Response(
			array(
				'ok'      => true,
				'already' => true,
				'votes'   => absint( get_post_meta( $post_id, '_aegis_votes', true ) ),
				'message' => __( 'Вы уже поддержали эту инициативу', 'aegis' ),
			),
			200
		);
	}

	$votes = absint( get_post_meta( $post_id, '_aegis_votes', true ) ) + 1;
	update_post_meta( $post_id, '_aegis_votes', $votes );
	aegis_mark_voted( $post_id );

	return array(
		'ok'      => true,
		'already' => false,
		'votes'   => $votes,
		'message' => __( 'Ваш голос учтён', 'aegis' ),
	);
}

function aegis_rest_propose( WP_REST_Request $request ) {
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'aegis_bad_nonce', __( 'Сессия истекла. Обновите страницу.', 'aegis' ), array( 'status' => 403 ) );
	}

	$honeypot = trim( (string) $request->get_param( 'website' ) );
	if ( $honeypot !== '' ) {
		return array( 'ok' => true, 'message' => __( 'Инициатива отправлена на модерацию.', 'aegis' ) );
	}

	$title   = sanitize_text_field( (string) $request->get_param( 'title' ) );
	$body    = wp_kses_post( (string) $request->get_param( 'description' ) );
	$author  = sanitize_text_field( (string) $request->get_param( 'author' ) );
	$topic   = sanitize_text_field( (string) $request->get_param( 'topic' ) );

	if ( strlen( $title ) < 8 || strlen( wp_strip_all_tags( $body ) ) < 40 ) {
		return new WP_Error( 'aegis_short', __( 'Опишите инициативу подробнее: заголовок от 8 символов, текст от 40.', 'aegis' ), array( 'status' => 400 ) );
	}

	$lock_key = 'aegis_propose_' . aegis_voter_hash();
	if ( get_transient( $lock_key ) ) {
		return new WP_Error( 'aegis_rate', __( 'Подождите несколько минут перед следующей заявкой.', 'aegis' ), array( 'status' => 429 ) );
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'aegis_initiative',
			'post_status'  => 'pending',
			'post_title'   => $title,
			'post_content' => $body,
			'post_excerpt' => wp_trim_words( wp_strip_all_tags( $body ), 28 ),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return new WP_Error( 'aegis_save', __( 'Не удалось сохранить заявку.', 'aegis' ), array( 'status' => 500 ) );
	}

	update_post_meta( $post_id, '_aegis_author_name', $author );
	update_post_meta( $post_id, '_aegis_status', 'review' );
	update_post_meta( $post_id, '_aegis_votes', 0 );

	if ( $topic ) {
		wp_set_object_terms( $post_id, $topic, 'aegis_topic', true );
	}

	set_transient( $lock_key, 1, 10 * MINUTE_IN_SECONDS );

	$admin = aegis_email();
	if ( $admin ) {
		wp_mail(
			$admin,
			sprintf( __( 'Новая инициатива: %s', 'aegis' ), $title ),
			wp_strip_all_tags( $body )
		);
	}

	return array(
		'ok'      => true,
		'message' => __( 'Инициатива отправлена на модерацию фонда.', 'aegis' ),
	);
}
