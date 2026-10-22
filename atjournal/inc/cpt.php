<?php
/**
 * Heroes and initiatives.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'atj_register_content_types', 5 );
/**
 * Register the memorial and the initiative ledger.
 */
function atj_register_content_types() {
	register_post_type(
		'atj_hero',
		array(
			'labels'              => array(
				'name'               => __( 'Память', 'atjournal' ),
				'singular_name'      => __( 'Карточка памяти', 'atjournal' ),
				'add_new'            => __( 'Добавить карточку', 'atjournal' ),
				'add_new_item'       => __( 'Новая карточка памяти', 'atjournal' ),
				'edit_item'          => __( 'Редактировать карточку', 'atjournal' ),
				'new_item'           => __( 'Новая карточка', 'atjournal' ),
				'view_item'          => __( 'Смотреть карточку', 'atjournal' ),
				'search_items'       => __( 'Искать в памяти', 'atjournal' ),
				'not_found'          => __( 'Карточек пока нет', 'atjournal' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'atjournal' ),
				'all_items'          => __( 'Все карточки', 'atjournal' ),
				'archives'           => __( 'Память', 'atjournal' ),
				'menu_name'          => __( 'Память', 'atjournal' ),
			),
			'public'              => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-book-alt',
			'menu_position'       => 21,
			'has_archive'         => 'pamyat',
			'rewrite'             => array(
				'slug'       => 'pamyat',
				'with_front' => false,
			),
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ),
			'exclude_from_search' => false,
		)
	);

	register_post_type(
		'atj_initiative',
		array(
			'labels'              => array(
				'name'               => __( 'Инициативы', 'atjournal' ),
				'singular_name'      => __( 'Инициатива', 'atjournal' ),
				'add_new'            => __( 'Добавить инициативу', 'atjournal' ),
				'add_new_item'       => __( 'Новая инициатива', 'atjournal' ),
				'edit_item'          => __( 'Редактировать инициативу', 'atjournal' ),
				'view_item'          => __( 'Смотреть инициативу', 'atjournal' ),
				'search_items'       => __( 'Искать инициативы', 'atjournal' ),
				'not_found'          => __( 'Инициатив пока нет', 'atjournal' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'atjournal' ),
				'all_items'          => __( 'Все инициативы', 'atjournal' ),
				'archives'           => __( 'Фонд инициатив', 'atjournal' ),
				'menu_name'          => __( 'Инициативы', 'atjournal' ),
			),
			'public'              => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-clipboard',
			'menu_position'       => 22,
			'has_archive'         => 'iniciativy',
			'rewrite'             => array(
				'slug'       => 'iniciativy',
				'with_front' => false,
			),
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'revisions' ),
			'exclude_from_search' => false,
		)
	);

	register_taxonomy(
		'atj_area',
		'atj_initiative',
		array(
			'labels'            => array(
				'name'          => __( 'Направления', 'atjournal' ),
				'singular_name' => __( 'Направление', 'atjournal' ),
				'search_items'  => __( 'Искать направления', 'atjournal' ),
				'all_items'     => __( 'Все направления', 'atjournal' ),
				'edit_item'     => __( 'Редактировать направление', 'atjournal' ),
				'add_new_item'  => __( 'Новое направление', 'atjournal' ),
				'menu_name'     => __( 'Направления', 'atjournal' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => 'napravlenie',
				'with_front' => false,
			),
		)
	);
}
