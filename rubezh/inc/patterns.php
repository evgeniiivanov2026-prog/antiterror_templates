<?php
/**
 * Блоки для редакционных материалов.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'rubezh_register_patterns' );
/**
 * Паттерны редактора.
 */
function rubezh_register_patterns() {
	register_block_pattern_category(
		'rubezh',
		array( 'label' => __( 'Рубеж', 'rubezh' ) )
	);

	register_block_pattern(
		'rubezh/note',
		array(
			'title'      => __( 'Редакционная врезка', 'rubezh' ),
			'categories' => array( 'rubezh' ),
			'content'    => '<!-- wp:group {"className":"rz-note"} --><div class="wp-block-group rz-note"><!-- wp:paragraph --><p><strong>От редакции.</strong> Короткое пояснение к материалу. Без домыслов и без призыва действовать самостоятельно.</p><!-- /wp:paragraph --></div><!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'rubezh/pull',
		array(
			'title'      => __( 'Крупная цитата', 'rubezh' ),
			'categories' => array( 'rubezh' ),
			'content'    => '<!-- wp:quote --><blockquote class="wp-block-quote"><p>Проверенная формулировка, которую можно вынести из текста.</p><cite>Источник</cite></blockquote><!-- /wp:quote -->',
		)
	);
}
