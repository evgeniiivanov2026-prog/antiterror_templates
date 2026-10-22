<?php
/**
 * One-person ledger for initiative votes.
 *
 * Guests are keyed by a random HttpOnly cookie. Logged-in readers are keyed
 * by their user ID. Raw IP addresses are not stored. A second click withdraws
 * the vote while voting is still open. The count is the number of rows.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Votes table name.
 *
 * @return string
 */
function atj_votes_table() {
	global $wpdb;
	return $wpdb->prefix . 'atj_votes';
}

/**
 * Create or update the votes table.
 */
function atj_install_votes_table() {
	global $wpdb;

	$table   = atj_votes_table();
	$charset = $wpdb->get_charset_collate();
	$sql     = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		post_id bigint(20) unsigned NOT NULL,
		voter_hash char(64) NOT NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY post_voter (post_id, voter_hash)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	update_option( 'atj_db_version', '1.0.0' );
}

add_action( 'after_setup_theme', 'atj_maybe_install_votes', 20 );
/**
 * Install the table once, including when the theme was copied in by hand.
 */
function atj_maybe_install_votes() {
	if ( '1.0.0' !== get_option( 'atj_db_version' ) ) {
		atj_install_votes_table();
	}
}

add_action( 'init', 'atj_ensure_visitor_cookie', 1 );
/**
 * Give a guest browser a stable anonymous mark before a vote is cast.
 */
function atj_ensure_visitor_cookie() {
	if ( is_user_logged_in() || wp_doing_cron() || ( is_admin() && ! wp_doing_ajax() ) || headers_sent() ) {
		return;
	}

	$token = isset( $_COOKIE['atj_vid'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['atj_vid'] ) ) : '';
	if ( preg_match( '/^[a-f0-9]{32}$/', $token ) ) {
		return;
	}

	try {
		$token = bin2hex( random_bytes( 16 ) );
	} catch ( Exception $exception ) {
		unset( $exception );
		$token = substr( hash( 'sha256', wp_generate_password( 64, true, true ) ), 0, 32 );
	}

	$args = array(
		'expires'  => time() + YEAR_IN_SECONDS,
		'path'     => ( defined( 'COOKIEPATH' ) && COOKIEPATH ) ? COOKIEPATH : '/',
		'secure'   => is_ssl(),
		'httponly' => true,
		'samesite' => 'Lax',
	);
	if ( defined( 'COOKIE_DOMAIN' ) && COOKIE_DOMAIN ) {
		$args['domain'] = COOKIE_DOMAIN;
	}

	setcookie( 'atj_vid', $token, $args );
	$_COOKIE['atj_vid'] = $token;
}

/**
 * Irreversible voter key for the current reader.
 *
 * @return string
 */
function atj_voter_hash() {
	if ( is_user_logged_in() ) {
		return hash( 'sha256', 'user|' . get_current_user_id() . '|' . wp_salt( 'auth' ) );
	}

	$token = isset( $_COOKIE['atj_vid'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['atj_vid'] ) ) : '';
	if ( ! preg_match( '/^[a-f0-9]{32}$/', $token ) ) {
		return '';
	}

	return hash( 'sha256', 'guest|' . $token . '|' . wp_salt( 'auth' ) );
}

/**
 * Request cache of vote membership.
 *
 * @return array{primed: bool, map: array<int, bool>}
 */
function &atj_vote_cache() {
	static $cache = array(
		'primed' => false,
		'map'    => array(),
	);
	return $cache;
}

/**
 * Load which of the given initiatives the current reader already supported.
 *
 * @param int[] $ids Post IDs.
 */
function atj_prime_votes( array $ids ) {
	$ids  = array_values( array_filter( array_map( 'absint', $ids ) ) );
	$hash = atj_voter_hash();
	$cache = &atj_vote_cache();

	if ( ! $ids || ! $hash || '1.0.0' !== get_option( 'atj_db_version' ) ) {
		return;
	}

	global $wpdb;
	$in  = implode( ',', $ids );
	$sql = $wpdb->prepare(
		"SELECT post_id FROM " . atj_votes_table() . " WHERE voter_hash = %s AND post_id IN ({$in})",
		$hash
	);
	$found = $wpdb->get_col( $sql );
	$found = array_map( 'intval', $found );

	foreach ( $ids as $id ) {
		$cache['map'][ $id ] = in_array( $id, $found, true );
	}
	$cache['primed'] = true;
}

/**
 * Whether the current reader supported an initiative.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function atj_user_has_voted( $post_id ) {
	$post_id = (int) $post_id;
	$cache   = &atj_vote_cache();
	if ( array_key_exists( $post_id, $cache['map'] ) ) {
		return (bool) $cache['map'][ $post_id ];
	}

	$hash = atj_voter_hash();
	if ( ! $hash ) {
		return false;
	}

	global $wpdb;
	$exists = (int) $wpdb->get_var(
		$wpdb->prepare(
			'SELECT id FROM ' . atj_votes_table() . ' WHERE post_id = %d AND voter_hash = %s',
			$post_id,
			$hash
		)
	);
	$cache['map'][ $post_id ] = $exists > 0;
	return $exists > 0;
}

/**
 * Stored tally. Rows are the source of truth and refresh this value.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function atj_get_vote_count( $post_id ) {
	$value = get_post_meta( $post_id, '_atj_vote_count', true );
	return '' === $value ? 0 : (int) $value;
}

/**
 * Russian plural for "голос".
 *
 * @param int $count Count.
 * @return string
 */
function atj_votes_word( $count ) {
	$count = abs( (int) $count );
	$n10   = $count % 10;
	$n100  = $count % 100;
	if ( 1 === $n10 && 11 !== $n100 ) {
		return 'голос';
	}
	if ( $n10 >= 2 && $n10 <= 4 && ( $n100 < 12 || $n100 > 14 ) ) {
		return 'голоса';
	}
	return 'голосов';
}

/**
 * Open or closed.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function atj_initiative_status( $post_id ) {
	$status = get_post_meta( $post_id, '_atj_status', true );
	return 'closed' === $status ? 'closed' : 'open';
}

/**
 * Soft hourly cap per voter key.
 *
 * @param string $hash Voter hash.
 * @return bool
 */
function atj_rate_limit_ok( $hash ) {
	$key   = 'atj_rl_' . substr( $hash, 0, 24 );
	$count = (int) get_transient( $key );
	if ( $count > 40 ) {
		return false;
	}
	set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	return true;
}

/**
 * Vote control.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function atj_vote_markup( $post_id ) {
	$post_id = (int) $post_id;
	$closed  = 'open' !== atj_initiative_status( $post_id );
	$voted   = atj_user_has_voted( $post_id );
	$count   = atj_get_vote_count( $post_id );
	$classes = 'atj-vote';
	if ( $voted ) {
		$classes .= ' is-voted';
	}
	if ( $closed ) {
		$classes .= ' is-closed';
	}

	$label = $closed ? __( 'Голосование завершено', 'atjournal' ) : ( $voted ? __( 'Голос учтён', 'atjournal' ) : __( 'Поддержать', 'atjournal' ) );

	ob_start();
	?>
	<div class="<?php echo esc_attr( $classes ); ?>" data-vote data-post="<?php echo esc_attr( (string) $post_id ); ?>">
		<button type="button" class="atj-vote__button" aria-pressed="<?php echo $voted ? 'true' : 'false'; ?>" <?php echo $closed ? 'disabled' : ''; ?>>
			<span class="atj-vote__label"><?php echo esc_html( $label ); ?></span>
		</button>
		<p class="atj-vote__count" aria-live="polite">
			<span data-vote-count><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
			<span data-vote-word><?php echo esc_html( atj_votes_word( $count ) ); ?></span>
		</p>
		<p class="atj-vote__error" data-vote-error hidden></p>
	</div>
	<?php
	return (string) ob_get_clean();
}

add_action( 'wp_ajax_atj_vote', 'atj_handle_vote' );
add_action( 'wp_ajax_nopriv_atj_vote', 'atj_handle_vote' );
/**
 * Toggle a vote.
 */
function atj_handle_vote() {
	check_ajax_referer( 'atj_vote', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$post    = get_post( $post_id );
	if ( ! $post || 'atj_initiative' !== $post->post_type || 'publish' !== $post->post_status ) {
		wp_send_json_error( array( 'message' => __( 'Инициатива не найдена.', 'atjournal' ) ), 404 );
	}

	if ( '1.0.0' !== get_option( 'atj_db_version' ) ) {
		atj_install_votes_table();
	}

	$hash = atj_voter_hash();
	if ( ! $hash ) {
		wp_send_json_error( array( 'message' => __( 'Не удалось закрепить голос в этом браузере.', 'atjournal' ) ), 400 );
	}
	if ( ! atj_rate_limit_ok( $hash ) ) {
		wp_send_json_error( array( 'message' => __( 'Слишком много попыток. Подождите немного.', 'atjournal' ) ), 429 );
	}

	$closed = 'open' !== atj_initiative_status( $post_id );
	if ( $closed ) {
		wp_send_json_error(
			array(
				'message' => __( 'Голосование завершено.', 'atjournal' ),
				'count'   => atj_get_vote_count( $post_id ),
				'voted'   => atj_user_has_voted( $post_id ),
				'closed'  => true,
			),
			403
		);
	}

	global $wpdb;
	$table    = atj_votes_table();
	$existing = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE post_id = %d AND voter_hash = %s", $post_id, $hash ) );
	$voted    = true;

	if ( $existing ) {
		$wpdb->delete( $table, array( 'id' => $existing ), array( '%d' ) );
		$voted = false;
	} else {
		$inserted = $wpdb->insert(
			$table,
			array(
				'post_id'    => $post_id,
				'voter_hash' => $hash,
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s' )
		);
		$voted = false !== $inserted || atj_user_has_voted( $post_id );
	}

	$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE post_id = %d", $post_id ) );
	update_post_meta( $post_id, '_atj_vote_count', $count );

	$cache = &atj_vote_cache();
	$cache['map'][ $post_id ] = $voted;

	wp_send_json_success(
		array(
			'count'  => $count,
			'voted'  => $voted,
			'word'   => atj_votes_word( $count ),
			'closed' => false,
		)
	);
}

add_action( 'before_delete_post', 'atj_delete_votes' );
/**
 * Remove ledger rows with the initiative.
 *
 * @param int $post_id Post ID.
 */
function atj_delete_votes( $post_id ) {
	if ( 'atj_initiative' !== get_post_type( $post_id ) || '1.0.0' !== get_option( 'atj_db_version' ) ) {
		return;
	}
	global $wpdb;
	$wpdb->delete( atj_votes_table(), array( 'post_id' => (int) $post_id ), array( '%d' ) );
}
