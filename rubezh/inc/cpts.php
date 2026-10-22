<?php
/**
 * Память и фонд инициатив.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'rubezh_register_content_types' );
/**
 * Типы записей.
 */
function rubezh_register_content_types() {
	register_post_type(
		'hero',
		array(
			'labels'       => array(
				'name'               => __( 'Память', 'rubezh' ),
				'singular_name'      => __( 'Карточка памяти', 'rubezh' ),
				'add_new_item'       => __( 'Добавить карточку памяти', 'rubezh' ),
				'edit_item'          => __( 'Редактировать карточку', 'rubezh' ),
				'new_item'           => __( 'Новая карточка', 'rubezh' ),
				'view_item'          => __( 'Смотреть карточку', 'rubezh' ),
				'search_items'       => __( 'Искать в памяти', 'rubezh' ),
				'not_found'          => __( 'Карточек пока нет', 'rubezh' ),
				'all_items'          => __( 'Все карточки', 'rubezh' ),
				'menu_name'          => __( 'Память', 'rubezh' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-id',
			'menu_position'=> 6,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'pamyat' ),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author', 'page-attributes' ),
		)
	);

	register_post_type(
		'initiative',
		array(
			'labels'       => array(
				'name'               => __( 'Инициативы', 'rubezh' ),
				'singular_name'      => __( 'Инициатива', 'rubezh' ),
				'add_new_item'       => __( 'Добавить инициативу', 'rubezh' ),
				'edit_item'          => __( 'Редактировать инициативу', 'rubezh' ),
				'new_item'           => __( 'Новая инициатива', 'rubezh' ),
				'view_item'          => __( 'Смотреть инициативу', 'rubezh' ),
				'search_items'       => __( 'Искать инициативы', 'rubezh' ),
				'not_found'          => __( 'Инициатив пока нет', 'rubezh' ),
				'all_items'          => __( 'Все инициативы', 'rubezh' ),
				'menu_name'          => __( 'Инициативы', 'rubezh' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-megaphone',
			'menu_position'=> 7,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'iniciativy' ),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author', 'page-attributes' ),
		)
	);
}

add_action( 'pre_get_posts', 'rubezh_archive_query' );
/**
 * Поиск и архивы типов записей.
 *
 * @param WP_Query $query Запрос.
 */
function rubezh_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_search() ) {
		$query->set( 'post_type', array( 'post', 'hero', 'initiative' ) );
	}

	if ( $query->is_post_type_archive( 'hero' ) || $query->is_post_type_archive( 'initiative' ) ) {
		$query->set( 'posts_per_page', 12 );
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
	}
}
