<?php
/**
 * Custom post types: heroes of anti-terror and civic initiatives.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'vigilum_register_types' );
function vigilum_register_types() {
	register_post_type( 'vigilum_hero', array(
		'labels' => array(
			'name'               => __( 'Герои', 'vigilum' ),
			'singular_name'      => __( 'Герой', 'vigilum' ),
			'add_new_item'       => __( 'Добавить карточку героя', 'vigilum' ),
			'edit_item'          => __( 'Редактировать карточку', 'vigilum' ),
			'new_item'           => __( 'Новая карточка', 'vigilum' ),
			'view_item'          => __( 'Открыть карточку', 'vigilum' ),
			'search_items'       => __( 'Искать героев', 'vigilum' ),
			'not_found'          => __( 'Карточек пока нет', 'vigilum' ),
			'all_items'          => __( 'Все герои', 'vigilum' ),
			'menu_name'          => __( 'Память героев', 'vigilum' ),
		),
		'public'             => true,
		'has_archive'        => true,
		'rewrite'            => array( 'slug' => 'geroi', 'with_front' => false ),
		'show_in_rest'       => true,
		'menu_icon'          => 'dashicons-awards',
		'menu_position'      => 21,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields' ),
		'template'           => array(
			array( 'core/paragraph', array(
				'placeholder' => __( 'Короткий текст памяти: факты службы, поступок, дата. Без сенсаций.', 'vigilum' ),
			) ),
		),
	) );

	register_post_type( 'vigilum_initiative', array(
		'labels' => array(
			'name'               => __( 'Инициативы', 'vigilum' ),
			'singular_name'      => __( 'Инициатива', 'vigilum' ),
			'add_new_item'       => __( 'Добавить инициативу', 'vigilum' ),
			'edit_item'          => __( 'Редактировать инициативу', 'vigilum' ),
			'new_item'           => __( 'Новая инициатива', 'vigilum' ),
			'view_item'          => __( 'Открыть инициативу', 'vigilum' ),
			'search_items'       => __( 'Искать инициативы', 'vigilum' ),
			'not_found'          => __( 'Инициатив пока нет', 'vigilum' ),
			'all_items'          => __( 'Все инициативы', 'vigilum' ),
			'menu_name'          => __( 'Фонд инициатив', 'vigilum' ),
		),
		'public'             => true,
		'has_archive'        => true,
		'rewrite'            => array( 'slug' => 'iniciativy', 'with_front' => false ),
		'show_in_rest'       => true,
		'menu_icon'          => 'dashicons-megaphone',
		'menu_position'      => 22,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields', 'comments' ),
		'template'           => array(
			array( 'core/paragraph', array(
				'placeholder' => __( 'Суть инициативы: проблема, предлагаемое действие, ожидаемый эффект.', 'vigilum' ),
			) ),
		),
	) );

	register_taxonomy( 'hero_role', 'vigilum_hero', array(
		'labels'            => array(
			'name'          => __( 'Роли и звания', 'vigilum' ),
			'singular_name' => __( 'Роль', 'vigilum' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'rol' ),
	) );

	register_taxonomy( 'initiative_topic', 'vigilum_initiative', array(
		'labels'            => array(
			'name'          => __( 'Темы инициатив', 'vigilum' ),
			'singular_name' => __( 'Тема', 'vigilum' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'tema' ),
	) );

	register_taxonomy( 'initiative_status', 'vigilum_initiative', array(
		'labels'            => array(
			'name'          => __( 'Статусы', 'vigilum' ),
			'singular_name' => __( 'Статус', 'vigilum' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'status' ),
	) );
}

add_action( 'save_post_vigilum_initiative', 'vigilum_default_votes', 5 );
function vigilum_default_votes( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( '' === get_post_meta( $post_id, '_vigilum_votes', true ) ) {
		update_post_meta( $post_id, '_vigilum_votes', 0 );
	}
}

add_filter( 'register_post_type_args', 'vigilum_archive_titles', 10, 2 );
function vigilum_archive_titles( $args, $post_type ) {
	if ( 'vigilum_hero' === $post_type ) {
		$args['has_archive'] = true;
	}
	return $args;
}
