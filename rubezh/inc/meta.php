<?php
/**
 * Мета-поля героев и инициатив.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'rubezh_meta_boxes' );
function rubezh_meta_boxes() {
	add_meta_box( 'rubezh_hero', __( 'Данные героя', 'rubezh' ), 'rubezh_hero_box', 'hero', 'side', 'high' );
	add_meta_box( 'rubezh_initiative', __( 'Данные инициативы', 'rubezh' ), 'rubezh_initiative_box', 'initiative', 'side', 'high' );
}

function rubezh_hero_box( $post ) {
	wp_nonce_field( 'rubezh_hero_meta', 'rubezh_hero_nonce' );
	$years   = get_post_meta( $post->ID, '_rubezh_years', true );
	$rank    = get_post_meta( $post->ID, '_rubezh_rank', true );
	$place   = get_post_meta( $post->ID, '_rubezh_place', true );
	$line    = get_post_meta( $post->ID, '_rubezh_line', true );
	?>
	<p><label for="rubezh_years"><?php esc_html_e( 'Годы жизни', 'rubezh' ); ?></label>
	<input class="widefat" type="text" id="rubezh_years" name="rubezh_years" value="<?php echo esc_attr( $years ); ?>" placeholder="1978–2004"></p>
	<p><label for="rubezh_rank"><?php esc_html_e( 'Звание / статус', 'rubezh' ); ?></label>
	<input class="widefat" type="text" id="rubezh_rank" name="rubezh_rank" value="<?php echo esc_attr( $rank ); ?>"></p>
	<p><label for="rubezh_place"><?php esc_html_e( 'Место подвига / службы', 'rubezh' ); ?></label>
	<input class="widefat" type="text" id="rubezh_place" name="rubezh_place" value="<?php echo esc_attr( $place ); ?>"></p>
	<p><label for="rubezh_line"><?php esc_html_e( 'Короткая строка памяти', 'rubezh' ); ?></label>
	<textarea class="widefat" id="rubezh_line" name="rubezh_line" rows="3"><?php echo esc_textarea( $line ); ?></textarea></p>
	<?php
}

function rubezh_initiative_box( $post ) {
	wp_nonce_field( 'rubezh_initiative_meta', 'rubezh_initiative_nonce' );
	$status = get_post_meta( $post->ID, '_rubezh_status', true );
	$author = get_post_meta( $post->ID, '_rubezh_author', true );
	$votes  = (int) get_post_meta( $post->ID, '_rubezh_votes', true );
	?>
	<p><label for="rubezh_status"><?php esc_html_e( 'Статус', 'rubezh' ); ?></label>
	<select class="widefat" id="rubezh_status" name="rubezh_status">
		<?php
		$opts = array(
			'open'      => __( 'Открыта для голосов', 'rubezh' ),
			'review'    => __( 'На рассмотрении', 'rubezh' ),
			'accepted'  => __( 'Принята фондом', 'rubezh' ),
			'completed' => __( 'Реализована', 'rubezh' ),
		);
		foreach ( $opts as $key => $label ) {
			printf( '<option value="%s"%s>%s</option>', esc_attr( $key ), selected( $status, $key, false ), esc_html( $label ) );
		}
		?>
	</select></p>
	<p><label for="rubezh_author"><?php esc_html_e( 'Автор инициативы', 'rubezh' ); ?></label>
	<input class="widefat" type="text" id="rubezh_author" name="rubezh_author" value="<?php echo esc_attr( $author ); ?>"></p>
	<p><label for="rubezh_votes"><?php esc_html_e( 'Голоса', 'rubezh' ); ?></label>
	<input class="widefat" type="number" min="0" id="rubezh_votes" name="rubezh_votes" value="<?php echo esc_attr( $votes ); ?>"></p>
	<?php
}

add_action( 'save_post_hero', 'rubezh_save_hero' );
function rubezh_save_hero( $post_id ) {
	if ( ! isset( $_POST['rubezh_hero_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rubezh_hero_nonce'] ) ), 'rubezh_hero_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$fields = array( 'rubezh_years', 'rubezh_rank', 'rubezh_place', 'rubezh_line' );
	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_' . $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
}

add_action( 'save_post_initiative', 'rubezh_save_initiative' );
function rubezh_save_initiative( $post_id ) {
	if ( ! isset( $_POST['rubezh_initiative_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rubezh_initiative_nonce'] ) ), 'rubezh_initiative_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['rubezh_status'] ) ) {
		update_post_meta( $post_id, '_rubezh_status', sanitize_key( wp_unslash( $_POST['rubezh_status'] ) ) );
	}
	if ( isset( $_POST['rubezh_author'] ) ) {
		update_post_meta( $post_id, '_rubezh_author', sanitize_text_field( wp_unslash( $_POST['rubezh_author'] ) ) );
	}
	if ( isset( $_POST['rubezh_votes'] ) ) {
		update_post_meta( $post_id, '_rubezh_votes', max( 0, absint( $_POST['rubezh_votes'] ) ) );
	}
}

add_action( 'save_post_initiative', 'rubezh_ensure_votes_meta', 20 );
function rubezh_ensure_votes_meta( $post_id ) {
	if ( '' === get_post_meta( $post_id, '_rubezh_votes', true ) ) {
		update_post_meta( $post_id, '_rubezh_votes', 0 );
	}
	if ( '' === get_post_meta( $post_id, '_rubezh_status', true ) ) {
		update_post_meta( $post_id, '_rubezh_status', 'open' );
	}
}
