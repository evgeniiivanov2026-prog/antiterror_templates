<?php
/**
 * Metaboxes for heroes and initiatives.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'vigilum_register_metaboxes' );
function vigilum_register_metaboxes() {
	add_meta_box(
		'vigilum_hero_meta',
		__( 'Данные памяти', 'vigilum' ),
		'vigilum_hero_metabox',
		'vigilum_hero',
		'side',
		'high'
	);

	add_meta_box(
		'vigilum_initiative_meta',
		__( 'Параметры инициативы', 'vigilum' ),
		'vigilum_initiative_metabox',
		'vigilum_initiative',
		'side',
		'high'
	);
}

function vigilum_hero_metabox( $post ) {
	wp_nonce_field( 'vigilum_hero_meta', 'vigilum_hero_nonce' );
	$years  = get_post_meta( $post->ID, '_vigilum_years', true );
	$role   = get_post_meta( $post->ID, '_vigilum_rank', true );
	$place  = get_post_meta( $post->ID, '_vigilum_place', true );
	$award  = get_post_meta( $post->ID, '_vigilum_award', true );
	?>
	<p>
		<label for="vigilum_years"><?php esc_html_e( 'Годы жизни', 'vigilum' ); ?></label>
		<input type="text" class="widefat" id="vigilum_years" name="vigilum_years" value="<?php echo esc_attr( $years ); ?>" placeholder="1974–2004">
	</p>
	<p>
		<label for="vigilum_rank"><?php esc_html_e( 'Звание / должность', 'vigilum' ); ?></label>
		<input type="text" class="widefat" id="vigilum_rank" name="vigilum_rank" value="<?php echo esc_attr( $role ); ?>">
	</p>
	<p>
		<label for="vigilum_place"><?php esc_html_e( 'Место службы / события', 'vigilum' ); ?></label>
		<input type="text" class="widefat" id="vigilum_place" name="vigilum_place" value="<?php echo esc_attr( $place ); ?>">
	</p>
	<p>
		<label for="vigilum_award"><?php esc_html_e( 'Награда (если есть)', 'vigilum' ); ?></label>
		<input type="text" class="widefat" id="vigilum_award" name="vigilum_award" value="<?php echo esc_attr( $award ); ?>">
	</p>
	<?php
}

function vigilum_initiative_metabox( $post ) {
	wp_nonce_field( 'vigilum_initiative_meta', 'vigilum_initiative_nonce' );
	$author = get_post_meta( $post->ID, '_vigilum_author', true );
	$goal   = get_post_meta( $post->ID, '_vigilum_goal', true );
	$votes  = (int) get_post_meta( $post->ID, '_vigilum_votes', true );
	$status = get_post_meta( $post->ID, '_vigilum_status', true );
	if ( ! $status ) {
		$status = 'review';
	}
	?>
	<p>
		<label for="vigilum_author"><?php esc_html_e( 'Автор / организация', 'vigilum' ); ?></label>
		<input type="text" class="widefat" id="vigilum_author" name="vigilum_author" value="<?php echo esc_attr( $author ); ?>">
	</p>
	<p>
		<label for="vigilum_status"><?php esc_html_e( 'Статус', 'vigilum' ); ?></label>
		<select class="widefat" id="vigilum_status" name="vigilum_status">
			<option value="review" <?php selected( $status, 'review' ); ?>><?php esc_html_e( 'На рассмотрении', 'vigilum' ); ?></option>
			<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'В работе', 'vigilum' ); ?></option>
			<option value="supported" <?php selected( $status, 'supported' ); ?>><?php esc_html_e( 'Поддержана', 'vigilum' ); ?></option>
			<option value="closed" <?php selected( $status, 'closed' ); ?>><?php esc_html_e( 'Завершена', 'vigilum' ); ?></option>
		</select>
	</p>
	<p>
		<label for="vigilum_goal"><?php esc_html_e( 'Цель голосов (необязательно)', 'vigilum' ); ?></label>
		<input type="number" min="0" class="widefat" id="vigilum_goal" name="vigilum_goal" value="<?php echo esc_attr( $goal ); ?>">
	</p>
	<p>
		<label for="vigilum_votes"><?php esc_html_e( 'Текущие голоса', 'vigilum' ); ?></label>
		<input type="number" min="0" class="widefat" id="vigilum_votes" name="vigilum_votes" value="<?php echo esc_attr( $votes ); ?>">
	</p>
	<?php
}

add_action( 'save_post_vigilum_hero', 'vigilum_save_hero_meta' );
function vigilum_save_hero_meta( $post_id ) {
	if ( ! isset( $_POST['vigilum_hero_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vigilum_hero_nonce'] ) ), 'vigilum_hero_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array( 'vigilum_years', 'vigilum_rank', 'vigilum_place', 'vigilum_award' );
	foreach ( $fields as $field ) {
		$key = '_' . $field;
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
}

add_action( 'save_post_vigilum_initiative', 'vigilum_save_initiative_meta' );
function vigilum_save_initiative_meta( $post_id ) {
	if ( ! isset( $_POST['vigilum_initiative_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vigilum_initiative_nonce'] ) ), 'vigilum_initiative_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['vigilum_author'] ) ) {
		update_post_meta( $post_id, '_vigilum_author', sanitize_text_field( wp_unslash( $_POST['vigilum_author'] ) ) );
	}
	if ( isset( $_POST['vigilum_status'] ) ) {
		$status = sanitize_key( wp_unslash( $_POST['vigilum_status'] ) );
		$allowed = array( 'review', 'active', 'supported', 'closed' );
		if ( in_array( $status, $allowed, true ) ) {
			update_post_meta( $post_id, '_vigilum_status', $status );
		}
	}
	if ( isset( $_POST['vigilum_goal'] ) ) {
		update_post_meta( $post_id, '_vigilum_goal', max( 0, (int) $_POST['vigilum_goal'] ) );
	}
	if ( isset( $_POST['vigilum_votes'] ) ) {
		update_post_meta( $post_id, '_vigilum_votes', max( 0, (int) $_POST['vigilum_votes'] ) );
	}
}

/**
 * @param int $post_id
 * @return array<string,string>
 */
function vigilum_hero_data( $post_id ) {
	return array(
		'years' => (string) get_post_meta( $post_id, '_vigilum_years', true ),
		'rank'  => (string) get_post_meta( $post_id, '_vigilum_rank', true ),
		'place' => (string) get_post_meta( $post_id, '_vigilum_place', true ),
		'award' => (string) get_post_meta( $post_id, '_vigilum_award', true ),
	);
}

/**
 * @param int $post_id
 * @return array<string,mixed>
 */
function vigilum_initiative_data( $post_id ) {
	$status = (string) get_post_meta( $post_id, '_vigilum_status', true );
	if ( ! $status ) {
		$status = 'review';
	}
	return array(
		'author' => (string) get_post_meta( $post_id, '_vigilum_author', true ),
		'status' => $status,
		'goal'   => (int) get_post_meta( $post_id, '_vigilum_goal', true ),
		'votes'  => (int) get_post_meta( $post_id, '_vigilum_votes', true ),
	);
}

function vigilum_status_label( $status ) {
	$map = array(
		'review'    => __( 'На рассмотрении', 'vigilum' ),
		'active'    => __( 'В работе', 'vigilum' ),
		'supported' => __( 'Поддержана', 'vigilum' ),
		'closed'    => __( 'Завершена', 'vigilum' ),
	);
	return $map[ $status ] ?? $status;
}
