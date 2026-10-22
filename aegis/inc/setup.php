<?php
/**
 * Theme supports, menus, assets.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'aegis_setup' );
function aegis_setup() {
	load_theme_textdomain( 'aegis', AEGIS_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 280,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'appearance-tools' );
	add_theme_support( 'border' );
	add_theme_support( 'link-color' );
	add_theme_support( 'custom-line-height' );
	add_theme_support( 'custom-spacing' );
	add_theme_support( 'custom-units' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_editor_style( 'assets/css/main.css' );

	register_nav_menus( array(
		'primary' => __( 'Основное меню', 'aegis' ),
		'footer'  => __( 'Меню в подвале', 'aegis' ),
	) );

	set_post_thumbnail_size( 1280, 720, true );
	add_image_size( 'aegis-featured', 1600, 1000, true );
	add_image_size( 'aegis-card', 800, 560, true );
	add_image_size( 'aegis-portrait', 720, 960, true );
}

add_action( 'widgets_init', 'aegis_widgets' );
function aegis_widgets() {
	register_sidebar( array(
		'name'          => __( 'Боковая колонка', 'aegis' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget__title">',
		'after_title'   => '</h2>',
	) );
	register_sidebar( array(
		'name'          => __( 'Подвал', 'aegis' ),
		'id'            => 'footer-1',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget__title">',
		'after_title'   => '</h2>',
	) );
}

add_action( 'wp_enqueue_scripts', 'aegis_assets' );
function aegis_assets() {
	wp_enqueue_style(
		'aegis-fonts',
		'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Manrope:wght@400;500;600;700;800&family=PT+Serif:ital,wght@0,400;0,700;1,400&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'aegis-style', get_stylesheet_uri(), array( 'aegis-fonts' ), AEGIS_VERSION );
	wp_enqueue_style( 'aegis-main', AEGIS_URI . '/assets/css/main.css', array( 'aegis-style' ), AEGIS_VERSION );

	wp_enqueue_script( 'aegis-site', AEGIS_URI . '/assets/js/site.js', array(), AEGIS_VERSION, true );
	wp_localize_script(
		'aegis-site',
		'aegisTheme',
		array(
			'rest'      => esc_url_raw( rest_url( 'aegis/v1/' ) ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'voteNonce' => wp_create_nonce( 'aegis_vote' ),
			'propose'   => wp_create_nonce( 'aegis_propose' ),
			'i18n'      => array(
				'dark'      => __( 'Включить тёмную тему', 'aegis' ),
				'light'     => __( 'Включить светлую тему', 'aegis' ),
				'openMenu'  => __( 'Открыть меню', 'aegis' ),
				'closeMenu' => __( 'Закрыть меню', 'aegis' ),
				'voted'     => __( 'Ваш голос учтён', 'aegis' ),
				'voteError' => __( 'Голос не принят. Попробуйте позже.', 'aegis' ),
				'already'   => __( 'Вы уже поддержали эту инициативу', 'aegis' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

add_filter( 'body_class', 'aegis_body_class' );
function aegis_body_class( $classes ) {
	$classes[] = 'aegis-site';
	if ( is_front_page() ) {
		$classes[] = 'aegis-home';
	}
	return $classes;
}

add_action( 'wp_head', 'aegis_meta', 2 );
function aegis_meta() {
	echo '<meta name="theme-color" content="#16233a">' . "\n";
	echo '<meta name="color-scheme" content="light dark">' . "\n";
}

add_filter( 'excerpt_length', 'aegis_excerpt_length' );
function aegis_excerpt_length( $length ) {
	return 22;
}

add_filter( 'excerpt_more', 'aegis_excerpt_more' );
function aegis_excerpt_more() {
	return '…';
}

add_filter( 'document_title_separator', 'aegis_title_sep' );
function aegis_title_sep() {
	return '—';
}

add_action( 'pre_get_posts', 'aegis_search_types' );
function aegis_search_types( WP_Query $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}
	$query->set( 'post_type', array( 'post', 'page', 'aegis_hero', 'aegis_initiative' ) );
}

add_action( 'after_setup_theme', 'aegis_content_width', 0 );
function aegis_content_width() {
	$GLOBALS['content_width'] = 720;
}

add_action( 'after_switch_theme', 'aegis_flush_rewrites' );
function aegis_flush_rewrites() {
	aegis_register_types();
	flush_rewrite_rules();
}
