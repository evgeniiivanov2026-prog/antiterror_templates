<?php
/**
 * Sidebars and widgets.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'widgets_init', 'atj_widgets_init' );
function atj_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Боковая колонка журнала', 'antiterror-journal' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Появляется в архивах и статьях, если назначены виджеты.', 'antiterror-journal' ),
			'before_widget' => '<section id="%1$s" class="atj-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="atj-widget__title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Подвал, колонка 1', 'antiterror-journal' ),
			'id'            => 'footer-1',
			'before_widget' => '<section id="%1$s" class="atj-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="atj-widget__title">',
			'after_title'   => '</h2>',
		)
	);
}
