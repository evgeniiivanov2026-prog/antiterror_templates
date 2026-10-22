<?php
/**
 * Editorial fields for heroes and initiatives.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'atj_register_meta' );
/**
 * Register meta for the REST editor and sanitization.
 */
function atj_register_meta() {
	$auth = static function () {
		return current_user_can( 'edit_posts' );
	};

	register_post_meta(
		'atj_hero',
		'_atj_years',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => $auth,
		)
	);
	register_post_meta(
		'atj_hero',
		'_atj_role',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => $auth,
		)
	);
	register_post_meta(
		'atj_hero',
		'_atj_place',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => $auth,
		)
	);
	register_post_meta(
		'atj_initiative',
		'_atj_status',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => 'open',
			'show_in_rest'      => true,
			'sanitize_callback' => 'atj_sanitize_status',
			'auth_callback'     => $auth,
		)
	);
	register_post_meta(
		'atj_initiative',
		'_atj_vote_count',
		array(
			'type'          => 'integer',
			'single'        => true,
			'default'       => 0,
			'show_in_rest'  => false,
			'auth_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		)
	);
}

/**
 * Keep initiative status to the two editorial states.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function atj_sanitize_status( $value ) {
	$value = sanitize_key( (string) $value );
	return in_array( $value, array( 'open', 'closed' ), true ) ? $value : 'open';
}

add_action( 'add_meta_boxes', 'atj_meta_boxes' );
/**
 * Classic meta boxes next to the block editor.
 */
function atj_meta_boxes() {
	add_meta_box( 'atj_hero_details', __( 'Карточка памяти', 'atjournal' ), 'atj_hero_box', 'atj_hero', 'side', 'high' );
	add_meta_box( 'atj_initiative_details', __( 'Голосование', 'atjournal' ), 'atj_initiative_box', 'atj_initiative', 'side', 'high' );
}

/**
 * Hero fields.
 *
 * @param WP_Post $post Post.
 */
function atj_hero_box( $post ) {
	wp_nonce_field( 'atj_save_meta', 'atj_meta_nonce' );
	$years = get_post_meta( $post->ID, '_atj_years', true );
	$role  = get_post_meta( $post->ID, '_atj_role', true );
	$place = get_post_meta( $post->ID, '_atj_place', true );
	echo '<div class="atj-fields">';
	atj_field( 'atj_years', __( 'Годы', 'atjournal' ), $years, __( 'Например, 1978—2024. Публикуйте только подтверждённое.', 'atjournal' ) );
	atj_field( 'atj_role', __( 'Роль', 'atjournal' ), $role, __( 'Служба или общественное дело — без домыслов.', 'atjournal' ) );
	atj_field( 'atj_place', __( 'Место', 'atjournal' ), $place, '' );
	echo '<p class="description">' . esc_html__( 'Порядок на странице памяти задаётся полем «Порядок» в атрибутах страницы.', 'atjournal' ) . '</p>';
	echo '</div>';
}

/**
 * Initiative status. The tally itself is calculated from votes.
 *
 * @param WP_Post $post Post.
 */
function atj_initiative_box( $post ) {
	wp_nonce_field( 'atj_save_meta', 'atj_meta_nonce' );
	$status = atj_initiative_status( $post->ID );
	$count  = atj_get_vote_count( $post->ID );
	echo '<div class="atj-fields">';
	echo '<p><label for="atj_status"><strong>' . esc_html__( 'Состояние', 'atjournal' ) . '</strong></label></p>';
	echo '<select id="atj_status" name="atj_status" style="width:100%">';
	printf( '<option value="open" %s>%s</option>', selected( $status, 'open', false ), esc_html__( 'Голосование открыто', 'atjournal' ) );
	printf( '<option value="closed" %s>%s</option>', selected( $status, 'closed', false ), esc_html__( 'Голосование завершено', 'atjournal' ) );
	echo '</select>';
	echo '<p class="description">' . esc_html__( 'Краткое описание для ленты — это отрывок записи. Число голосов считается по реальным голосам и вручную не правится.', 'atjournal' ) . '</p>';
	echo '<p><strong>' . esc_html__( 'Сейчас', 'atjournal' ) . ':</strong> ' . esc_html( number_format_i18n( $count ) ) . ' ' . esc_html( atj_votes_word( $count ) ) . '</p>';
	echo '</div>';
}

/**
 * One labeled text field.
 *
 * @param string $name  Field name.
 * @param string $label Label.
 * @param string $value Value.
 * @param string $help  Help text.
 */
function atj_field( $name, $label, $value, $help ) {
	echo '<p><label for="' . esc_attr( $name ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
	echo '<input type="text" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" style="width:100%"></p>';
	if ( $help ) {
		echo '<p class="description">' . esc_html( $help ) . '</p>';
	}
}

add_action( 'save_post', 'atj_save_meta' );
/**
 * Persist editorial fields.
 *
 * @param int $post_id Post ID.
 */
function atj_save_meta( $post_id ) {
	if ( ! isset( $_POST['atj_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['atj_meta_nonce'] ) ), 'atj_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$type = get_post_type( $post_id );
	if ( 'atj_hero' === $type ) {
		update_post_meta( $post_id, '_atj_years', sanitize_text_field( wp_unslash( $_POST['atj_years'] ?? '' ) ) );
		update_post_meta( $post_id, '_atj_role', sanitize_text_field( wp_unslash( $_POST['atj_role'] ?? '' ) ) );
		update_post_meta( $post_id, '_atj_place', sanitize_text_field( wp_unslash( $_POST['atj_place'] ?? '' ) ) );
	}

	if ( 'atj_initiative' === $type ) {
		update_post_meta( $post_id, '_atj_status', atj_sanitize_status( wp_unslash( $_POST['atj_status'] ?? 'open' ) ) );
		if ( '' === get_post_meta( $post_id, '_atj_vote_count', true ) ) {
			update_post_meta( $post_id, '_atj_vote_count', 0 );
		}
	}
}

add_filter( 'manage_atj_hero_posts_columns', 'atj_hero_columns' );
/**
 * Hero list columns.
 *
 * @param array<string, string> $columns Columns.
 * @return array<string, string>
 */
function atj_hero_columns( $columns ) {
	$columns['atj_role']  = __( 'Роль', 'atjournal' );
	$columns['atj_years'] = __( 'Годы', 'atjournal' );
	return $columns;
}

add_action( 'manage_atj_hero_posts_custom_column', 'atj_hero_column', 10, 2 );
/**
 * Hero column values.
 *
 * @param string $column Column.
 * @param int    $post_id Post ID.
 */
function atj_hero_column( $column, $post_id ) {
	if ( 'atj_role' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_atj_role', true ) );
	}
	if ( 'atj_years' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_atj_years', true ) );
	}
}

add_filter( 'manage_atj_initiative_posts_columns', 'atj_initiative_columns' );
/**
 * Initiative list columns.
 *
 * @param array<string, string> $columns Columns.
 * @return array<string, string>
 */
function atj_initiative_columns( $columns ) {
	$columns['atj_votes']  = __( 'Голоса', 'atjournal' );
	$columns['atj_status'] = __( 'Состояние', 'atjournal' );
	return $columns;
}

add_action( 'manage_atj_initiative_posts_custom_column', 'atj_initiative_column', 10, 2 );
/**
 * Initiative column values.
 *
 * @param string $column Column.
 * @param int    $post_id Post ID.
 */
function atj_initiative_column( $column, $post_id ) {
	if ( 'atj_votes' === $column ) {
		echo esc_html( number_format_i18n( atj_get_vote_count( $post_id ) ) );
	}
	if ( 'atj_status' === $column ) {
		echo 'open' === atj_initiative_status( $post_id )
			? esc_html__( 'Открыто', 'atjournal' )
			: esc_html__( 'Завершено', 'atjournal' );
	}
}
