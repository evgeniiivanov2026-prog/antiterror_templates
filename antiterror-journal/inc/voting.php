<?php
/**
 * Public support votes for initiatives.
 *
 * One browser cookie or one account can support an initiative once.
 * This is an editorial tally, not a legal referendum.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', 'rubezh_register_vote_route' );
add_action( 'admin_post_nopriv_rubezh_vote', 'rubezh_handle_vote_post' );
add_action( 'admin_post_rubezh_vote', 'rubezh_handle_vote_post' );

/**
 * REST route used by the ballot script.
 */
function rubezh_register_vote_route() {
	register_rest_route(
		'rubezh/v1',
		'/vote',
		array(
			'methods'             => 'POST',
			'callback'            => 'rubezh_rest_vote',
			'permission_callback' => 'rubezh_rest_nonce_ok',
			'args'                => array(
				'id' => array(
					'type'     => 'integer',
					'required' => true,
				),
			),
		)
	);
}

/**
 * Anonymous and logged-in visitors both get a REST nonce on the page.
 *
 * @return bool
 */
function rubezh_rest_nonce_ok() {
	$nonce = isset( $_SERVER['HTTP_X_WP_NONCE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WP_NONCE'] ) ) : '';
	return (bool) wp_verify_nonce( $nonce, 'wp_rest' );
}

/**
 * REST callback.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function rubezh_rest_vote( $request ) {
	$result = rubezh_cast_vote( (int) $request->get_param( 'id' ) );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	return rest_ensure_response( $result );
}

/**
 * No-JS fallback.
 */
function rubezh_handle_vote_post() {
	$id = isset( $_POST['initiative_id'] ) ? absint( wp_unslash( $_POST['initiative_id'] ) ) : 0;
	if ( ! isset( $_POST['rubezh_vote_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rubezh_vote_nonce'] ) ), 'rubezh_vote_' . $id ) ) {
		wp_die( esc_html__( 'Ссылка устарела. Обновите страницу.', 'rubezh' ), 403 );
	}
	rubezh_cast_vote( $id );
	$back = wp_get_referer();
	if ( ! $back ) {
		$back = get_permalink( $id );
	}
	wp_safe_redirect( $back ? $back : home_url( '/' ) );
	exit;
}

/**
 * Record a single vote.
 *
 * @param int $id Initiative ID.
 * @return array<string, mixed>|WP_Error
 */
function rubezh_cast_vote( $id ) {
	$post = get_post( $id );
	if ( ! $post || 'rubezh_initiative' !== $post->post_type || 'publish' !== $post->post_status ) {
		return new WP_Error( 'rubezh_invalid', __( 'Инициатива не найдена.', 'rubezh' ), array( 'status' => 404 ) );
	}
	$status = rubezh_initiative_status( $id );
	$goal   = max( 1, (int) get_post_meta( $id, '_rubezh_goal', true ) );
	$votes  = (int) get_post_meta( $id, '_rubezh_votes', true );
	if ( 'adopted' === $status ) {
		return new WP_Error( 'rubezh_closed', __( 'Голосование закрыто.', 'rubezh' ), array( 'status' => 409 ) );
	}
	if ( rubezh_has_voted( $id ) ) {
		return array(
			'votes'   => $votes,
			'goal'    => $goal,
			'status'  => $status,
			'already' => true,
		);
	}
	if ( ! rubezh_vote_rate_ok() ) {
		return new WP_Error( 'rubezh_rate', __( 'Слишком много запросов. Попробуйте позже.', 'rubezh' ), array( 'status' => 429 ) );
	}

	$votes++;
	update_post_meta( $id, '_rubezh_votes', $votes );
	rubezh_remember_vote( $id );

	return array(
		'votes'   => $votes,
		'goal'    => $goal,
		'status'  => $status,
		'already' => false,
	);
}

/**
 * Hourly ceiling per address. The key is a hash and expires.
 *
 * @return bool
 */
function rubezh_vote_rate_ok() {
	$ip     = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '0';
	$key    = 'rubezh_rl_' . md5( $ip );
	$count  = (int) get_transient( $key );
	if ( $count > 30 ) {
		return false;
	}
	set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	return true;
}

/**
 * IDs this visitor already supported.
 *
 * @return int[]
 */
function rubezh_voted_ids() {
	$ids = array();
	if ( is_user_logged_in() ) {
		$stored = get_user_meta( get_current_user_id(), '_rubezh_votes', true );
		if ( is_array( $stored ) ) {
			$ids = array_map( 'intval', $stored );
		}
	}
	if ( empty( $_COOKIE['rubezh_ballot'] ) ) {
		return array_values( array_unique( $ids ) );
	}
	$parts = explode( '.', sanitize_text_field( wp_unslash( $_COOKIE['rubezh_ballot'] ) ), 2 );
	if ( 2 !== count( $parts ) ) {
		return array_values( array_unique( $ids ) );
	}
	$expected = hash_hmac( 'sha256', $parts[0], rubezh_ballot_key() );
	if ( ! hash_equals( $expected, $parts[1] ) ) {
		return array_values( array_unique( $ids ) );
	}
	$decoded = json_decode( rubezh_base64url_decode( $parts[0] ), true );
	if ( is_array( $decoded ) ) {
		$ids = array_merge( $ids, array_map( 'intval', $decoded ) );
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Whether the visitor already voted.
 *
 * @param int $id Initiative ID.
 * @return bool
 */
function rubezh_has_voted( $id ) {
	return in_array( (int) $id, rubezh_voted_ids(), true );
}

/**
 * Persist the vote for this visitor.
 *
 * @param int $id Initiative ID.
 */
function rubezh_remember_vote( $id ) {
	$ids   = rubezh_voted_ids();
	$ids[] = (int) $id;
	$ids   = array_slice( array_values( array_unique( array_map( 'intval', $ids ) ) ), -200 );
	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), '_rubezh_votes', $ids );
	}
	$payload = rubezh_base64url_encode( wp_json_encode( $ids ) );
	$signed  = $payload . '.' . hash_hmac( 'sha256', $payload, rubezh_ballot_key() );
	setcookie(
		'rubezh_ballot',
		$signed,
		array(
			'expires'  => time() + 180 * DAY_IN_SECONDS,
			'path'     => defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);
	$_COOKIE['rubezh_ballot'] = $signed;
}

/**
 * Key bound to the site salts.
 *
 * @return string
 */
function rubezh_ballot_key() {
	return wp_hash( 'rubezh_ballot' );
}

/**
 * URL-safe base64.
 *
 * @param string $data Raw.
 * @return string
 */
function rubezh_base64url_encode( $data ) {
	return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
}

/**
 * Decode URL-safe base64.
 *
 * @param string $data Encoded.
 * @return string
 */
function rubezh_base64url_decode( $data ) {
	$data = strtr( $data, '-_', '+/' );
	$pad  = strlen( $data ) % 4;
	if ( $pad ) {
		$data .= str_repeat( '=', 4 - $pad );
	}
	$decoded = base64_decode( $data, true );
	return false === $decoded ? '' : $decoded;
}

/**
 * Ballot control shared by cards and the initiative page.
 *
 * @param int $post_id Initiative ID.
 * @return string
 */
function rubezh_vote_markup( $post_id ) {
	$status = rubezh_initiative_status( $post_id );
	$goal   = max( 1, (int) get_post_meta( $post_id, '_rubezh_goal', true ) );
	$votes  = (int) get_post_meta( $post_id, '_rubezh_votes', true );
	$voted  = rubezh_has_voted( $post_id );
	$closed = ( 'adopted' === $status );
	$ratio  = min( 100, (int) round( ( $votes / $goal ) * 100 ) );
	ob_start();
	?>
	<div class="rz-meter" role="meter" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( (string) $goal ); ?>" aria-valuenow="<?php echo esc_attr( (string) $votes ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: votes, 2: goal */ __( '%1$d из %2$d голосов', 'rubezh' ), $votes, $goal ) ); ?>">
		<span style="width:<?php echo esc_attr( (string) $ratio ); ?>%"></span>
	</div>
	<?php if ( $votes > 0 ) : ?>
		<p class="rz-meter__label"><strong data-vote-count><?php echo esc_html( (string) $votes ); ?></strong> <span><?php echo esc_html( sprintf( /* translators: goal */ __( 'из %d', 'rubezh' ), $goal ) ); ?></span></p>
	<?php else : ?>
		<p class="rz-meter__quiet" data-vote-empty><?php esc_html_e( 'Пока никто не голосовал', 'rubezh' ); ?></p>
		<p class="rz-meter__label" hidden><strong data-vote-count>0</strong> <span><?php echo esc_html( sprintf( __( 'из %d', 'rubezh' ), $goal ) ); ?></span></p>
	<?php endif; ?>
	<?php if ( $closed ) : ?>
		<p class="rz-vote-note"><?php esc_html_e( 'Голосование закрыто. Инициатива в работе редакции.', 'rubezh' ); ?></p>
	<?php else : ?>
		<form class="rz-vote" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-vote-form>
			<?php wp_nonce_field( 'rubezh_vote_' . $post_id, 'rubezh_vote_nonce' ); ?>
			<input type="hidden" name="action" value="rubezh_vote">
			<input type="hidden" name="initiative_id" value="<?php echo esc_attr( (string) $post_id ); ?>">
			<button class="rz-button" type="submit" data-vote="<?php echo esc_attr( (string) $post_id ); ?>" data-goal="<?php echo esc_attr( (string) $goal ); ?>" <?php disabled( $voted ); ?> aria-pressed="<?php echo $voted ? 'true' : 'false'; ?>">
				<?php echo esc_html( $voted ? __( 'Голос учтён', 'rubezh' ) : __( 'Отдать голос', 'rubezh' ) ); ?>
			</button>
			<p class="rz-vote-note" data-vote-note></p>
		</form>
	<?php endif; ?>
	<?php
	return (string) ob_get_clean();
}
