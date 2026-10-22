<?php
/**
 * Журнал голосов.
 *
 * Гость получает случайный идентификатор в cookie. В базе хранится только HMAC,
 * без IP и без самого токена. Вошедший пользователь голосует от аккаунта.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Имя таблицы.
 */
function strazha_ballots_table() {
	global $wpdb;
	return $wpdb->prefix . 'strazha_ballots';
}

/**
 * Создать таблицу голосов.
 */
function strazha_install_table() {
	global $wpdb;
	$table   = strazha_ballots_table();
	$charset = $wpdb->get_charset_collate();
	$sql     = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		initiative_id bigint(20) unsigned NOT NULL,
		voter_key char(64) NOT NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY initiative_voter (initiative_id,voter_key)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	update_option( 'strazha_db_version', '1', false );
}

add_action( 'init', 'strazha_maybe_install', 5 );
/**
 * Доустановить таблицу, если тему скопировали без переключения.
 */
function strazha_maybe_install() {
	if ( '1' === get_option( 'strazha_db_version' ) ) {
		return;
	}
	strazha_install_table();
}

add_action( 'init', 'strazha_ensure_voter_cookie', 1 );
/**
 * Выдать гостю стабильный ключ на этот браузер.
 */
function strazha_ensure_voter_cookie() {
	if ( is_user_logged_in() || wp_doing_cron() ) {
		return;
	}
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	if ( headers_sent() ) {
		return;
	}

	$token = isset( $_COOKIE['strazha_vid'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['strazha_vid'] ) ) : '';
	if ( preg_match( '/^[a-f0-9]{32}$/', $token ) ) {
		return;
	}

	try {
		$token = bin2hex( random_bytes( 16 ) );
	} catch ( Exception $exception ) {
		$token = wp_generate_password( 32, false, false );
	}

	$path   = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
	$domain = ( defined( 'COOKIE_DOMAIN' ) && is_string( COOKIE_DOMAIN ) ) ? COOKIE_DOMAIN : '';
	setcookie(
		'strazha_vid',
		$token,
		array(
			'expires'  => time() + 180 * DAY_IN_SECONDS,
			'path'     => $path,
			'domain'   => $domain,
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);
	$_COOKIE['strazha_vid'] = $token;
}

/**
 * Ключ голосующего. В базу попадает только он.
 */
function strazha_voter_key() {
	if ( is_user_logged_in() ) {
		return hash_hmac( 'sha256', 'user:' . get_current_user_id(), wp_salt( 'auth' ) );
	}
	$token = isset( $_COOKIE['strazha_vid'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['strazha_vid'] ) ) : '';
	if ( ! preg_match( '/^[a-f0-9]{32}$/', $token ) ) {
		$token = 'anon';
	}
	return hash_hmac( 'sha256', 'guest:' . $token, wp_salt( 'auth' ) );
}

/**
 * Запомнить, за какие карточки на странице человек уже голосовал.
 *
 * @param int[] $ids Идентификаторы.
 */
function strazha_prime_ballots( $ids ) {
	global $wpdb;
	$ids = array_values( array_filter( array_map( 'absint', (array) $ids ) ) );
	if ( ! $ids || '1' !== get_option( 'strazha_db_version' ) ) {
		return;
	}

	$key      = strazha_voter_key();
	$prepared = implode( ',', $ids );
	$table    = strazha_ballots_table();
	// Идентификаторы уже приведены к int.
	$rows = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT initiative_id FROM {$table} WHERE voter_key = %s AND initiative_id IN ({$prepared})",
			$key
		)
	);
	$voted = array_fill_keys( array_map( 'intval', (array) $rows ), true );
	foreach ( $ids as $id ) {
		strazha_ballot_cache( $id, isset( $voted[ $id ] ) );
	}
}

/**
 * Кэш голоса в пределах запроса.
 *
 * @param int       $id    Инициатива.
 * @param bool|null $value Новое значение либо null, чтобы прочитать.
 */
function strazha_ballot_cache( $id, $value = null ) {
	static $cache = array();
	$id           = (int) $id;
	if ( null === $value ) {
		return array_key_exists( $id, $cache ) ? $cache[ $id ] : null;
	}
	$cache[ $id ] = (bool) $value;
	return $cache[ $id ];
}

/**
 * Уже голосовал ли текущий ключ.
 *
 * @param int $post_id Инициатива.
 */
function strazha_user_has_voted( $post_id ) {
	$cached = strazha_ballot_cache( $post_id );
	if ( null !== $cached ) {
		return $cached;
	}
	if ( '1' !== get_option( 'strazha_db_version' ) ) {
		return false;
	}

	global $wpdb;
	$found = (int) $wpdb->get_var(
		$wpdb->prepare(
			'SELECT id FROM ' . strazha_ballots_table() . ' WHERE initiative_id = %d AND voter_key = %s LIMIT 1',
			$post_id,
			strazha_voter_key()
		)
	);
	return strazha_ballot_cache( $post_id, $found > 0 );
}

/**
 * Сдвинуть счётчик.
 *
 * @param int $post_id Инициатива.
 * @param int $delta   1 или -1.
 */
function strazha_shift_votes( $post_id, $delta ) {
	global $wpdb;
	if ( '' === get_post_meta( $post_id, '_strazha_votes', true ) ) {
		update_post_meta( $post_id, '_strazha_votes', 0 );
	}
	if ( $delta > 0 ) {
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = CAST(meta_value AS SIGNED) + 1 WHERE post_id = %d AND meta_key = '_strazha_votes'",
				$post_id
			)
		);
	} else {
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = GREATEST(CAST(meta_value AS SIGNED) - 1, 0) WHERE post_id = %d AND meta_key = '_strazha_votes'",
				$post_id
			)
		);
	}
	wp_cache_delete( $post_id, 'post_meta' );
}

/**
 * Принять или снять голос.
 *
 * @param int $post_id Инициатива.
 * @return array<string, mixed>
 */
function strazha_cast_vote( $post_id ) {
	$post_id = absint( $post_id );
	$result  = array(
		'ok'          => false,
		'code'        => 'error',
		'message'     => __( 'Не удалось сохранить голос.', 'strazha' ),
		'votes'       => strazha_vote_count( $post_id ),
		'votes_label' => number_format_i18n( strazha_vote_count( $post_id ) ),
		'pressed'     => strazha_user_has_voted( $post_id ),
		'label'       => __( 'Поддержать', 'strazha' ),
	);

	$fail = static function ( $code, $message ) use ( &$result ) {
		$result['code']    = $code;
		$result['message'] = $message;
		return $result;
	};

	if ( strazha_flag( 'votes_require_login' ) && ! is_user_logged_in() ) {
		return $fail( 'login', __( 'Чтобы голосовать, нужно войти.', 'strazha' ) );
	}

	$bucket = 'strazha_rl_' . substr( strazha_voter_key(), 0, 24 );
	$count  = (int) get_transient( $bucket );
	if ( $count > 40 ) {
		return $fail( 'limited', __( 'Слишком много попыток. Подождите немного.', 'strazha' ) );
	}
	set_transient( $bucket, $count + 1, HOUR_IN_SECONDS );

	$post = get_post( $post_id );
	if ( ! $post || 'initiative' !== $post->post_type || 'publish' !== $post->post_status ) {
		return $fail( 'missing', __( 'Инициатива не найдена.', 'strazha' ) );
	}
	if ( post_password_required( $post ) ) {
		return $fail( 'protected', __( 'Эта инициатива закрыта паролем.', 'strazha' ) );
	}
	if ( 'collecting' !== strazha_initiative_status( $post_id ) ) {
		return $fail( 'closed', __( 'Голосование по этой инициативе закрыто.', 'strazha' ) );
	}
	if ( '1' !== get_option( 'strazha_db_version' ) ) {
		strazha_install_table();
	}

	global $wpdb;
	$table = strazha_ballots_table();
	$key   = strazha_voter_key();
	$existing = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT id FROM {$table} WHERE initiative_id = %d AND voter_key = %s LIMIT 1",
			$post_id,
			$key
		)
	);

	if ( $existing ) {
		$wpdb->delete( $table, array( 'id' => $existing ), array( '%d' ) );
		strazha_shift_votes( $post_id, -1 );
		strazha_ballot_cache( $post_id, false );
		$result['pressed'] = false;
		$result['code']    = 'removed';
		$result['message'] = __( 'Голос снят.', 'strazha' );
		$result['label']   = __( 'Поддержать', 'strazha' );
	} else {
		$inserted = $wpdb->insert(
			$table,
			array(
				'initiative_id' => $post_id,
				'voter_key'     => $key,
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s' )
		);
		if ( false === $inserted ) {
			$again = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$table} WHERE initiative_id = %d AND voter_key = %s LIMIT 1",
					$post_id,
					$key
				)
			);
			if ( ! $again ) {
				return $fail( 'error', __( 'Не удалось сохранить голос.', 'strazha' ) );
			}
			strazha_ballot_cache( $post_id, true );
			$result['pressed'] = true;
			$result['code']    = 'added';
			$result['message'] = __( 'Голос учтён.', 'strazha' );
			$result['label']   = __( 'Вы поддержали', 'strazha' );
			$votes             = strazha_vote_count( $post_id );
			$result['ok']      = true;
			$result['votes']   = $votes;
			$result['votes_label'] = number_format_i18n( $votes );
			return $result;
		}
		strazha_shift_votes( $post_id, 1 );
		strazha_ballot_cache( $post_id, true );
		$result['pressed'] = true;
		$result['code']    = 'added';
		$result['message'] = __( 'Голос учтён.', 'strazha' );
		$result['label']   = __( 'Вы поддержали', 'strazha' );
	}

	$votes                 = strazha_vote_count( $post_id );
	$result['ok']          = true;
	$result['votes']       = $votes;
	$result['votes_label'] = number_format_i18n( $votes );
	return $result;
}

if ( ! function_exists( 'strazha_vote_control' ) ) {
	/**
	 * Кнопка голоса. Без JavaScript уходит обычной формой.
	 *
	 * @param int $post_id Инициатива.
	 */
	function strazha_vote_control( $post_id ) {
		$post_id = absint( $post_id );
		$status  = strazha_initiative_status( $post_id );
		$open    = ( 'collecting' === $status );
		$pressed = $open && strazha_user_has_voted( $post_id );
		$votes   = strazha_vote_count( $post_id );
		$need_login = strazha_flag( 'votes_require_login' ) && ! is_user_logged_in();

		if ( $need_login && $open ) {
			printf(
				'<a class="vote vote--login" href="%1$s">%2$s</a>',
				esc_url( wp_login_url( get_permalink( $post_id ) ) ),
				esc_html__( 'Войти, чтобы голосовать', 'strazha' )
			);
			return;
		}

		if ( $pressed ) {
			$label = __( 'Вы поддержали', 'strazha' );
		} elseif ( $open ) {
			$label = __( 'Поддержать', 'strazha' );
		} else {
			$label = __( 'Закрыто', 'strazha' );
		}
		?>
		<form class="vote-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-vote-form>
			<input type="hidden" name="action" value="strazha_vote">
			<input type="hidden" name="id" value="<?php echo esc_attr( (string) $post_id ); ?>">
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( strazha_current_url() ); ?>">
			<?php wp_nonce_field( 'strazha_vote_' . $post_id ); ?>
			<button class="vote<?php echo $pressed ? ' is-on' : ''; ?>" type="submit" data-id="<?php echo esc_attr( (string) $post_id ); ?>" aria-pressed="<?php echo $pressed ? 'true' : 'false'; ?>" <?php disabled( ! $open ); ?>>
				<span class="vote__glyph" aria-hidden="true"></span>
				<span data-label><?php echo esc_html( $label ); ?></span>
				<span data-count aria-live="polite"><?php echo esc_html( number_format_i18n( $votes ) ); ?></span>
			</button>
			<p class="vote-form__note" data-vote-note role="status"></p>
		</form>
		<?php
	}
}

add_action( 'admin_post_nopriv_strazha_vote', 'strazha_vote_post' );
add_action( 'admin_post_strazha_vote', 'strazha_vote_post' );
/**
 * Голос обычной формой.
 */
function strazha_vote_post() {
	$post_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	check_admin_referer( 'strazha_vote_' . $post_id );
	$result   = strazha_cast_vote( $post_id );
	$fallback = $post_id ? get_permalink( $post_id ) : home_url( '/' );
	$redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
	$redirect = wp_validate_redirect( $redirect, $fallback );
	$redirect = remove_query_arg( 'voted', $redirect );
	$redirect = add_query_arg( 'voted', rawurlencode( $result['code'] ), $redirect );
	wp_safe_redirect( $redirect );
	exit;
}

add_action( 'wp_ajax_strazha_vote', 'strazha_vote_ajax' );
add_action( 'wp_ajax_nopriv_strazha_vote', 'strazha_vote_ajax' );
/**
 * Голос без перезагрузки.
 */
function strazha_vote_ajax() {
	$post_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	check_ajax_referer( 'strazha_vote_' . $post_id );
	$result = strazha_cast_vote( $post_id );
	if ( empty( $result['ok'] ) ) {
		wp_send_json_error( $result, 400 );
	}
	wp_send_json_success( $result );
}

add_action( 'admin_post_strazha_recount', 'strazha_recount_votes' );
/**
 * Пересчитать счётчик из журнала.
 */
function strazha_recount_votes() {
	$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
	if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'strazha' ) );
	}
	check_admin_referer( 'strazha_recount_' . $post_id );
	if ( 'initiative' !== get_post_type( $post_id ) ) {
		wp_die( esc_html__( 'Это не инициатива.', 'strazha' ) );
	}

	global $wpdb;
	$count = (int) $wpdb->get_var(
		$wpdb->prepare(
			'SELECT COUNT(*) FROM ' . strazha_ballots_table() . ' WHERE initiative_id = %d',
			$post_id
		)
	);
	update_post_meta( $post_id, '_strazha_votes', $count );
	wp_safe_redirect( get_edit_post_link( $post_id, 'raw' ) );
	exit;
}
