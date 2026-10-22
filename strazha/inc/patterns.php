<?php
/**
 * Блоки для редактора.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'strazha_patterns' );
/**
 * Паттерны редакции.
 */
function strazha_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	register_block_pattern_category(
		'strazha',
		array( 'label' => __( 'Стража', 'strazha' ) )
	);

	register_block_pattern(
		'strazha/editorial-note',
		array(
			'title'      => __( 'Редакционная врезка', 'strazha' ),
			'categories' => array( 'strazha' ),
			'content'    => '<!-- wp:group {"className":"strazha-note","layout":{"type":"constrained"}} -->
<div class="wp-block-group strazha-note"><!-- wp:paragraph -->
<p><strong>Как читать этот материал.</strong> Это хроника и контекст, не инструкция. При непосредственной угрозе звоните 112 и не пытайтесь вмешиваться самостоятельно.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'strazha/pull-quote',
		array(
			'title'      => __( 'Вынос цитаты', 'strazha' ),
			'categories' => array( 'strazha' ),
			'content'    => '<!-- wp:pullquote -->
<figure class="wp-block-pullquote"><blockquote><p>Имя важнее сводки. Сначала человек, потом обстоятельства.</p></blockquote></figure>
<!-- /wp:pullquote -->',
		)
	);

	register_block_pattern(
		'strazha/initiative-lead',
		array(
			'title'      => __( 'Лид инициативы', 'strazha' ),
			'categories' => array( 'strazha' ),
			'content'    => '<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Коротко: какую общественную задачу решает инициатива, кого она касается и что можно сделать в правовом поле. Без сбора средств и без призывов к самостоятельным действиям.</p>
<!-- /wp:paragraph -->',
		)
	);
}
