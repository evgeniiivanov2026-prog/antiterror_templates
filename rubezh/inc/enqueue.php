<?php
/**
 * Подключение шрифтов, стилей и скриптов.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'rubezh_assets' );
function rubezh_assets() {
	wp_enqueue_style(
		'rubezh-fonts',
		'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400;1,6..72,500&display=swap&subset=cyrillic',
		array(),
		null
	);

	wp_enqueue_style(
		'rubezh-main',
		RUBEZH_URI . '/assets/css/main.css',
		array( 'rubezh-fonts' ),
		RUBEZH_VERSION
	);

	wp_enqueue_script(
		'rubezh-theme',
		RUBEZH_URI . '/assets/js/theme.js',
		array(),
		RUBEZH_VERSION,
		true
	);

	wp_localize_script(
		'rubezh-theme',
		'rubezhTheme',
		array(
			'rest'      => esc_url_raw( rest_url( 'rubezh/v1/vote' ) ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'home'      => esc_url_raw( home_url( '/' ) ),
			'i18n'      => array(
				'voted'     => __( 'Ваш голос учтён', 'rubezh' ),
				'error'     => __( 'Не удалось отправить голос. Попробуйте позже.', 'rubezh' ),
				'duplicate' => __( 'Вы уже поддержали эту инициативу.', 'rubezh' ),
				'openMenu'  => __( 'Открыть меню', 'rubezh' ),
				'closeMenu' => __( 'Закрыть меню', 'rubezh' ),
				'dark'      => __( 'Включить тёмную тему', 'rubezh' ),
				'light'     => __( 'Включить светлую тему', 'rubezh' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
