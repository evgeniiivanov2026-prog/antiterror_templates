<?php
/**
 * Scripts and styles.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'atj_enqueue_assets' );
function atj_enqueue_assets() {
	wp_enqueue_style(
		'atj-fonts',
		'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&family=Newsreader:opsz,wght@6..72,400;500;600;700&display=swap&subset=cyrillic',
		array(),
		null
	);

	wp_enqueue_style(
		'atj-theme',
		get_template_directory_uri() . '/assets/css/theme.css',
		array( 'atj-fonts' ),
		ATJ_VERSION
	);

	wp_enqueue_script(
		'atj-theme',
		get_template_directory_uri() . '/assets/js/theme.js',
		array(),
		ATJ_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);

	if ( is_singular( 'atj_initiative' ) || is_post_type_archive( 'atj_initiative' ) || is_front_page() || is_page_template( 'page-fund.php' ) ) {
		wp_enqueue_script(
			'atj-vote',
			get_template_directory_uri() . '/assets/js/vote.js',
			array(),
			ATJ_VERSION,
			array( 'strategy' => 'defer', 'in_footer' => true )
		);

		wp_localize_script(
			'atj-vote',
			'atjVote',
			array(
				'endpoint' => esc_url_raw( rest_url( 'antiterror-journal/v1/vote' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'i18n'     => array(
					'done'    => __( 'Голос учтён', 'antiterror-journal' ),
					'error'   => __( 'Не удалось отправить голос. Попробуйте позже.', 'antiterror-journal' ),
					'closed'  => __( 'Голосование закрыто', 'antiterror-journal' ),
					'duplicate' => __( 'Вы уже голосовали за эту инициативу', 'antiterror-journal' ),
				),
			)
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

add_filter( 'wp_resource_hints', 'atj_resource_hints', 10, 2 );
function atj_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href'        => 'https://fonts.googleapis.com',
			'crossorigin' => 'anonymous',
		);
		$urls[] = 'https://fonts.gstatic.com';
	}
	return $urls;
}
