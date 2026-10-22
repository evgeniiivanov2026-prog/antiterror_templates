<?php
/**
 * Theme supports, menus, image sizes.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'atj_setup' );
function atj_setup() {
	load_theme_textdomain( 'antiterror-journal', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'appearance-tools' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 80,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_editor_style( 'assets/css/editor.css' );

	register_nav_menus(
		array(
			'primary'  => __( 'Основное меню', 'antiterror-journal' ),
			'footer'   => __( 'Подвал', 'antiterror-journal' ),
			'memorial' => __( 'Память', 'antiterror-journal' ),
		)
	);

	add_image_size( 'atj-featured', 1440, 900, true );
	add_image_size( 'atj-card', 860, 560, true );
	add_image_size( 'atj-portrait', 720, 900, true );
	add_image_size( 'atj-thumb', 420, 280, true );

	$GLOBALS['content_width'] = 720;
}

add_filter( 'body_class', 'atj_body_class' );
function atj_body_class( $classes ) {
	$classes[] = 'atj-site';
	if ( is_front_page() ) {
		$classes[] = 'atj-is-home';
	}
	return $classes;
}

add_action( 'wp_head', 'atj_meta_head', 2 );
function atj_meta_head() {
	echo '<meta name="theme-color" content="#121c2e">' . "\n";
	echo '<meta name="color-scheme" content="light dark">' . "\n";
}

add_action( 'after_switch_theme', 'atj_flush_rewrites' );
function atj_flush_rewrites() {
	atj_register_content_types();
	flush_rewrite_rules();
}

add_action( 'pre_get_posts', 'atj_public_archive_query' );
function atj_public_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'atj_initiative' ) ) {
		$query->set( 'meta_key', '_atj_votes' );
		$query->set(
			'orderby',
			array(
				'meta_value_num' => 'DESC',
				'date'           => 'DESC',
			)
		);
	}
}

add_action( 'save_post_atj_initiative', 'atj_init_votes', 5 );
function atj_init_votes( $post_id ) {
	if ( '' === get_post_meta( $post_id, '_atj_votes', true ) ) {
		update_post_meta( $post_id, '_atj_votes', 0 );
	}
}
