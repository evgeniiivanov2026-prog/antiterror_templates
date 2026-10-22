<?php
/**
 * Metaboxes for heroes and initiatives.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'atj_register_metaboxes' );
function atj_register_metaboxes() {
	add_meta_box( 'atj_hero_meta', __( 'Сведения о герое', 'antiterror-journal' ), 'atj_hero_metabox', 'atj_hero', 'side', 'high' );
	add_meta_box( 'atj_initiative_meta', __( 'Параметры инициативы', 'antiterror-journal' ), 'atj_initiative_metabox', 'atj_initiative', 'side', 'high' );
}

function atj_hero_metabox( $post ) {
	wp_nonce_field( 'atj_hero_meta', 'atj_hero_nonce' );
	$fields = array(
		'rank'           => array( __( 'Звание / должность', 'antiterror-journal' ), (string) get_post_meta( $post->ID, '_atj_rank', true ) ),
		'years'          => array( __( 'Годы жизни или службы', 'antiterror-journal' ), (string) get_post_meta( $post->ID, '_atj_years', true ) ),
		'service_label'  => array( __( 'Подразделение', 'antiterror-journal' ), (string) get_post_meta( $post->ID, '_atj_service_label', true ) ),
		'memorial_date'  => array( __( 'Дата памяти', 'antiterror-journal' ), (string) get_post_meta( $post->ID, '_atj_memorial_date', true ) ),
		'awards'         => array( __( 'Награды (кратко)', 'antiterror-journal' ), (string) get_post_meta( $post->ID, '_atj_awards', true ) ),
	);
	foreach ( $fields as $key => $field ) {
		printf(
			'<p><label for="atj_%1$s">%2$s</label><input class="widefat" type="text" id="atj_%1$s" name="atj_%1$s" value="%3$s"></p>',
			esc_attr( $key ),
			esc_html( $field[0] ),
			esc_attr( $field[1] )
		);
	}
	$featured = (bool) get_post_meta( $post->ID, '_atj_featured', true );
	printf(
		'<p><label><input type="checkbox" name="atj_featured" value="1" %s> %s</label></p>',
		checked( $featured, true, false ),
		esc_html__( 'Показать на главной', 'antiterror-journal' )
	);
}

function atj_initiative_metabox( $post ) {
	wp_nonce_field( 'atj_initiative_meta', 'atj_initiative_nonce' );
	$status   = get_post_meta( $post->ID, '_atj_status', true ) ?: 'open';
	$deadline = (string) get_post_meta( $post->ID, '_atj_deadline', true );
	$author   = (string) get_post_meta( $post->ID, '_atj_author_label', true );
	$region   = (string) get_post_meta( $post->ID, '_atj_region', true );
	$votes    = (int) get_post_meta( $post->ID, '_atj_votes', true );
	$featured = (bool) get_post_meta( $post->ID, '_atj_featured', true );

	echo '<p><label for="atj_status">' . esc_html__( 'Статус', 'antiterror-journal' ) . '</label>';
	echo '<select class="widefat" id="atj_status" name="atj_status">';
	foreach ( array( 'open' => __( 'Открыта', 'antiterror-journal' ), 'closed' => __( 'Закрыта', 'antiterror-journal' ) ) as $value => $label ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $value ), selected( $status, $value, false ), esc_html( $label ) );
	}
	echo '</select></p>';

	printf( '<p><label for="atj_deadline">%s</label><input class="widefat" type="date" id="atj_deadline" name="atj_deadline" value="%s"></p>', esc_html__( 'Срок голосования', 'antiterror-journal' ), esc_attr( $deadline ) );
	printf( '<p><label for="atj_author_label">%s</label><input class="widefat" type="text" id="atj_author_label" name="atj_author_label" value="%s"></p>', esc_html__( 'Автор инициативы', 'antiterror-journal' ), esc_attr( $author ) );
	printf( '<p><label for="atj_region">%s</label><input class="widefat" type="text" id="atj_region" name="atj_region" value="%s"></p>', esc_html__( 'Регион', 'antiterror-journal' ), esc_attr( $region ) );
	printf( '<p><label for="atj_votes">%s</label><input class="widefat" type="number" min="0" id="atj_votes" name="atj_votes" value="%d"></p>', esc_html__( 'Голоса', 'antiterror-journal' ), $votes );
	printf(
		'<p><label><input type="checkbox" name="atj_featured" value="1" %s> %s</label></p>',
		checked( $featured, true, false ),
		esc_html__( 'Показать на главной', 'antiterror-journal' )
	);
}

add_action( 'save_post_atj_hero', 'atj_save_hero_meta' );
function atj_save_hero_meta( $post_id ) {
	if ( ! isset( $_POST['atj_hero_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['atj_hero_nonce'] ) ), 'atj_hero_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$keys = array( 'rank', 'years', 'service_label', 'memorial_date', 'awards' );
	foreach ( $keys as $key ) {
		$value = isset( $_POST[ 'atj_' . $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'atj_' . $key ] ) ) : '';
		update_post_meta( $post_id, '_atj_' . $key, $value );
	}
	update_post_meta( $post_id, '_atj_featured', empty( $_POST['atj_featured'] ) ? '' : '1' );
}

add_action( 'save_post_atj_initiative', 'atj_save_initiative_meta' );
function atj_save_initiative_meta( $post_id ) {
	if ( ! isset( $_POST['atj_initiative_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['atj_initiative_nonce'] ) ), 'atj_initiative_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$status = isset( $_POST['atj_status'] ) && 'closed' === $_POST['atj_status'] ? 'closed' : 'open';
	update_post_meta( $post_id, '_atj_status', $status );
	update_post_meta( $post_id, '_atj_deadline', isset( $_POST['atj_deadline'] ) ? sanitize_text_field( wp_unslash( $_POST['atj_deadline'] ) ) : '' );
	update_post_meta( $post_id, '_atj_author_label', isset( $_POST['atj_author_label'] ) ? sanitize_text_field( wp_unslash( $_POST['atj_author_label'] ) ) : '' );
	update_post_meta( $post_id, '_atj_region', isset( $_POST['atj_region'] ) ? sanitize_text_field( wp_unslash( $_POST['atj_region'] ) ) : '' );
	update_post_meta( $post_id, '_atj_featured', empty( $_POST['atj_featured'] ) ? '' : '1' );

	if ( isset( $_POST['atj_votes'] ) && current_user_can( 'manage_options' ) ) {
		update_post_meta( $post_id, '_atj_votes', max( 0, absint( $_POST['atj_votes'] ) ) );
	}
}

add_filter( 'manage_atj_initiative_posts_columns', 'atj_initiative_columns' );
function atj_initiative_columns( $columns ) {
	$columns['atj_votes']  = __( 'Голоса', 'antiterror-journal' );
	$columns['atj_status'] = __( 'Статус', 'antiterror-journal' );
	return $columns;
}

add_action( 'manage_atj_initiative_posts_custom_column', 'atj_initiative_column_content', 10, 2 );
function atj_initiative_column_content( $column, $post_id ) {
	if ( 'atj_votes' === $column ) {
		echo (int) get_post_meta( $post_id, '_atj_votes', true );
	}
	if ( 'atj_status' === $column ) {
		$status = get_post_meta( $post_id, '_atj_status', true ) ?: 'open';
		echo 'closed' === $status ? esc_html__( 'Закрыта', 'antiterror-journal' ) : esc_html__( 'Открыта', 'antiterror-journal' );
	}
}

add_filter( 'manage_edit-atj_initiative_sortable_columns', 'atj_initiative_sortable' );
function atj_initiative_sortable( $columns ) {
	$columns['atj_votes'] = 'atj_votes';
	return $columns;
}

add_action( 'pre_get_posts', 'atj_sort_initiatives_by_votes' );
function atj_sort_initiatives_by_votes( $query ) {
	if ( is_admin() && $query->is_main_query() && 'atj_votes' === $query->get( 'orderby' ) ) {
		$query->set( 'meta_key', '_atj_votes' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
