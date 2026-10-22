<?php
/**
 * Подгрузка следующих материалов летописи.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_rubezh_posts', 'rubezh_ajax_posts' );
add_action( 'wp_ajax_nopriv_rubezh_posts', 'rubezh_ajax_posts' );
/**
 * Следующая страница карточек.
 */
function rubezh_ajax_posts() {
	check_ajax_referer( 'rubezh_posts', 'nonce' );

	$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
	$cat  = isset( $_POST['cat'] ) ? absint( $_POST['cat'] ) : 0;
	$page = max( 1, min( 50, $page ) );

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'paged'               => $page,
		'ignore_sticky_posts' => true,
	);
	if ( $cat && term_exists( $cat, 'category' ) ) {
		$args['cat'] = $cat;
	}

	$query = new WP_Query( $args );
	ob_start();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part(
				'template-parts/content/card',
				null,
				array( 'variant' => 'row' )
			);
		}
	}
	wp_reset_postdata();

	wp_send_json_success(
		array(
			'html' => ob_get_clean(),
			'more' => $page < (int) $query->max_num_pages,
		)
	);
}
