<?php
/**
 * Подгрузка ленты.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_strazha_feed', 'strazha_feed' );
add_action( 'wp_ajax_nopriv_strazha_feed', 'strazha_feed' );
/**
 * Следующая страница журнала.
 */
function strazha_feed() {
	check_ajax_referer( 'strazha_feed', 'nonce' );

	$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
	$page = max( 1, min( 200, $page ) );

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'paged'               => $page,
		'ignore_sticky_posts' => true,
	);

	$cat = isset( $_POST['cat'] ) ? absint( $_POST['cat'] ) : 0;
	$tag = isset( $_POST['tag'] ) ? absint( $_POST['tag'] ) : 0;
	$author = isset( $_POST['author'] ) ? absint( $_POST['author'] ) : 0;
	$year   = isset( $_POST['year'] ) ? absint( $_POST['year'] ) : 0;
	$month  = isset( $_POST['month'] ) ? absint( $_POST['month'] ) : 0;
	$day    = isset( $_POST['day'] ) ? absint( $_POST['day'] ) : 0;
	$search = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';

	if ( $cat ) {
		$args['cat'] = $cat;
	}
	if ( $tag ) {
		$args['tag_id'] = $tag;
	}
	if ( $author ) {
		$args['author'] = $author;
	}
	if ( $year ) {
		$args['year'] = $year;
	}
	if ( $month ) {
		$args['monthnum'] = min( 12, $month );
	}
	if ( $day ) {
		$args['day'] = min( 31, $day );
	}
	if ( '' !== $search ) {
		$args['s'] = mb_substr( $search, 0, 120 );
	}

	$query = new WP_Query( $args );
	ob_start();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/content/card' );
		}
		wp_reset_postdata();
	}
	$html = ob_get_clean();

	wp_send_json_success(
		array(
			'html' => $html,
			'more' => $page < (int) $query->max_num_pages,
		)
	);
}
