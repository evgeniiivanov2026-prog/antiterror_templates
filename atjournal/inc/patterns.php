<?php
/**
 * Block patterns for pages an editor assembles by hand.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'atj_patterns' );
/**
 * Register patterns.
 */
function atj_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	register_block_pattern_category(
		'atjournal',
		array( 'label' => __( 'Вестник антитеррора', 'atjournal' ) )
	);

	register_block_pattern(
		'atjournal/signal',
		array(
			'title'       => __( 'Экстренная плашка', 'atjournal' ),
			'categories'  => array( 'atjournal' ),
			'description' => __( 'Напоминание про 112. Без формы для оперативных сообщений.', 'atjournal' ),
			'content'     => '<!-- wp:group {"className":"atj-pattern-signal"} -->
<div class="wp-block-group atj-pattern-signal"><!-- wp:paragraph -->
<p><strong>Если есть угроза, звоните 112.</strong> Не выясняйте обстоятельства сами и не публикуйте имена, фотографии и адреса.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'atjournal/ledger',
		array(
			'title'       => __( 'Фонд инициатив', 'atjournal' ),
			'categories'  => array( 'atjournal' ),
			'description' => __( 'Лента инициатив с голосованием.', 'atjournal' ),
			'content'     => "<!-- wp:heading -->\n<h2>Фонд инициатив</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Это мнение читателей журнала, а не официальное голосование.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->\n[atj_initiatives count=\"6\"]\n<!-- /wp:shortcode -->",
		)
	);

	register_block_pattern(
		'atjournal/memory',
		array(
			'title'       => __( 'Лента памяти', 'atjournal' ),
			'categories'  => array( 'atjournal' ),
			'description' => __( 'Сетка карточек памяти.', 'atjournal' ),
			'content'     => "<!-- wp:heading -->\n<h2>Память</h2>\n<!-- /wp:heading -->\n\n<!-- wp:shortcode -->\n[atj_heroes count=\"6\"]\n<!-- /wp:shortcode -->",
		)
	);
}
