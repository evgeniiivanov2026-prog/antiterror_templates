<?php
/**
 * Block patterns for editorial pages.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'atj_register_patterns' );
function atj_register_patterns() {
	register_block_pattern_category(
		'antiterror-journal',
		array( 'label' => __( 'Журнал антитеррора', 'antiterror-journal' ) )
	);

	register_block_pattern(
		'antiterror-journal/pullquote',
		array(
			'title'      => __( 'Редакционная цитата', 'antiterror-journal' ),
			'categories' => array( 'antiterror-journal' ),
			'content'    => '<!-- wp:quote {"className":"atj-pullquote"} --><blockquote class="wp-block-quote atj-pullquote"><p>' . esc_html__( 'Память — не лозунг. Это ответственность называть имена и не оставлять угрозу без ответа.', 'antiterror-journal' ) . '</p><cite>' . esc_html__( 'Редакция', 'antiterror-journal' ) . '</cite></blockquote><!-- /wp:quote -->',
		)
	);

	register_block_pattern(
		'antiterror-journal/notice',
		array(
			'title'      => __( 'Экстренное напоминание', 'antiterror-journal' ),
			'categories' => array( 'antiterror-journal' ),
			'content'    => '<!-- wp:group {"className":"atj-notice"} --><div class="wp-block-group atj-notice"><p><strong>112</strong> — ' . esc_html__( 'в экстренной ситуации звоните в службы спасения. Материалы журнала не заменяют официальное обращение.', 'antiterror-journal' ) . '</p></div><!-- /wp:group -->',
		)
	);
}
