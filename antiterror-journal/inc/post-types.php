<?php
/**
 * Heroes of memory and the initiative fund.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'rubezh_register_content_types' );

/**
 * Post types and the initiative taxonomy.
 */
function rubezh_register_content_types() {
	register_post_type(
		'rubezh_hero',
		array(
			'labels'       => array(
				'name'               => __( 'Память', 'rubezh' ),
				'singular_name'      => __( 'Герой памяти', 'rubezh' ),
				'add_new_item'       => __( 'Добавить профиль', 'rubezh' ),
				'edit_item'          => __( 'Править профиль', 'rubezh' ),
				'new_item'           => __( 'Новый профиль', 'rubezh' ),
				'view_item'          => __( 'Смотреть профиль', 'rubezh' ),
				'search_items'       => __( 'Искать в памяти', 'rubezh' ),
				'not_found'          => __( 'Профилей пока нет', 'rubezh' ),
				'all_items'          => __( 'Все профили', 'rubezh' ),
				'menu_name'          => __( 'Память', 'rubezh' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-awards',
			'menu_position'=> 21,
			'has_archive'  => 'pamyat',
			'rewrite'      => array(
				'slug'       => 'pamyat',
				'with_front' => true,
			),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ),
		)
	);

	register_post_type(
		'rubezh_initiative',
		array(
			'labels'       => array(
				'name'          => __( 'Инициативы', 'rubezh' ),
				'singular_name' => __( 'Инициатива', 'rubezh' ),
				'add_new_item'  => __( 'Добавить инициативу', 'rubezh' ),
				'edit_item'     => __( 'Править инициативу', 'rubezh' ),
				'view_item'     => __( 'Смотреть инициативу', 'rubezh' ),
				'search_items'  => __( 'Искать инициативы', 'rubezh' ),
				'not_found'     => __( 'Инициатив пока нет', 'rubezh' ),
				'all_items'     => __( 'Все инициативы', 'rubezh' ),
				'menu_name'     => __( 'Инициативы', 'rubezh' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-megaphone',
			'menu_position'=> 22,
			'has_archive'  => 'iniciativy',
			'rewrite'      => array(
				'slug'       => 'iniciativy',
				'with_front' => true,
			),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ),
		)
	);

	register_taxonomy(
		'rubezh_track',
		'rubezh_initiative',
		array(
			'labels'            => array(
				'name'          => __( 'Направления', 'rubezh' ),
				'singular_name' => __( 'Направление', 'rubezh' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'napravlenie' ),
		)
	);
}

add_filter( 'manage_rubezh_initiative_posts_columns', 'rubezh_initiative_columns' );

/**
 * Votes column.
 *
 * @param array<string, string> $columns Columns.
 * @return array<string, string>
 */
function rubezh_initiative_columns( $columns ) {
	$columns['rubezh_votes']  = __( 'Голоса', 'rubezh' );
	$columns['rubezh_status'] = __( 'Статус', 'rubezh' );
	return $columns;
}

add_action( 'manage_rubezh_initiative_posts_custom_column', 'rubezh_initiative_column', 10, 2 );

/**
 * Votes column value.
 *
 * @param string $column Column key.
 * @param int    $post_id Post ID.
 */
function rubezh_initiative_column( $column, $post_id ) {
	if ( 'rubezh_votes' === $column ) {
		echo esc_html( (string) (int) get_post_meta( $post_id, '_rubezh_votes', true ) );
	}
	if ( 'rubezh_status' === $column ) {
		echo esc_html( rubezh_status_label( rubezh_initiative_status( $post_id ) ) );
	}
}

add_filter( 'manage_edit-rubezh_initiative_sortable_columns', 'rubezh_initiative_sortable' );

/**
 * Make the vote column sortable.
 *
 * @param array<string, string> $columns Columns.
 * @return array<string, string>
 */
function rubezh_initiative_sortable( $columns ) {
	$columns['rubezh_votes'] = 'rubezh_votes';
	return $columns;
}

add_action( 'pre_get_posts', 'rubezh_sort_by_votes' );

/**
 * Admin sort by vote count.
 *
 * @param WP_Query $query Query.
 */
function rubezh_sort_by_votes( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'rubezh_votes' === $query->get( 'orderby' ) ) {
		$query->set( 'meta_key', '_rubezh_votes' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}

add_filter( 'dashboard_glance_items', 'rubezh_glance' );

/**
 * At a glance counts.
 *
 * @param string[] $items Items.
 * @return string[]
 */
function rubezh_glance( $items ) {
	$map = array(
		'rubezh_hero'       => __( 'Память', 'rubezh' ),
		'rubezh_initiative' => __( 'Инициативы', 'rubezh' ),
	);
	foreach ( $map as $type => $label ) {
		$count    = rubezh_published_count( $type );
		$items[]  = '<a href="' . esc_url( admin_url( 'edit.php?post_type=' . $type ) ) . '">' . esc_html( $count . ' — ' . $label ) . '</a>';
	}
	return $items;
}
