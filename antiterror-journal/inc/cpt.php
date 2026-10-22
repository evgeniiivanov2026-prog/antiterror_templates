<?php
/**
 * Custom post types and taxonomies.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'atj_register_content_types' );
function atj_register_content_types() {
	register_post_type(
		'atj_hero',
		array(
			'labels'              => array(
				'name'          => __( 'Герои', 'antiterror-journal' ),
				'singular_name' => __( 'Карточка памяти', 'antiterror-journal' ),
				'add_new_item'  => __( 'Добавить карточку памяти', 'antiterror-journal' ),
				'edit_item'     => __( 'Редактировать карточку памяти', 'antiterror-journal' ),
				'search_items'  => __( 'Найти карточку памяти', 'antiterror-journal' ),
				'not_found'     => __( 'Карточки памяти ещё не добавлены.', 'antiterror-journal' ),
				'menu_name'     => __( 'Память героев', 'antiterror-journal' ),
			),
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'heroes', 'with_front' => false ),
			'menu_icon'           => 'dashicons-awards',
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields' ),
			'menu_position'       => 6,
		)
	);

	register_post_type(
		'atj_initiative',
		array(
			'labels'              => array(
				'name'          => __( 'Инициативы', 'antiterror-journal' ),
				'singular_name' => __( 'Инициатива', 'antiterror-journal' ),
				'add_new_item'  => __( 'Добавить инициативу', 'antiterror-journal' ),
				'edit_item'     => __( 'Редактировать инициативу', 'antiterror-journal' ),
				'search_items'  => __( 'Найти инициативу', 'antiterror-journal' ),
				'not_found'     => __( 'Инициативы ещё не добавлены.', 'antiterror-journal' ),
				'menu_name'     => __( 'Фонд инициатив', 'antiterror-journal' ),
			),
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'initiatives', 'with_front' => false ),
			'menu_icon'           => 'dashicons-megaphone',
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields', 'comments' ),
			'menu_position'       => 7,
		)
	);

	register_taxonomy(
		'hero_service',
		'atj_hero',
		array(
			'labels'            => array(
				'name'          => __( 'Службы', 'antiterror-journal' ),
				'singular_name' => __( 'Служба', 'antiterror-journal' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'service' ),
		)
	);

	register_taxonomy(
		'initiative_topic',
		'atj_initiative',
		array(
			'labels'            => array(
				'name'          => __( 'Темы инициатив', 'antiterror-journal' ),
				'singular_name' => __( 'Тема', 'antiterror-journal' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'topic' ),
		)
	);
}
