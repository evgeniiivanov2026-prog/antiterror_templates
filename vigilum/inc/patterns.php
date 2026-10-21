<?php
/**
 * Block patterns for a flexible homepage and inner pages.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'vigilum_register_patterns' );
function vigilum_register_patterns() {
	register_block_pattern_category( 'vigilum', array(
		'label' => __( 'Vigilum', 'vigilum' ),
	) );

	register_block_pattern( 'vigilum/mission', array(
		'title'      => __( 'Миссия редакции', 'vigilum' ),
		'categories' => array( 'vigilum' ),
		'content'    => '<!-- wp:group {"align":"wide","className":"pattern-mission"} -->
<div class="wp-block-group alignwide pattern-mission"><!-- wp:heading {"level":2} --><h2 class="wp-block-heading">' . esc_html__( 'Не оставлять угрозу без ответа', 'vigilum' ) . '</h2><!-- /wp:heading -->
<!-- wp:paragraph {"className":"is-style-lede"} --><p class="is-style-lede">' . esc_html__( 'Мы создаём понятный и ответственный канал: журналистика фактов, память героев и гражданские инициативы в правовом поле.', 'vigilum' ) . '</p><!-- /wp:paragraph --></div>
<!-- /wp:group -->',
	) );

	register_block_pattern( 'vigilum/principles', array(
		'title'      => __( 'Три принципа', 'vigilum' ),
		'categories' => array( 'vigilum' ),
		'content'    => '<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'Факт', 'vigilum' ) . '</h3><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html__( 'Публикуем только проверяемые сведения. Без сенсаций и давления.', 'vigilum' ) . '</p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'Память', 'vigilum' ) . '</h3><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html__( 'Храним истории героев антитеррора уважительно и точно.', 'vigilum' ) . '</p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html__( 'Закон', 'vigilum' ) . '</h3><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html__( 'Инициативы и обращения — только в правовом поле.', 'vigilum' ) . '</p><!-- /wp:paragraph --></div><!-- /wp:column --></div>
<!-- /wp:columns -->',
	) );
}
