<?php
/**
 * Поля карточки памяти и инициативы.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'rubezh_register_meta' );
/**
 * Метаполя доступны редактору блоков.
 */
function rubezh_register_meta() {
	$auth = static function () {
		return current_user_can( 'edit_posts' );
	};
	$string = array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'auth_callback'     => $auth,
		'sanitize_callback' => 'sanitize_text_field',
	);

	foreach ( array( '_rubezh_years', '_rubezh_role', '_rubezh_place' ) as $key ) {
		register_post_meta( 'hero', $key, $string );
	}
	foreach ( array( '_rubezh_region', '_rubezh_proposer' ) as $key ) {
		register_post_meta( 'initiative', $key, $string );
	}
	register_post_meta(
		'initiative',
		'_rubezh_status',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'auth_callback'     => $auth,
			'sanitize_callback' => 'rubezh_sanitize_status',
		)
	);
}

/**
 * @param string $value Статус.
 * @return string
 */
function rubezh_sanitize_status( $value ) {
	$labels = rubezh_status_labels();
	$value  = sanitize_key( (string) $value );
	return isset( $labels[ $value ] ) ? $value : 'open';
}

add_action( 'add_meta_boxes', 'rubezh_meta_boxes' );
/**
 * Классические поля, они же видны в редакторе блоков.
 */
function rubezh_meta_boxes() {
	add_meta_box(
		'rubezh_hero',
		__( 'Сведения для раздела памяти', 'rubezh' ),
		'rubezh_hero_box',
		'hero',
		'side',
		'high'
	);
	add_meta_box(
		'rubezh_initiative',
		__( 'Фонд инициатив', 'rubezh' ),
		'rubezh_initiative_box',
		'initiative',
		'side',
		'high'
	);
}

/**
 * @param WP_Post $post Запись.
 */
function rubezh_hero_box( $post ) {
	wp_nonce_field( 'rubezh_meta', 'rubezh_meta_nonce' );
	$years = get_post_meta( $post->ID, '_rubezh_years', true );
	$role  = get_post_meta( $post->ID, '_rubezh_role', true );
	$place = get_post_meta( $post->ID, '_rubezh_place', true );
	echo '<p><label for="rubezh_years"><strong>' . esc_html__( 'Годы', 'rubezh' ) . '</strong></label><br>';
	echo '<input class="widefat" id="rubezh_years" name="rubezh_years" value="' . esc_attr( (string) $years ) . '" placeholder="' . esc_attr__( 'гггг—гггг', 'rubezh' ) . '"></p>';
	echo '<p><label for="rubezh_role"><strong>' . esc_html__( 'Роль или служба', 'rubezh' ) . '</strong></label><br>';
	echo '<input class="widefat" id="rubezh_role" name="rubezh_role" value="' . esc_attr( (string) $role ) . '"></p>';
	echo '<p><label for="rubezh_place"><strong>' . esc_html__( 'Населённый пункт', 'rubezh' ) . '</strong></label><br>';
	echo '<input class="widefat" id="rubezh_place" name="rubezh_place" value="' . esc_attr( (string) $place ) . '"></p>';
	echo '<p class="description">' . esc_html__( 'Публикуйте только проверенные сведения. Заголовок записи — имя.', 'rubezh' ) . '</p>';
}

/**
 * @param WP_Post $post Запись.
 */
function rubezh_initiative_box( $post ) {
	wp_nonce_field( 'rubezh_meta', 'rubezh_meta_nonce' );
	$status   = rubezh_initiative_status( $post->ID );
	$region   = get_post_meta( $post->ID, '_rubezh_region', true );
	$proposer = get_post_meta( $post->ID, '_rubezh_proposer', true );
	$votes    = rubezh_vote_count( $post->ID );

	echo '<p><label for="rubezh_status"><strong>' . esc_html__( 'Статус', 'rubezh' ) . '</strong></label><br>';
	echo '<select class="widefat" id="rubezh_status" name="rubezh_status">';
	foreach ( rubezh_status_labels() as $key => $label ) {
		echo '<option value="' . esc_attr( $key ) . '" ' . selected( $status, $key, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select></p>';
	echo '<p><label for="rubezh_region"><strong>' . esc_html__( 'Регион', 'rubezh' ) . '</strong></label><br>';
	echo '<input class="widefat" id="rubezh_region" name="rubezh_region" value="' . esc_attr( (string) $region ) . '"></p>';
	echo '<p><label for="rubezh_proposer"><strong>' . esc_html__( 'Кто предложил', 'rubezh' ) . '</strong></label><br>';
	echo '<input class="widefat" id="rubezh_proposer" name="rubezh_proposer" value="' . esc_attr( (string) $proposer ) . '"></p>';
	echo '<p class="description">' . esc_html( sprintf( /* translators: %s: vote count label */ __( 'Сейчас: %s. Счётчик меняют голоса на сайте, вручную он не правится.', 'rubezh' ), rubezh_votes_label( $votes ) ) ) . '</p>';
}

add_action( 'save_post_hero', 'rubezh_save_hero' );
/**
 * @param int $post_id Запись.
 */
function rubezh_save_hero( $post_id ) {
	if ( ! rubezh_meta_request_ok( $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_rubezh_years', sanitize_text_field( wp_unslash( $_POST['rubezh_years'] ?? '' ) ) );
	update_post_meta( $post_id, '_rubezh_role', sanitize_text_field( wp_unslash( $_POST['rubezh_role'] ?? '' ) ) );
	update_post_meta( $post_id, '_rubezh_place', sanitize_text_field( wp_unslash( $_POST['rubezh_place'] ?? '' ) ) );
}

add_action( 'save_post_initiative', 'rubezh_save_initiative' );
/**
 * @param int $post_id Запись.
 */
function rubezh_save_initiative( $post_id ) {
	if ( ! rubezh_meta_request_ok( $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_rubezh_status', rubezh_sanitize_status( wp_unslash( $_POST['rubezh_status'] ?? 'open' ) ) );
	update_post_meta( $post_id, '_rubezh_region', sanitize_text_field( wp_unslash( $_POST['rubezh_region'] ?? '' ) ) );
	update_post_meta( $post_id, '_rubezh_proposer', sanitize_text_field( wp_unslash( $_POST['rubezh_proposer'] ?? '' ) ) );
}

/**
 * Сохраняем поля только из формы метабокса, чтобы REST-сохранение их не стирало.
 *
 * @param int $post_id Запись.
 * @return bool
 */
function rubezh_meta_request_ok( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! isset( $_POST['rubezh_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rubezh_meta_nonce'] ) ), 'rubezh_meta' ) ) {
		return false;
	}
	return current_user_can( 'edit_post', $post_id );
}

add_filter( 'manage_initiative_posts_columns', 'rubezh_initiative_columns' );
/**
 * @param array<string, string> $columns Колонки.
 * @return array<string, string>
 */
function rubezh_initiative_columns( $columns ) {
	$columns['rubezh_status'] = __( 'Статус', 'rubezh' );
	$columns['rubezh_votes']  = __( 'Голоса', 'rubezh' );
	return $columns;
}

add_action( 'manage_initiative_posts_custom_column', 'rubezh_initiative_column', 10, 2 );
/**
 * @param string $column Колонка.
 * @param int    $post_id Запись.
 */
function rubezh_initiative_column( $column, $post_id ) {
	if ( 'rubezh_status' === $column ) {
		echo esc_html( rubezh_status_label( rubezh_initiative_status( $post_id ) ) );
	}
	if ( 'rubezh_votes' === $column ) {
		echo esc_html( (string) rubezh_vote_count( $post_id ) );
	}
}

add_filter( 'manage_hero_posts_columns', 'rubezh_hero_columns' );
/**
 * @param array<string, string> $columns Колонки.
 * @return array<string, string>
 */
function rubezh_hero_columns( $columns ) {
	$columns['rubezh_years'] = __( 'Годы', 'rubezh' );
	$columns['rubezh_place'] = __( 'Место', 'rubezh' );
	return $columns;
}

add_action( 'manage_hero_posts_custom_column', 'rubezh_hero_column', 10, 2 );
/**
 * @param string $column Колонка.
 * @param int    $post_id Запись.
 */
function rubezh_hero_column( $column, $post_id ) {
	if ( 'rubezh_years' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_rubezh_years', true ) );
	}
	if ( 'rubezh_place' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_rubezh_place', true ) );
	}
}
