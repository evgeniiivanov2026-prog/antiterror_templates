<?php
/**
 * Theme supports, assets and document chrome.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'rubezh_setup' );

/**
 * Register theme supports.
 */
function rubezh_setup() {
	load_theme_textdomain( 'rubezh', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
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

	add_image_size( 'rubezh-lead', 1600, 1000, true );
	add_image_size( 'rubezh-card', 960, 640, true );

	register_nav_menus(
		array(
			'primary' => __( 'Основное меню', 'rubezh' ),
			'footer'  => __( 'Меню в подвале', 'rubezh' ),
		)
	);

	add_editor_style( 'style.css' );
	add_editor_style( 'assets/css/editor.css' );
}

add_action( 'wp_enqueue_scripts', 'rubezh_assets' );

/**
 * Front-end assets.
 */
function rubezh_assets() {
	wp_enqueue_style(
		'rubezh-fonts',
		'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'rubezh-style', get_stylesheet_uri(), array( 'rubezh-fonts' ), RUBEZH_VERSION );
	wp_enqueue_script(
		'rubezh-theme',
		get_template_directory_uri() . '/assets/js/theme.js',
		array(),
		RUBEZH_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
	wp_localize_script(
		'rubezh-theme',
		'rubezhData',
		array(
			'rest'  => esc_url_raw( rest_url( 'rubezh/v1/vote' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'voted' => __( 'Голос учтён', 'rubezh' ),
			'error' => __( 'Не удалось отправить голос. Обновите страницу и попробуйте ещё раз.', 'rubezh' ),
			'wait'  => __( 'Отправляем…', 'rubezh' ),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

add_action( 'enqueue_block_editor_assets', 'rubezh_editor_assets' );

/**
 * Keep the site editor preview in the same visual system.
 */
function rubezh_editor_assets() {
	wp_enqueue_style(
		'rubezh-fonts',
		'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'rubezh-style', get_stylesheet_uri(), array( 'rubezh-fonts' ), RUBEZH_VERSION );
}

add_action( 'wp_head', 'rubezh_head', 0 );

/**
 * Early theme choice, so the first paint matches the saved preference.
 */
function rubezh_head() {
	echo "<script>try{var k='rubezh-theme',s=localStorage.getItem(k),d=s||(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',d);}catch(e){}</script>\n";
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	echo '<meta name="theme-color" content="#101826">' . "\n";
}

add_filter( 'body_class', 'rubezh_body_class' );

/**
 * Body classes.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function rubezh_body_class( $classes ) {
	$classes[] = 'rubezh-site';
	if ( is_front_page() ) {
		$classes[] = 'rz-is-front';
	}
	return $classes;
}

add_filter( 'excerpt_more', 'rubezh_excerpt_more' );

/**
 * Quiet excerpt ending.
 *
 * @return string
 */
function rubezh_excerpt_more() {
	return '…';
}

add_filter( 'block_categories_all', 'rubezh_block_category', 10, 2 );

/**
 * Inserter group for the theme blocks.
 *
 * @param array[] $categories Categories.
 * @return array[]
 */
function rubezh_block_category( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'rubezh',
			'title' => __( 'Рубеж', 'rubezh' ),
			'icon'  => null,
		)
	);
	return $categories;
}
