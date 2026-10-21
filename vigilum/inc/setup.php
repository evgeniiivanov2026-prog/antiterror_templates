<?php
/**
 * Theme supports, menus, assets.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'vigilum_setup' );
function vigilum_setup() {
	load_theme_textdomain( 'vigilum', VIGILUM_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 96,
		'width'       => 320,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'appearance-tools' );
	add_editor_style( 'assets/css/editor.css' );

	register_nav_menus( array(
		'primary' => __( 'Основное меню', 'vigilum' ),
		'footer'  => __( 'Меню в подвале', 'vigilum' ),
	) );

	add_image_size( 'vigilum-hero', 1600, 1000, true );
	add_image_size( 'vigilum-card', 860, 640, true );
	add_image_size( 'vigilum-portrait', 720, 900, true );
	add_image_size( 'vigilum-square', 640, 640, true );
}

add_action( 'widgets_init', 'vigilum_widgets' );
function vigilum_widgets() {
	register_sidebar( array(
		'name'          => __( 'Боковая колонка журнала', 'vigilum' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}

add_action( 'wp_enqueue_scripts', 'vigilum_assets' );
function vigilum_assets() {
	wp_enqueue_style(
		'vigilum-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Manrope:wght@400;500;600;700;800&display=swap&subset=cyrillic,cyrillic-ext,latin',
		array(),
		null
	);

	wp_enqueue_style( 'vigilum-style', get_stylesheet_uri(), array( 'vigilum-fonts' ), VIGILUM_VERSION );
	wp_enqueue_style(
		'vigilum-main',
		VIGILUM_URI . '/assets/css/main.css',
		array( 'vigilum-style' ),
		VIGILUM_VERSION
	);

	wp_enqueue_script(
		'vigilum-theme',
		VIGILUM_URI . '/assets/js/theme.js',
		array(),
		VIGILUM_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);

	if ( is_singular( 'vigilum_initiative' ) || is_post_type_archive( 'vigilum_initiative' ) || is_front_page() || is_search() || is_tax( 'initiative_topic' ) || is_tax( 'initiative_status' ) ) {
		wp_enqueue_script(
			'vigilum-vote',
			VIGILUM_URI . '/assets/js/vote.js',
			array(),
			VIGILUM_VERSION,
			array( 'strategy' => 'defer', 'in_footer' => true )
		);

		wp_localize_script( 'vigilum-vote', 'vigilumVote', array(
			'rest'  => esc_url_raw( rest_url( 'vigilum/v1/vote' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'i18n'  => array(
				'done'   => __( 'Голос принят', 'vigilum' ),
				'error'  => __( 'Не удалось проголосовать. Попробуйте позже.', 'vigilum' ),
				'double' => __( 'Вы уже поддержали эту инициативу.', 'vigilum' ),
			),
		) );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

add_filter( 'body_class', 'vigilum_body_class' );
function vigilum_body_class( $classes ) {
	$classes[] = 'vigilum-site';
	if ( is_front_page() ) {
		$classes[] = 'is-front';
	}
	return $classes;
}

add_action( 'wp_head', 'vigilum_meta', 2 );
function vigilum_meta() {
	$theme = get_theme_mod( 'vigilum_default_theme', 'system' );
	echo '<meta name="theme-color" content="#16233a">' . "\n";
	echo '<meta name="color-scheme" content="light dark">' . "\n";
	if ( in_array( $theme, array( 'light', 'dark', 'system' ), true ) ) {
		printf( '<script>document.documentElement.dataset.themePref="%s";</script>' . "\n", esc_js( $theme ) );
	}
}

add_filter( 'excerpt_length', static function () {
	return 22;
} );

add_filter( 'excerpt_more', static function () {
	return '…';
} );

add_action( 'after_switch_theme', 'vigilum_on_activate' );
function vigilum_on_activate() {
	if ( ! get_option( 'vigilum_pages_seeded' ) ) {
		vigilum_seed_pages();
		update_option( 'vigilum_pages_seeded', 1 );
	}
	flush_rewrite_rules();
}

add_action( 'switch_theme', 'vigilum_on_deactivate' );
function vigilum_on_deactivate() {
	flush_rewrite_rules();
}

/**
 * Create structural pages once. Content stays empty until the editor fills it.
 */
function vigilum_seed_pages() {
	$pages = array(
		'journal'     => array(
			'title'   => __( 'Журнал', 'vigilum' ),
			'content' => '',
		),
		'about'       => array(
			'title'   => __( 'О проекте', 'vigilum' ),
			'content' => '<!-- wp:paragraph --><p>' . esc_html__( 'Vigilum — журнальная площадка об антитерроре: факты, память героев и гражданские инициативы. Замените этот текст на миссию вашей редакции или фонда.', 'vigilum' ) . '</p><!-- /wp:paragraph -->',
		),
		'privacy'     => array(
			'title'   => __( 'Конфиденциальность', 'vigilum' ),
			'content' => '<!-- wp:paragraph --><p>' . esc_html__( 'Опишите политику обработки персональных данных до запуска форм обращения.', 'vigilum' ) . '</p><!-- /wp:paragraph -->',
		),
	);

	$ids = array();
	foreach ( $pages as $key => $page ) {
		$existing = get_page_by_path( sanitize_title( $page['title'] ) );
		if ( $existing ) {
			$ids[ $key ] = $existing->ID;
			continue;
		}
		$ids[ $key ] = wp_insert_post( array(
			'post_title'   => $page['title'],
			'post_content' => $page['content'],
			'post_status'  => 'publish',
			'post_type'    => 'page',
		) );
	}

	if ( ! empty( $ids['journal'] ) && ! get_option( 'page_for_posts' ) ) {
		update_option( 'page_for_posts', (int) $ids['journal'] );
		update_option( 'show_on_front', 'page' );
		if ( ! get_option( 'page_on_front' ) ) {
			$front = wp_insert_post( array(
				'post_title'   => __( 'Главная', 'vigilum' ),
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			) );
			if ( $front ) {
				update_option( 'page_on_front', (int) $front );
			}
		}
	}

	if ( empty( get_option( 'vigilum_primary_menu_id' ) ) ) {
		$menu_id = wp_create_nav_menu( __( 'Vigilum — основное', 'vigilum' ) );
		if ( ! is_wp_error( $menu_id ) ) {
			$items = array(
				array( 'title' => __( 'Журнал', 'vigilum' ), 'url' => get_permalink( $ids['journal'] ?? 0 ) ?: home_url( '/journal/' ) ),
				array( 'title' => __( 'Память', 'vigilum' ), 'url' => get_post_type_archive_link( 'vigilum_hero' ) ?: home_url( '/geroi/' ) ),
				array( 'title' => __( 'Инициативы', 'vigilum' ), 'url' => get_post_type_archive_link( 'vigilum_initiative' ) ?: home_url( '/iniciativy/' ) ),
				array( 'title' => __( 'О проекте', 'vigilum' ), 'url' => get_permalink( $ids['about'] ?? 0 ) ?: home_url( '/o-proekte/' ) ),
			);
			foreach ( $items as $item ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'  => $item['title'],
					'menu-item-url'    => $item['url'],
					'menu-item-status' => 'publish',
				) );
			}
			$locations            = get_theme_mod( 'nav_menu_locations', array() );
			$locations['primary'] = (int) $menu_id;
			$locations['footer']  = (int) $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
			update_option( 'vigilum_primary_menu_id', (int) $menu_id );
		}
	}
}
