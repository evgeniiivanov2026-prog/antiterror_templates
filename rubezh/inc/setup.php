<?php
/**
 * Поддержка WordPress, стили и сценарии.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'rubezh_setup' );
/**
 * Возможности темы.
 */
function rubezh_setup() {
	load_theme_textdomain( 'rubezh', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
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
	add_theme_support( 'wp-block-styles' );

	add_image_size( 'rubezh-lead', 1600, 1000, true );
	add_image_size( 'rubezh-card', 900, 640, true );
	add_image_size( 'rubezh-portrait', 800, 1000, true );

	register_nav_menus(
		array(
			'primary' => __( 'Основное меню', 'rubezh' ),
			'footer'  => __( 'Меню в подвале', 'rubezh' ),
		)
	);

	add_editor_style( 'assets/css/fonts.css' );
	add_editor_style( 'assets/css/editor.css' );
}

add_action( 'after_setup_theme', 'rubezh_content_width', 0 );
/**
 * Ширина содержимого для встроенных объектов.
 */
function rubezh_content_width() {
	$GLOBALS['content_width'] = 760;
}

add_action( 'widgets_init', 'rubezh_widgets' );
/**
 * Области виджетов.
 */
function rubezh_widgets() {
	register_sidebar(
		array(
			'name'          => __( 'Колонка летописи', 'rubezh' ),
			'id'            => 'rubezh-column',
			'description'   => __( 'Появляется рядом с материалом. Пока пусто, тема показывает собственную колонку.', 'rubezh' ),
			'before_widget' => '<section id="%1$s" class="rz-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="rz-widget__title">',
			'after_title'   => '</h2>',
		)
	);
}

add_filter( 'body_class', 'rubezh_body_class' );
/**
 * @param string[] $classes Классы.
 * @return string[]
 */
function rubezh_body_class( $classes ) {
	$classes[] = 'rubezh';
	return $classes;
}

add_action( 'wp_head', 'rubezh_theme_boot', 0 );
/**
 * Тема оформления до первой отрисовки.
 */
function rubezh_theme_boot() {
	echo "<script>(function(){try{var t=localStorage.getItem('rubezh-theme');if(!t&&window.matchMedia('(prefers-color-scheme: dark)').matches){t='dark';}if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-theme',t);}}catch(e){}})();</script>\n";
	echo '<meta name="theme-color" content="#f3efe6">' . "\n";
}

add_action( 'wp_enqueue_scripts', 'rubezh_assets' );
/**
 * Стили и сценарий сайта.
 */
function rubezh_assets() {
	$version = RUBEZH_VERSION;
	wp_enqueue_style( 'rubezh-fonts', get_template_directory_uri() . '/assets/css/fonts.css', array(), $version );
	wp_enqueue_style( 'rubezh-main', get_template_directory_uri() . '/assets/css/main.css', array( 'rubezh-fonts' ), $version );
	wp_enqueue_style( 'rubezh-style', get_stylesheet_uri(), array( 'rubezh-main' ), $version );
	wp_add_inline_style( 'rubezh-main', rubezh_color_variables() );

	wp_enqueue_script( 'rubezh-theme', get_template_directory_uri() . '/assets/js/theme.js', array(), $version, true );
	wp_localize_script(
		'rubezh-theme',
		'rubezhData',
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'rubezh_vote' ),
			'postsNonce' => wp_create_nonce( 'rubezh_posts' ),
			'voteLabel'  => __( 'Поддержать', 'rubezh' ),
			'votedLabel' => __( 'Поддержано', 'rubezh' ),
			'error'      => __( 'Не удалось учесть голос. Обновите страницу.', 'rubezh' ),
		)
	);
}

/**
 * Пользовательские цвета светлой темы.
 *
 * @return string
 */
function rubezh_color_variables() {
	$defaults = array(
		'paper'  => '#f3efe6',
		'ink'    => '#1c1915',
		'signal' => '#7c2433',
	);
	$colors = array();
	foreach ( $defaults as $key => $default ) {
		$value           = sanitize_hex_color( (string) get_theme_mod( 'rubezh_color_' . $key, $default ) );
		$colors[ $key ] = $value ? $value : $default;
	}
	$ink_on_signal = rubezh_hex_is_light( $colors['signal'] ) ? '#1c1915' : '#fff8f6';

	return ':root:not([data-theme="dark"]){'
		. '--rz-paper:' . $colors['paper'] . ';'
		. '--rz-ink:' . $colors['ink'] . ';'
		. '--rz-signal:' . $colors['signal'] . ';'
		. '--rz-signal-solid:' . $colors['signal'] . ';'
		. '--rz-signal-ink:' . $ink_on_signal . ';'
		. '}';
}

add_filter( 'get_the_archive_title', 'rubezh_archive_title' );
/**
 * Убирает служебные приставки «Рубрика:» и «Автор:».
 *
 * @param string $title Заголовок.
 * @return string
 */
function rubezh_archive_title( $title ) {
	if ( is_category() ) {
		return single_cat_title( '', false );
	}
	if ( is_tag() ) {
		return single_tag_title( '', false );
	}
	if ( is_author() ) {
		return get_the_author();
	}
	if ( is_post_type_archive() ) {
		return post_type_archive_title( '', false );
	}
	return $title;
}

add_filter( 'excerpt_more', 'rubezh_excerpt_more' );
/**
 * @return string
 */
function rubezh_excerpt_more() {
	return '…';
}

add_action( 'template_redirect', 'rubezh_issue_voter_cookie' );
/**
 * Выдаёт гостевой жетон до голосования. Сам обработчик голоса жетон не создаёт.
 */
function rubezh_issue_voter_cookie() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || is_user_logged_in() ) {
		return;
	}
	if ( rubezh_valid_guest_token() ) {
		return;
	}
	rubezh_set_voter_cookie();
}
