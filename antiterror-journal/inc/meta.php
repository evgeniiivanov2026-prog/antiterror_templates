<?php
/**
 * Profile and initiative fields.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'rubezh_register_meta' );

/**
 * Expose fields to the block editor.
 */
function rubezh_register_meta() {
	$string = array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'auth_callback'     => 'rubezh_can_edit_meta',
		'sanitize_callback' => 'sanitize_text_field',
	);
	foreach ( array( '_rubezh_role', '_rubezh_years', '_rubezh_place' ) as $key ) {
		register_post_meta( 'rubezh_hero', $key, $string );
	}
	register_post_meta(
		'rubezh_hero',
		'_rubezh_epitaph',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'auth_callback'     => 'rubezh_can_edit_meta',
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	register_post_meta(
		'rubezh_initiative',
		'_rubezh_status',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'auth_callback'     => 'rubezh_can_edit_meta',
			'sanitize_callback' => 'rubezh_sanitize_status',
		)
	);
	register_post_meta(
		'rubezh_initiative',
		'_rubezh_goal',
		array(
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => true,
			'auth_callback'     => 'rubezh_can_edit_meta',
			'sanitize_callback' => 'absint',
		)
	);
	$flag = array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'auth_callback'     => 'rubezh_can_edit_meta',
		'sanitize_callback' => 'rubezh_sanitize_flag',
	);
	register_post_meta( 'rubezh_hero', '_rubezh_sample', $flag );
	register_post_meta( 'rubezh_initiative', '_rubezh_sample', $flag );
	register_post_meta( 'post', '_rubezh_sample', $flag );
}

/**
 * Meta capability.
 *
 * @return bool
 */
function rubezh_can_edit_meta() {
	return current_user_can( 'edit_posts' );
}

/**
 * Whitelist initiative status.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function rubezh_sanitize_status( $value ) {
	$value = sanitize_key( (string) $value );
	return in_array( $value, array( 'collecting', 'review', 'adopted' ), true ) ? $value : 'collecting';
}

/**
 * Sample flag.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function rubezh_sanitize_flag( $value ) {
	return ( '1' === (string) $value || 1 === $value || true === $value ) ? '1' : '0';
}

add_action( 'add_meta_boxes', 'rubezh_meta_boxes' );

/**
 * Side panels in the editor.
 */
function rubezh_meta_boxes() {
	add_meta_box( 'rubezh_hero_box', __( 'Профиль памяти', 'rubezh' ), 'rubezh_hero_box', 'rubezh_hero', 'side', 'high' );
	add_meta_box( 'rubezh_initiative_box', __( 'Фонд инициатив', 'rubezh' ), 'rubezh_initiative_box', 'rubezh_initiative', 'side', 'high' );
	add_meta_box( 'rubezh_sample_box', __( 'Образец', 'rubezh' ), 'rubezh_sample_box', 'post', 'side', 'low' );
}

/**
 * Hero fields.
 *
 * @param WP_Post $post Post.
 */
function rubezh_hero_box( $post ) {
	wp_nonce_field( 'rubezh_hero', 'rubezh_hero_nonce' );
	$fields = array(
		'_rubezh_role'    => __( 'Роль', 'rubezh' ),
		'_rubezh_years'   => __( 'Годы', 'rubezh' ),
		'_rubezh_place'   => __( 'Место', 'rubezh' ),
	);
	foreach ( $fields as $key => $label ) {
		echo '<p><label for="' . esc_attr( $key ) . '"><strong>' . esc_html( $label ) . '</strong></label>';
		echo '<input class="widefat" type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( (string) get_post_meta( $post->ID, $key, true ) ) . '"></p>';
	}
	echo '<p><label for="_rubezh_epitaph"><strong>' . esc_html__( 'Короткая формула', 'rubezh' ) . '</strong></label>';
	echo '<textarea class="widefat" rows="4" id="_rubezh_epitaph" name="_rubezh_epitaph">' . esc_textarea( (string) get_post_meta( $post->ID, '_rubezh_epitaph', true ) ) . '</textarea></p>';
	rubezh_sample_field( $post->ID );
	echo '<p class="description">' . esc_html__( 'Публикуйте имя только по документу или с согласия близких. Без натуралистических подробностей.', 'rubezh' ) . '</p>';
}

/**
 * Initiative fields.
 *
 * @param WP_Post $post Post.
 */
function rubezh_initiative_box( $post ) {
	wp_nonce_field( 'rubezh_initiative', 'rubezh_initiative_nonce' );
	$status = rubezh_initiative_status( $post->ID );
	$goal   = (int) get_post_meta( $post->ID, '_rubezh_goal', true );
	if ( $goal < 1 ) {
		$goal = 100;
	}
	$votes = (int) get_post_meta( $post->ID, '_rubezh_votes', true );
	echo '<p><label for="_rubezh_status"><strong>' . esc_html__( 'Статус', 'rubezh' ) . '</strong></label>';
	echo '<select class="widefat" id="_rubezh_status" name="_rubezh_status">';
	foreach ( array( 'collecting', 'review', 'adopted' ) as $slug ) {
		echo '<option value="' . esc_attr( $slug ) . '" ' . selected( $status, $slug, false ) . '>' . esc_html( rubezh_status_label( $slug ) ) . '</option>';
	}
	echo '</select></p>';
	echo '<p><label for="_rubezh_goal"><strong>' . esc_html__( 'Ориентир голосов', 'rubezh' ) . '</strong></label>';
	echo '<input class="widefat" type="number" min="1" id="_rubezh_goal" name="_rubezh_goal" value="' . esc_attr( (string) $goal ) . '"></p>';
	echo '<p><strong>' . esc_html__( 'Сейчас голосов', 'rubezh' ) . ':</strong> ' . esc_html( (string) $votes ) . '</p>';
	echo '<p><label><input type="checkbox" name="rubezh_reset_votes" value="1"> ' . esc_html__( 'Обнулить счётчик', 'rubezh' ) . '</label></p>';
	rubezh_sample_field( $post->ID );
	echo '<p class="description">' . esc_html__( 'Голос на сайте — публичная поддержка, не юридический референдум. Статус «принята» закрывает голосование.', 'rubezh' ) . '</p>';
}

/**
 * Sample checkbox for essays.
 *
 * @param WP_Post $post Post.
 */
function rubezh_sample_box( $post ) {
	wp_nonce_field( 'rubezh_sample', 'rubezh_sample_nonce' );
	rubezh_sample_field( $post->ID );
}

/**
 * Shared sample checkbox.
 *
 * @param int $post_id Post ID.
 */
function rubezh_sample_field( $post_id ) {
	$checked = rubezh_is_sample( $post_id ) ? 'checked' : '';
	echo '<p><label><input type="checkbox" name="_rubezh_sample" value="1" ' . esc_attr( $checked ) . '> ' . esc_html__( 'Это демонстрационный материал', 'rubezh' ) . '</label></p>';
}

add_action( 'save_post_rubezh_hero', 'rubezh_save_hero' );
add_action( 'save_post_rubezh_initiative', 'rubezh_save_initiative' );
add_action( 'save_post_post', 'rubezh_save_sample_post' );

/**
 * Save a hero profile.
 *
 * @param int $post_id Post ID.
 */
function rubezh_save_hero( $post_id ) {
	if ( ! rubezh_meta_guard( $post_id, 'rubezh_hero_nonce', 'rubezh_hero' ) ) {
		return;
	}
	$map = array(
		'_rubezh_role'    => 'sanitize_text_field',
		'_rubezh_years'   => 'sanitize_text_field',
		'_rubezh_place'   => 'sanitize_text_field',
		'_rubezh_epitaph' => 'sanitize_textarea_field',
	);
	foreach ( $map as $key => $callback ) {
		$value = isset( $_POST[ $key ] ) ? call_user_func( $callback, wp_unslash( $_POST[ $key ] ) ) : '';
		update_post_meta( $post_id, $key, $value );
	}
	rubezh_save_sample_flag( $post_id );
}

/**
 * Save an initiative.
 *
 * @param int $post_id Post ID.
 */
function rubezh_save_initiative( $post_id ) {
	if ( ! rubezh_meta_guard( $post_id, 'rubezh_initiative_nonce', 'rubezh_initiative' ) ) {
		return;
	}
	$status = isset( $_POST['_rubezh_status'] ) ? rubezh_sanitize_status( wp_unslash( $_POST['_rubezh_status'] ) ) : 'collecting';
	$goal   = isset( $_POST['_rubezh_goal'] ) ? absint( wp_unslash( $_POST['_rubezh_goal'] ) ) : 100;
	update_post_meta( $post_id, '_rubezh_status', $status );
	update_post_meta( $post_id, '_rubezh_goal', max( 1, $goal ) );
	if ( ! empty( $_POST['rubezh_reset_votes'] ) ) {
		update_post_meta( $post_id, '_rubezh_votes', 0 );
	}
	rubezh_save_sample_flag( $post_id );
}

/**
 * Save the sample flag on essays.
 *
 * @param int $post_id Post ID.
 */
function rubezh_save_sample_post( $post_id ) {
	if ( ! rubezh_meta_guard( $post_id, 'rubezh_sample_nonce', 'rubezh_sample' ) ) {
		return;
	}
	rubezh_save_sample_flag( $post_id );
}

/**
 * Nonce and capability check.
 *
 * @param int    $post_id Post ID.
 * @param string $field   Nonce field.
 * @param string $action  Nonce action.
 * @return bool
 */
function rubezh_meta_guard( $post_id, $field, $action ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! isset( $_POST[ $field ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $field ] ) ), $action ) ) {
		return false;
	}
	return current_user_can( 'edit_post', $post_id );
}

/**
 * Persist the sample checkbox. Absent means "not a sample".
 *
 * @param int $post_id Post ID.
 */
function rubezh_save_sample_flag( $post_id ) {
	update_post_meta( $post_id, '_rubezh_sample', empty( $_POST['_rubezh_sample'] ) ? '0' : '1' );
}
