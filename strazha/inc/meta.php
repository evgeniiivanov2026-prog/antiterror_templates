<?php
/**
 * Поля героя и инициативы.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'strazha_register_meta' );
/**
 * Мета для редактора и REST.
 */
function strazha_register_meta() {
	$auth = static function () {
		return current_user_can( 'edit_posts' );
	};

	foreach ( array( '_strazha_years', '_strazha_role', '_strazha_place' ) as $key ) {
		register_post_meta(
			'hero',
			$key,
			array(
				'single'            => true,
				'type'              => 'string',
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => $auth,
			)
		);
	}

	register_post_meta(
		'initiative',
		'_strazha_status',
		array(
			'single'            => true,
			'type'              => 'string',
			'show_in_rest'      => true,
			'sanitize_callback' => 'strazha_sanitize_status',
			'auth_callback'     => $auth,
		)
	);
}

/**
 * Допустимый статус.
 *
 * @param mixed $value Значение.
 */
function strazha_sanitize_status( $value ) {
	$value = sanitize_key( (string) $value );
	return isset( strazha_statuses()[ $value ] ) ? $value : 'collecting';
}

add_action( 'add_meta_boxes', 'strazha_meta_boxes' );
/**
 * Метабоксы.
 */
function strazha_meta_boxes() {
	add_meta_box(
		'strazha_hero',
		__( 'Карточка памяти', 'strazha' ),
		'strazha_hero_box',
		'hero',
		'side',
		'high'
	);
	add_meta_box(
		'strazha_initiative',
		__( 'Голосование', 'strazha' ),
		'strazha_initiative_box',
		'initiative',
		'side',
		'high'
	);
}

/**
 * Поля героя.
 *
 * @param WP_Post $post Запись.
 */
function strazha_hero_box( $post ) {
	wp_nonce_field( 'strazha_meta_' . $post->ID, 'strazha_meta_nonce' );
	$fields = array(
		'_strazha_years' => __( 'Годы', 'strazha' ),
		'_strazha_role'  => __( 'Служба или роль', 'strazha' ),
		'_strazha_place' => __( 'Место', 'strazha' ),
	);
	foreach ( $fields as $key => $label ) {
		printf(
			'<p><label for="%1$s"><strong>%2$s</strong></label><input class="widefat" type="text" id="%1$s" name="%1$s" value="%3$s"></p>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( (string) get_post_meta( $post->ID, $key, true ) )
		);
	}
	echo '<p class="description">' . esc_html__( 'Годы можно записать как 1978—2024. Биография — в основном тексте, короткая строка для стены — в отрывке. Порядок на стене задаёт поле «Порядок».', 'strazha' ) . '</p>';
}

/**
 * Поля инициативы.
 *
 * @param WP_Post $post Запись.
 */
function strazha_initiative_box( $post ) {
	wp_nonce_field( 'strazha_meta_' . $post->ID, 'strazha_meta_nonce' );
	$current = strazha_initiative_status( $post->ID );
	echo '<p><label for="_strazha_status"><strong>' . esc_html__( 'Статус', 'strazha' ) . '</strong></label>';
	echo '<select class="widefat" id="_strazha_status" name="_strazha_status">';
	foreach ( strazha_statuses() as $value => $label ) {
		printf(
			'<option value="%1$s"%2$s>%3$s</option>',
			esc_attr( $value ),
			selected( $current, $value, false ),
			esc_html( $label )
		);
	}
	echo '</select></p>';
	printf(
		'<p><strong>%1$s</strong> %2$s</p>',
		esc_html__( 'Голосов:', 'strazha' ),
		esc_html( number_format_i18n( strazha_vote_count( $post->ID ) ) )
	);
	echo '<p class="description">' . esc_html__( 'Голос можно отдать и отозвать, только пока статус «Идёт голосование». Счётчик ведётся журналом голосов.', 'strazha' ) . '</p>';
	if ( 'auto-draft' !== $post->post_status ) {
		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=strazha_recount&post_id=' . $post->ID ),
			'strazha_recount_' . $post->ID
		);
		printf( '<p><a href="%s">%s</a></p>', esc_url( $url ), esc_html__( 'Пересчитать голоса', 'strazha' ) );
	}
}

add_action( 'save_post_hero', 'strazha_save_hero' );
/**
 * Сохранить карточку памяти.
 *
 * @param int $post_id Запись.
 */
function strazha_save_hero( $post_id ) {
	if ( ! strazha_meta_request_ok( $post_id ) ) {
		return;
	}
	foreach ( array( '_strazha_years', '_strazha_role', '_strazha_place' ) as $key ) {
		$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}

add_action( 'save_post_initiative', 'strazha_save_initiative' );
/**
 * Сохранить статус и завести нулевые мета.
 *
 * @param int $post_id Запись.
 */
function strazha_save_initiative( $post_id ) {
	if ( strazha_meta_request_ok( $post_id ) && isset( $_POST['_strazha_status'] ) ) {
		update_post_meta( $post_id, '_strazha_status', strazha_sanitize_status( wp_unslash( $_POST['_strazha_status'] ) ) );
	} elseif ( '' === get_post_meta( $post_id, '_strazha_status', true ) ) {
		update_post_meta( $post_id, '_strazha_status', 'collecting' );
	}

	if ( '' === get_post_meta( $post_id, '_strazha_votes', true ) ) {
		update_post_meta( $post_id, '_strazha_votes', 0 );
	}
}

/**
 * Общая проверка сохранения метабокса.
 *
 * @param int $post_id Запись.
 */
function strazha_meta_request_ok( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return false;
	}
	if ( ! isset( $_POST['strazha_meta_nonce'] ) ) {
		return false;
	}
	$nonce = sanitize_text_field( wp_unslash( $_POST['strazha_meta_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'strazha_meta_' . $post_id ) ) {
		return false;
	}
	return current_user_can( 'edit_post', $post_id );
}

add_filter( 'manage_initiative_posts_columns', 'strazha_initiative_columns' );
/**
 * Колонки списка инициатив.
 *
 * @param array<string, string> $columns Колонки.
 */
function strazha_initiative_columns( $columns ) {
	$columns['strazha_status'] = __( 'Статус', 'strazha' );
	$columns['strazha_votes']  = __( 'Голоса', 'strazha' );
	return $columns;
}

add_action( 'manage_initiative_posts_custom_column', 'strazha_initiative_column', 10, 2 );
/**
 * Значения колонок.
 *
 * @param string $column  Колонка.
 * @param int    $post_id Запись.
 */
function strazha_initiative_column( $column, $post_id ) {
	if ( 'strazha_status' === $column ) {
		$statuses = strazha_statuses();
		$status   = strazha_initiative_status( $post_id );
		echo esc_html( $statuses[ $status ] );
	}
	if ( 'strazha_votes' === $column ) {
		echo esc_html( number_format_i18n( strazha_vote_count( $post_id ) ) );
	}
}

add_filter( 'manage_edit-initiative_sortable_columns', 'strazha_initiative_sortable' );
/**
 * Сортировка по голосам в админке.
 *
 * @param array<string, string> $columns Колонки.
 */
function strazha_initiative_sortable( $columns ) {
	$columns['strazha_votes'] = 'strazha_votes';
	return $columns;
}

add_action( 'pre_get_posts', 'strazha_admin_vote_sort' );
/**
 * Сортировка колонки голосов.
 *
 * @param WP_Query $query Запрос.
 */
function strazha_admin_vote_sort( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'strazha_votes' === $query->get( 'orderby' ) ) {
		$query->set( 'meta_key', '_strazha_votes' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
