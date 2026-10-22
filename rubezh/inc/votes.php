<?php
/**
 * Общественное голосование за инициативы.
 *
 * Голос не является референдумом. Адрес посетителя не сохраняется:
 * для гостя хранится только случайный жетон в cookie, для накрутки —
 * часовой обезличенный счётчик.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Имя таблицы голосов.
 *
 * @return string
 */
function rubezh_votes_table() {
	global $wpdb;
	return $wpdb->prefix . 'rubezh_votes';
}

add_action( 'init', 'rubezh_maybe_install_tables', 5 );
/**
 * Создаёт таблицу при первом запуске и после смены версии схемы.
 */
function rubezh_maybe_install_tables() {
	if ( '1' === get_option( 'rubezh_db_version' ) ) {
		return;
	}
	rubezh_install_tables();
}

/**
 * Таблица голосов.
 */
function rubezh_install_tables() {
	global $wpdb;
	$table   = rubezh_votes_table();
	$charset = $wpdb->get_charset_collate();
	$sql     = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		post_id bigint(20) unsigned NOT NULL,
		voter_hash char(64) NOT NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY post_voter (post_id,voter_hash)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	update_option( 'rubezh_db_version', '1' );
}

/**
 * Ставит подписанный жетон гостя.
 */
function rubezh_set_voter_cookie() {
	if ( headers_sent() ) {
		return;
	}
	$token   = bin2hex( random_bytes( 16 ) );
	$issued  = (string) time();
	$payload = $token . '.' . $issued;
	$sig     = hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
	$value   = $payload . '.' . $sig;

	setcookie(
		'rubezh_voter',
		$value,
		array(
			'expires'  => time() + YEAR_IN_SECONDS,
			'path'     => '/',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);
	$_COOKIE['rubezh_voter'] = $value;
}

/**
 * Проверяет жетон, выданный прошлым ответом сайта.
 *
 * @return string Пустая строка, если жетона нет или он подделан.
 */
function rubezh_valid_guest_token() {
	if ( empty( $_COOKIE['rubezh_voter'] ) ) {
		return '';
	}
	$raw   = sanitize_text_field( wp_unslash( $_COOKIE['rubezh_voter'] ) );
	$parts = explode( '.', $raw );
	if ( 3 !== count( $parts ) ) {
		return '';
	}
	list( $token, $issued, $sig ) = $parts;
	if ( ! preg_match( '/^[a-f0-9]{32}$/', $token ) || ! ctype_digit( $issued ) ) {
		return '';
	}
	$expected = hash_hmac( 'sha256', $token . '.' . $issued, wp_salt( 'auth' ) );
	if ( ! hash_equals( $expected, $sig ) ) {
		return '';
	}
	$age = time() - (int) $issued;
	if ( $age < 0 || $age > YEAR_IN_SECONDS ) {
		return '';
	}
	return $token;
}

/**
 * Ключ голосующего. Пустая строка — гостю ещё нельзя голосовать.
 *
 * @return string
 */
function rubezh_voter_hash() {
	$user_id = get_current_user_id();
	if ( $user_id ) {
		$basis = 'user:' . $user_id;
	} else {
		$token = rubezh_valid_guest_token();
		if ( '' === $token ) {
			return '';
		}
		$basis = 'guest:' . $token;
	}
	return hash( 'sha256', wp_salt( 'auth' ) . '|' . $basis );
}

/**
 * Часовой предел действий с голосами. В ключе нет самого адреса.
 *
 * @return bool
 */
function rubezh_vote_rate_ok() {
	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '0';
	$key   = 'rzrl_' . substr( hash( 'sha256', wp_salt( 'auth' ) . '|' . $ip ), 0, 32 );
	$count = (int) get_transient( $key );
	if ( $count >= 30 ) {
		return false;
	}
	set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	return true;
}

/**
 * @param int $post_id Инициатива.
 * @return int
 */
function rubezh_vote_count( $post_id ) {
	$stored = get_post_meta( $post_id, '_rubezh_votes', true );
	if ( '' === $stored || false === $stored ) {
		return rubezh_recount_votes( $post_id );
	}
	return (int) $stored;
}

/**
 * @param int $post_id Инициатива.
 * @return int
 */
function rubezh_recount_votes( $post_id ) {
	global $wpdb;
	$table = rubezh_votes_table();
	$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE post_id = %d", $post_id ) );
	update_post_meta( $post_id, '_rubezh_votes', $count );
	return $count;
}

/**
 * Какие из инициатив этот посетитель уже поддержал.
 *
 * @param int[] $ids Идентификаторы.
 * @return array<int, bool>
 */
function rubezh_voted_map( $ids ) {
	static $cache = array();
	$ids     = array_values( array_filter( array_map( 'absint', $ids ) ) );
	$missing = array();
	foreach ( $ids as $id ) {
		if ( ! array_key_exists( $id, $cache ) ) {
			$missing[] = $id;
		}
	}

	if ( $missing ) {
		foreach ( $missing as $id ) {
			$cache[ $id ] = false;
		}
		$hash = rubezh_voter_hash();
		if ( '' !== $hash ) {
			global $wpdb;
			$table = rubezh_votes_table();
			$in    = implode( ',', $missing );
			$rows  = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$table} WHERE voter_hash = %s AND post_id IN ({$in})", $hash ) );
			if ( is_array( $rows ) ) {
				foreach ( $rows as $id ) {
					$cache[ (int) $id ] = true;
				}
			}
		}
	}

	$map = array();
	foreach ( $ids as $id ) {
		$map[ $id ] = ! empty( $cache[ $id ] );
	}
	return $map;
}

/**
 * @param int $post_id Инициатива.
 * @return bool
 */
function rubezh_has_voted( $post_id ) {
	$map = rubezh_voted_map( array( (int) $post_id ) );
	return ! empty( $map[ (int) $post_id ] );
}

add_action( 'wp_ajax_rubezh_vote', 'rubezh_handle_vote' );
add_action( 'wp_ajax_nopriv_rubezh_vote', 'rubezh_handle_vote' );
/**
 * Поставить или снять поддержку.
 */
function rubezh_handle_vote() {
	check_ajax_referer( 'rubezh_vote', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$post    = get_post( $post_id );
	if ( ! $post || 'initiative' !== $post->post_type || 'publish' !== $post->post_status ) {
		wp_send_json_error( array( 'message' => __( 'Инициатива не найдена.', 'rubezh' ) ), 404 );
	}
	if ( 'open' !== rubezh_initiative_status( $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Голосование для этой инициативы закрыто.', 'rubezh' ) ), 403 );
	}
	if ( 'users' === rubezh_mod( 'vote_mode' ) && ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Поддержать инициативу можно после входа на сайт.', 'rubezh' ) ), 403 );
	}
	if ( ! rubezh_vote_rate_ok() ) {
		wp_send_json_error( array( 'message' => __( 'Слишком много действий. Попробуйте позже.', 'rubezh' ) ), 429 );
	}

	$hash = rubezh_voter_hash();
	if ( '' === $hash ) {
		wp_send_json_error( array( 'message' => __( 'Откройте страницу ещё раз и повторите голос.', 'rubezh' ) ), 400 );
	}

	global $wpdb;
	$table = rubezh_votes_table();
	$voted = rubezh_has_voted( $post_id );

	if ( $voted ) {
		$wpdb->delete(
			$table,
			array(
				'post_id'    => $post_id,
				'voter_hash' => $hash,
			),
			array( '%d', '%s' )
		);
		$voted = false;
	} else {
		$inserted = $wpdb->query(
			$wpdb->prepare(
				"INSERT IGNORE INTO {$table} (post_id, voter_hash, created_at) VALUES (%d, %s, %s)",
				$post_id,
				$hash,
				current_time( 'mysql', true )
			)
		);
		if ( false === $inserted ) {
			wp_send_json_error( array( 'message' => __( 'Хранилище голосов недоступно.', 'rubezh' ) ), 500 );
		}
		$voted = true;
	}

	$count = rubezh_recount_votes( $post_id );
	wp_send_json_success(
		array(
			'voted' => $voted,
			'count' => $count,
			'label' => rubezh_votes_label( $count ),
		)
	);
}

/**
 * Кнопка поддержки.
 *
 * @param int       $post_id Инициатива.
 * @param bool|null $voted   Уже поддержана.
 * @param float|null $share  Доля относительно списка, 0–100. Null скрывает шкалу.
 */
function rubezh_vote_controls( $post_id, $voted = null, $share = null ) {
	$post_id = (int) $post_id;
	$status  = rubezh_initiative_status( $post_id );
	$open    = 'open' === $status;
	$count   = rubezh_vote_count( $post_id );
	if ( null === $voted ) {
		$voted = rubezh_has_voted( $post_id );
	}
	$users_only = 'users' === rubezh_mod( 'vote_mode' ) && ! is_user_logged_in();

	echo '<div class="rz-vote" data-vote-root data-id="' . esc_attr( (string) $post_id ) . '" data-count="' . esc_attr( (string) $count ) . '">';
	echo '<p class="rz-vote__count" data-vote-count aria-live="polite">' . esc_html( rubezh_votes_label( $count ) ) . '</p>';
	if ( null !== $share ) {
		$share = max( 0, min( 100, (float) $share ) );
		echo '<div class="rz-vote__bar" data-vote-bar style="--share:' . esc_attr( (string) $share ) . '%"><span></span></div>';
	}

	if ( $users_only && $open ) {
		echo '<a class="rz-vote__button" href="' . esc_url( wp_login_url( get_permalink( $post_id ) ) ) . '">' . esc_html__( 'Войдите, чтобы поддержать', 'rubezh' ) . '</a>';
	} else {
		echo '<button type="button" class="rz-vote__button" data-vote aria-pressed="' . ( $voted ? 'true' : 'false' ) . '"';
		if ( ! $open ) {
			echo ' disabled';
		}
		echo '>';
		if ( ! $open ) {
			echo esc_html( rubezh_status_label( $status ) );
		} elseif ( $voted ) {
			esc_html_e( 'Поддержано', 'rubezh' );
		} else {
			esc_html_e( 'Поддержать', 'rubezh' );
		}
		echo '</button>';
	}
	echo '<p class="rz-vote__error" data-vote-error></p>';
	echo '</div>';
}
