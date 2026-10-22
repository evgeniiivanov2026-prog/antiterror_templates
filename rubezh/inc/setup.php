<?php
/**
 * Регистрация возможностей темы.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'rubezh_setup' );
function rubezh_setup() {
	load_theme_textdomain( 'rubezh', RUBEZH_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
		'navigation-widgets',
	) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'custom-background', array(
		'default-color' => 'f3efe6',
	) );

	add_image_size( 'rubezh-cover', 1600, 1000, true );
	add_image_size( 'rubezh-feature', 1100, 720, true );
	add_image_size( 'rubezh-card', 720, 480, true );
	add_image_size( 'rubezh-portrait', 640, 800, true );
	add_image_size( 'rubezh-thumb', 360, 240, true );

	register_nav_menus( array(
		'primary' => __( 'Основное меню', 'rubezh' ),
		'footer'  => __( 'Меню в подвале', 'rubezh' ),
	) );

	add_editor_style( 'assets/css/main.css' );
}

add_action( 'after_setup_theme', 'rubezh_content_width', 0 );
function rubezh_content_width() {
	$GLOBALS['content_width'] = 760;
}

add_action( 'widgets_init', 'rubezh_widgets' );
function rubezh_widgets() {
	register_sidebar( array(
		'name'          => __( 'Боковая колонка', 'rubezh' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Аналитика, рубрики, связанные материалы.', 'rubezh' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget__title">',
		'after_title'   => '</h2>',
	) );
}

add_filter( 'body_class', 'rubezh_body_class' );
function rubezh_body_class( $classes ) {
	$classes[] = 'rubezh-site';
	if ( is_front_page() ) {
		$classes[] = 'is-cover-home';
	}
	return $classes;
}

add_action( 'wp_head', 'rubezh_meta_head', 2 );
function rubezh_meta_head() {
	echo '<meta name="theme-color" content="#152033">' . "\n";
	echo '<meta name="color-scheme" content="light dark">' . "\n";
}

add_filter( 'excerpt_length', 'rubezh_excerpt_length' );
function rubezh_excerpt_length( $length ) {
	return 22;
}

add_filter( 'excerpt_more', 'rubezh_excerpt_more' );
function rubezh_excerpt_more() {
	return '…';
}

add_action( 'after_switch_theme', 'rubezh_flush_rewrites' );
function rubezh_flush_rewrites() {
	rubezh_register_cpts();
	flush_rewrite_rules();
}
