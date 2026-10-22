<?php
/**
 * Поддержка темы, меню, стили.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'strazha_setup' );
/**
 * Возможности темы.
 */
function strazha_setup() {
	load_theme_textdomain( 'strazha', STRAZHA_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_image_size( 'strazha-feature', 1600, 1200, true );
	add_image_size( 'strazha-card', 960, 720, true );
	add_image_size( 'strazha-portrait', 800, 1000, array( 'center', 'top' ) );

	register_nav_menus(
		array(
			'primary' => __( 'Основное меню', 'strazha' ),
			'footer'  => __( 'Меню в подвале', 'strazha' ),
		)
	);

	add_editor_style( 'assets/css/editor.css' );

	add_theme_support(
		'starter-content',
		array(
			'posts'     => array(
				'about'    => array(
					'post_type'    => 'page',
					'post_title'   => 'О редакции',
					'post_content' => 'Здесь редакция рассказывает, кто ведёт журнал, по каким правилам готовит материалы и куда писать, если в публикации есть ошибка. Добавляйте только проверяемые сведения о редакции.',
				),
				'contacts' => array(
					'post_type'    => 'page',
					'post_title'   => 'Контакты',
					'post_content' => 'Почта редакции, формальное название организации и ссылка на политику конфиденциальности. При непосредственной угрозе звоните 112 — сайт не принимает экстренные сообщения.',
				),
			),
			'nav_menus' => array(
				'primary' => array(
					'name'  => 'Основное',
					'items' => array(
						'link_journal' => array(
							'title' => 'Журнал',
							'url'   => '#journal',
						),
						'link_memory'  => array(
							'title' => 'Память',
							'url'   => '/pamyat/',
						),
						'link_fund'    => array(
							'title' => 'Инициативы',
							'url'   => '/iniciativy/',
						),
						'page_about'   => array(
							'type'      => 'post_type',
							'object'    => 'page',
							'object_id' => '{{about}}',
						),
					),
				),
				'footer'  => array(
					'name'  => 'Подвал',
					'items' => array(
						'page_about'    => array(
							'type'      => 'post_type',
							'object'    => 'page',
							'object_id' => '{{about}}',
						),
						'page_contacts' => array(
							'type'      => 'post_type',
							'object'    => 'page',
							'object_id' => '{{contacts}}',
						),
					),
				),
			),
			'options'   => array(
				'show_on_front' => 'posts',
			),
		)
	);
}

add_action( 'widgets_init', 'strazha_widgets' );
/**
 * Колонка журнала. Пустая область показывает собранный сайдбар.
 */
function strazha_widgets() {
	register_sidebar(
		array(
			'name'          => __( 'Колонка журнала', 'strazha' ),
			'id'            => 'journal',
			'description'   => __( 'Если добавить виджеты, они заменят стандартную колонку: поиск, рубрики и открытые инициативы.', 'strazha' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget__title">',
			'after_title'   => '</h2>',
		)
	);
}

add_action( 'wp_enqueue_scripts', 'strazha_assets' );
/**
 * Стили, шрифты и сценарии.
 */
function strazha_assets() {
	wp_enqueue_style(
		'strazha-fonts',
		'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Manrope:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400;1,8..60,600&family=Unbounded:wght@400;500&display=swap',
		array(),
		null
	);
	wp_enqueue_style(
		'strazha',
		STRAZHA_URI . '/assets/css/main.css',
		array( 'strazha-fonts' ),
		STRAZHA_VERSION
	);
	wp_enqueue_script(
		'strazha-site',
		STRAZHA_URI . '/assets/js/site.js',
		array(),
		STRAZHA_VERSION,
		true
	);
	wp_localize_script(
		'strazha-site',
		'Strazha',
		array(
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'feedNonce' => wp_create_nonce( 'strazha_feed' ),
			'i18n'      => array(
				'more'    => __( 'Показать ещё', 'strazha' ),
				'loading' => __( 'Загрузка…', 'strazha' ),
				'end'     => __( 'Это все материалы', 'strazha' ),
				'error'   => __( 'Не удалось загрузить материалы', 'strazha' ),
				'dark'    => __( 'Включить тёмную тему', 'strazha' ),
				'light'   => __( 'Включить светлую тему', 'strazha' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

add_filter( 'body_class', 'strazha_body_class' );
/**
 * Класс темы и признак статьи для полосы чтения.
 *
 * @param string[] $classes Классы.
 */
function strazha_body_class( $classes ) {
	$classes[] = 'strazha-site';
	if ( is_singular( 'post' ) ) {
		$classes[] = 'has-progress';
	}
	return $classes;
}

add_filter( 'excerpt_more', 'strazha_excerpt_more' );
/**
 * Многоточие вместо «[…]».
 */
function strazha_excerpt_more() {
	return '…';
}

add_filter( 'excerpt_length', 'strazha_excerpt_length', 20 );
/**
 * Длина отрывка.
 */
function strazha_excerpt_length() {
	return 32;
}

add_filter( 'wp_get_attachment_image_attributes', 'strazha_image_attributes' );
/**
 * Асинхронная расшифровка изображений.
 *
 * @param array<string, string> $attr Атрибуты.
 */
function strazha_image_attributes( $attr ) {
	$attr['decoding'] = 'async';
	return $attr;
}

add_filter( 'comment_form_defaults', 'strazha_comment_defaults' );
/**
 * Русские подписи формы комментария.
 *
 * @param array<string, mixed> $defaults Настройки формы.
 */
function strazha_comment_defaults( $defaults ) {
	$defaults['title_reply']          = __( 'Комментарий', 'strazha' );
	$defaults['title_reply_before']   = '<h2 id="reply-title" class="comment-reply-title">';
	$defaults['label_submit']         = __( 'Отправить', 'strazha' );
	$defaults['comment_notes_before'] = '';
	$defaults['comment_notes_after']  = '';
	return $defaults;
}

add_action( 'wp_head', 'strazha_theme_color', 2 );
/**
 * Цвет панели браузера.
 */
function strazha_theme_color() {
	echo '<meta name="theme-color" content="#f3efe6">' . "\n";
}

add_action( 'wp_head', 'strazha_singular_schema', 20 );
/**
 * Краткая разметка статьи.
 */
function strazha_singular_schema() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	$data = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'NewsArticle',
		'headline'      => get_the_title(),
		'datePublished' => get_the_date( DATE_W3C ),
		'dateModified'  => get_the_modified_date( DATE_W3C ),
		'mainEntityOfPage' => get_permalink(),
		'author'        => array(
			'@type' => 'Person',
			'name'  => get_the_author(),
		),
	);
	if ( has_post_thumbnail() ) {
		$image = wp_get_attachment_image_url( get_post_thumbnail_id(), 'full' );
		if ( $image ) {
			$data['image'] = array( $image );
		}
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

add_filter( 'nav_menu_link_attributes', 'strazha_menu_current', 10, 2 );
/**
 * Явно помечаем текущий пункт, если ядро этого не сделало.
 *
 * @param array<string, string> $atts Атрибуты.
 * @param WP_Post               $item Пункт.
 */
function strazha_menu_current( $atts, $item ) {
	if ( ! empty( $item->current ) && empty( $atts['aria-current'] ) ) {
		$atts['aria-current'] = 'page';
	}
	return $atts;
}
