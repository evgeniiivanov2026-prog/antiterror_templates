<?php
/**
 * Герои и инициативы.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'strazha_register_content_types' );
/**
 * Типы записей и таксономии.
 */
function strazha_register_content_types() {
	register_post_type(
		'hero',
		array(
			'labels'       => array(
				'name'               => __( 'Герои', 'strazha' ),
				'singular_name'      => __( 'Герой', 'strazha' ),
				'add_new'            => __( 'Добавить', 'strazha' ),
				'add_new_item'       => __( 'Новое имя', 'strazha' ),
				'edit_item'          => __( 'Править запись', 'strazha' ),
				'new_item'           => __( 'Новое имя', 'strazha' ),
				'view_item'          => __( 'Смотреть', 'strazha' ),
				'search_items'       => __( 'Искать в памяти', 'strazha' ),
				'not_found'          => __( 'Имён пока нет', 'strazha' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'strazha' ),
				'all_items'          => __( 'Все имена', 'strazha' ),
				'menu_name'          => __( 'Память', 'strazha' ),
				'archives'           => __( 'Память героев', 'strazha' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-awards',
			'menu_position'=> 21,
			'has_archive'  => 'pamyat',
			'rewrite'      => array( 'slug' => 'pamyat', 'with_front' => false ),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ),
		)
	);

	register_taxonomy(
		'hero_unit',
		'hero',
		array(
			'labels'            => array(
				'name'          => __( 'Службы', 'strazha' ),
				'singular_name' => __( 'Служба', 'strazha' ),
				'search_items'  => __( 'Искать службы', 'strazha' ),
				'all_items'     => __( 'Все службы', 'strazha' ),
				'edit_item'     => __( 'Править службу', 'strazha' ),
				'add_new_item'  => __( 'Новая служба', 'strazha' ),
				'menu_name'     => __( 'Службы', 'strazha' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'rewrite'           => array( 'slug' => 'sluzhba', 'with_front' => false ),
			'show_admin_column' => true,
		)
	);

	register_post_type(
		'initiative',
		array(
			'labels'       => array(
				'name'               => __( 'Инициативы', 'strazha' ),
				'singular_name'      => __( 'Инициатива', 'strazha' ),
				'add_new'            => __( 'Добавить', 'strazha' ),
				'add_new_item'       => __( 'Новая инициатива', 'strazha' ),
				'edit_item'          => __( 'Править инициативу', 'strazha' ),
				'new_item'           => __( 'Новая инициатива', 'strazha' ),
				'view_item'          => __( 'Смотреть', 'strazha' ),
				'search_items'       => __( 'Искать инициативы', 'strazha' ),
				'not_found'          => __( 'Инициатив пока нет', 'strazha' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'strazha' ),
				'all_items'          => __( 'Все инициативы', 'strazha' ),
				'menu_name'          => __( 'Фонд инициатив', 'strazha' ),
				'archives'           => __( 'Фонд инициатив', 'strazha' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-lightbulb',
			'menu_position'=> 22,
			'has_archive'  => 'iniciativy',
			'rewrite'      => array( 'slug' => 'iniciativy', 'with_front' => false ),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		)
	);

	register_taxonomy(
		'initiative_field',
		'initiative',
		array(
			'labels'            => array(
				'name'          => __( 'Направления', 'strazha' ),
				'singular_name' => __( 'Направление', 'strazha' ),
				'search_items'  => __( 'Искать направления', 'strazha' ),
				'all_items'     => __( 'Все направления', 'strazha' ),
				'edit_item'     => __( 'Править направление', 'strazha' ),
				'add_new_item'  => __( 'Новое направление', 'strazha' ),
				'menu_name'     => __( 'Направления', 'strazha' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
			'rewrite'           => array( 'slug' => 'napravlenie', 'with_front' => false ),
			'show_admin_column' => true,
		)
	);
}

add_filter( 'enter_title_here', 'strazha_title_placeholder', 10, 2 );
/**
 * Подсказка в поле названия.
 *
 * @param string  $text Текст.
 * @param WP_Post $post Запись.
 */
function strazha_title_placeholder( $text, $post ) {
	if ( 'hero' === $post->post_type ) {
		return __( 'Имя и фамилия', 'strazha' );
	}
	if ( 'initiative' === $post->post_type ) {
		return __( 'Название инициативы', 'strazha' );
	}
	return $text;
}

add_action( 'pre_get_posts', 'strazha_archive_query' );
/**
 * Порядок стены памяти и фонда.
 *
 * @param WP_Query $query Запрос.
 */
function strazha_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'hero' ) || $query->is_tax( 'hero_unit' ) ) {
		$query->set( 'posts_per_page', (int) apply_filters( 'strazha_heroes_per_page', 12 ) );
		$query->set(
			'orderby',
			array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			)
		);
	}

	if ( $query->is_post_type_archive( 'initiative' ) || $query->is_tax( 'initiative_field' ) ) {
		$status     = sanitize_key( (string) $query->get( 'istatus' ) );
		$meta_query = array(
			'votes_clause' => array(
				'key'     => '_strazha_votes',
				'type'    => 'NUMERIC',
				'compare' => 'EXISTS',
			),
		);
		if ( isset( strazha_statuses()[ $status ] ) ) {
			$meta_query['status_clause'] = array(
				'key'   => '_strazha_status',
				'value' => $status,
			);
		}
		$query->set( 'posts_per_page', (int) apply_filters( 'strazha_initiatives_per_page', 9 ) );
		$query->set( 'meta_query', $meta_query );
		$query->set(
			'orderby',
			array(
				'votes_clause' => 'DESC',
				'date'         => 'DESC',
			)
		);
	}
}

add_filter( 'query_vars', 'strazha_query_vars' );
/**
 * Фильтр статуса инициатив в адресе.
 *
 * @param string[] $vars Переменные.
 */
function strazha_query_vars( $vars ) {
	$vars[] = 'istatus';
	return $vars;
}

add_action( 'after_switch_theme', 'strazha_after_switch' );
/**
 * Правила адресов и таблица голосов при включении темы.
 */
function strazha_after_switch() {
	strazha_register_content_types();
	if ( function_exists( 'strazha_install_table' ) ) {
		strazha_install_table();
	}
	flush_rewrite_rules();
}

add_action( 'switch_theme', 'strazha_switch_away' );
/**
 * Сбросить правила, когда тему выключают. Таблицу голосов оставляем.
 */
function strazha_switch_away() {
	flush_rewrite_rules();
}
