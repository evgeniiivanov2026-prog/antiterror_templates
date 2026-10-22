<?php
/**
 * Типы записей: герои памяти и инициативы фонда.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'rubezh_register_cpts' );
function rubezh_register_cpts() {
	register_post_type(
		'hero',
		array(
			'labels'       => array(
				'name'          => __( 'Герои памяти', 'rubezh' ),
				'singular_name' => __( 'Герой', 'rubezh' ),
				'add_new_item'  => __( 'Добавить карточку героя', 'rubezh' ),
				'edit_item'     => __( 'Редактировать карточку', 'rubezh' ),
				'search_items'  => __( 'Искать в памяти', 'rubezh' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'pamyat' ),
			'menu_icon'    => 'dashicons-awards',
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		)
	);

	register_taxonomy(
		'hero_service',
		'hero',
		array(
			'labels'       => array(
				'name'          => __( 'Службы и статусы', 'rubezh' ),
				'singular_name' => __( 'Служба', 'rubezh' ),
			),
			'public'       => true,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'sluzhba' ),
		)
	);

	register_post_type(
		'initiative',
		array(
			'labels'       => array(
				'name'          => __( 'Инициативы', 'rubezh' ),
				'singular_name' => __( 'Инициатива', 'rubezh' ),
				'add_new_item'  => __( 'Добавить инициативу', 'rubezh' ),
				'edit_item'     => __( 'Редактировать инициативу', 'rubezh' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'iniciativy' ),
			'menu_icon'    => 'dashicons-megaphone',
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		)
	);

	register_taxonomy(
		'initiative_topic',
		'initiative',
		array(
			'labels'       => array(
				'name'          => __( 'Темы инициатив', 'rubezh' ),
				'singular_name' => __( 'Тема', 'rubezh' ),
			),
			'public'       => true,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'tema' ),
		)
	);
}

add_filter( 'pre_get_posts', 'rubezh_initiative_archive_order' );
function rubezh_initiative_archive_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'initiative' ) ) {
		$query->set( 'meta_key', '_rubezh_votes' );
		$query->set( 'orderby', array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ) );
	}
	if ( $query->is_post_type_archive( 'hero' ) ) {
		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'ASC' );
		$query->set( 'posts_per_page', 18 );
	}
}
