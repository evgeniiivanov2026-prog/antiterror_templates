<?php
/**
 * Theme supports, assets, menus.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'atj_setup' );
/**
 * Register theme supports.
 */
function atj_setup() {
	load_theme_textdomain( 'atjournal', ATJ_DIR . '/languages' );

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
			'width'       => 80,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'editor-font-sizes',
		array(
			array(
				'name' => 'Мелкий',
				'size' => 14,
				'slug' => 'small',
			),
			array(
				'name' => 'Обычный',
				'size' => 18,
				'slug' => 'normal',
			),
			array(
				'name' => 'Крупный',
				'size' => 24,
				'slug' => 'large',
			),
		)
	);

	add_editor_style(
		array(
			'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&display=swap',
			'assets/css/theme.css',
			'assets/css/editor.css',
		)
	);

	add_image_size( 'atj-feature', 1600, 1000, true );
	add_image_size( 'atj-card', 960, 720, true );
	add_image_size( 'atj-portrait', 800, 1040, true );

	register_nav_menus(
		array(
			'primary' => __( 'Основное меню', 'atjournal' ),
			'footer'  => __( 'Меню в подвале', 'atjournal' ),
		)
	);
}

add_action( 'widgets_init', 'atj_widgets' );
/**
 * Journal sidebar.
 */
function atj_widgets() {
	register_sidebar(
		array(
			'name'          => __( 'Колонка журнала', 'atjournal' ),
			'id'            => 'journal',
			'description'   => __( 'Рядом с материалом. Если пусто, тема показывает поиск, рубрики и инициативы.', 'atjournal' ),
			'before_widget' => '<section class="atj-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="atj-widget__title">',
			'after_title'   => '</h2>',
		)
	);
}

add_filter( 'language_attributes', 'atj_language_attributes' );
/**
 * Default theme token. The head script replaces it before paint.
 *
 * @param string $output Attributes.
 * @return string
 */
function atj_language_attributes( $output ) {
	if ( false === strpos( $output, 'data-theme' ) ) {
		$output .= ' data-theme="light"';
	}
	return $output;
}

add_filter( 'body_class', 'atj_body_class' );
/**
 * Body classes.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function atj_body_class( $classes ) {
	$classes[] = 'atj-site';
	if ( is_singular( 'post' ) || is_singular( 'atj_initiative' ) ) {
		$classes[] = 'atj-has-sidebar';
	}
	return $classes;
}

add_filter( 'post_class', 'atj_post_class' );
/**
 * Mark sample cards.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function atj_post_class( $classes ) {
	if ( atj_is_sample( get_the_ID() ) ) {
		$classes[] = 'is-sample';
	}
	return $classes;
}

add_action( 'wp_enqueue_scripts', 'atj_assets' );
/**
 * Front-end assets.
 */
function atj_assets() {
	wp_enqueue_style(
		'atjournal-fonts',
		'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'atjournal', ATJ_URI . '/assets/css/theme.css', array( 'atjournal-fonts' ), ATJ_VERSION );
	wp_enqueue_script( 'atjournal', ATJ_URI . '/assets/js/theme.js', array(), ATJ_VERSION, true );
	wp_localize_script(
		'atjournal',
		'atjTheme',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'atj_vote' ),
			'labels'  => array(
				'support'   => __( 'Поддержать', 'atjournal' ),
				'voted'     => __( 'Голос учтён', 'atjournal' ),
				'closed'    => __( 'Голосование завершено', 'atjournal' ),
				'error'     => __( 'Не удалось сохранить голос. Попробуйте ещё раз.', 'atjournal' ),
				'menuOpen'  => __( 'Открыть меню', 'atjournal' ),
				'menuClose' => __( 'Закрыть меню', 'atjournal' ),
				'themeDark' => __( 'Включить тёмную тему', 'atjournal' ),
				'themeLight'=> __( 'Включить светлую тему', 'atjournal' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

add_filter( 'wp_resource_hints', 'atj_resource_hints', 10, 2 );
/**
 * Preconnect font hosts.
 *
 * @param array<int, string|array<string, string>> $urls URLs.
 * @param string                                   $relation Relation.
 * @return array<int, string|array<string, string>>
 */
function atj_resource_hints( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = array(
			'href' => 'https://fonts.googleapis.com',
		);
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}

add_action( 'wp_head', 'atj_head_meta', 1 );
/**
 * Small head extras.
 */
function atj_head_meta() {
	echo '<meta name="theme-color" content="#faf8f3" media="(prefers-color-scheme: light)">' . "\n";
	echo '<meta name="theme-color" content="#141c2b" media="(prefers-color-scheme: dark)">' . "\n";

	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
	}

	if ( is_singular() && atj_is_sample( get_queried_object_id() ) ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
	}
}

add_filter( 'excerpt_length', 'atj_excerpt_length' );
/**
 * Shorter card excerpts on the front.
 *
 * @param int $length Length.
 * @return int
 */
function atj_excerpt_length( $length ) {
	return is_admin() ? $length : 26;
}

add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

add_filter( 'comment_form_defaults', 'atj_comment_defaults' );
/**
 * Russian comment form even when the site locale is English.
 *
 * @param array<string, string> $defaults Defaults.
 * @return array<string, string>
 */
function atj_comment_defaults( $defaults ) {
	$defaults['title_reply']          = __( 'Комментарий', 'atjournal' );
	$defaults['label_submit']         = __( 'Отправить', 'atjournal' );
	$defaults['comment_notes_before'] = '<p class="comment-notes">' . esc_html__( 'Без оскорблений, без данных третьих лиц и без оперативных подробностей.', 'atjournal' ) . '</p>';
	$defaults['comment_notes_after']  = '';
	$defaults['comment_field']        = '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Комментарий', 'atjournal' ) . '</label><textarea id="comment" name="comment" cols="45" rows="6" required></textarea></p>';
	$user = wp_get_current_user();
	if ( $user->exists() ) {
		$defaults['logged_in_as'] = '<p class="logged-in-as">' . sprintf(
			/* translators: 1: display name, 2: logout URL */
			__( 'Вы вошли как %1$s. <a href="%2$s">Выйти</a>', 'atjournal' ),
			esc_html( $user->display_name ),
			esc_url( wp_logout_url( get_permalink() ) )
		) . '</p>';
	}
	return $defaults;
}

add_filter( 'excerpt_more', 'atj_excerpt_more' );
/**
 * Ellipsis.
 *
 * @param string $more More string.
 * @return string
 */
function atj_excerpt_more( $more ) {
	return is_admin() ? $more : '…';
}

add_action( 'pre_get_posts', 'atj_query' );
/**
 * Archives, search, and initiative order.
 *
 * @param WP_Query $query Query.
 */
function atj_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_search() ) {
		$query->set( 'post_type', array( 'post', 'page', 'atj_hero', 'atj_initiative' ) );
	}

	if ( $query->is_post_type_archive( 'atj_hero' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		$query->set( 'posts_per_page', 12 );
	}

	if ( $query->is_post_type_archive( 'atj_initiative' ) || $query->is_tax( 'atj_area' ) ) {
		$query->set( 'meta_key', '_atj_vote_count' );
		$query->set( 'orderby', array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ) );
		$query->set( 'posts_per_page', 12 );
	}
}

add_action( 'admin_init', 'atj_privacy' );
/**
 * Privacy policy suggestion.
 */
function atj_privacy() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}

	wp_add_privacy_policy_content(
		'Вестник антитеррора',
		wp_kses_post(
			'<p>Голосование за инициативы хранит не имя гостя, а необратимую метку. У читателя без учётной записи в браузере остаётся служебная cookie <code>atj_vid</code> (срок — один год, HttpOnly). По ней нельзя восстановить личность. У вошедшего читателя голос связан с идентификатором учётной записи. IP-адрес в таблицу голосов не записывается. Образцы демонстрационных материалов не являются сведениями о реальных людях.</p>'
		)
	);
}
