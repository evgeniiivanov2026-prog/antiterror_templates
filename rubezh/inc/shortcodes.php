<?php
/**
 * Вставка памяти и инициатив в любую страницу.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'rubezh_initiatives', 'rubezh_shortcode_initiatives' );
/**
 * [rubezh_initiatives count="6"]
 *
 * @param array<string, string> $atts Атрибуты.
 * @return string
 */
function rubezh_shortcode_initiatives( $atts ) {
	$atts  = shortcode_atts( array( 'count' => 6 ), $atts, 'rubezh_initiatives' );
	$query = new WP_Query(
		array(
			'post_type'      => 'initiative',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, min( 24, (int) $atts['count'] ) ),
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		)
	);
	ob_start();
	if ( $query->have_posts() ) {
		rubezh_render_ballots( $query );
		echo '<p class="rz-disclaimer">' . esc_html__( 'Голос — общественная поддержка, а не официальное решение.', 'rubezh' ) . '</p>';
	}
	wp_reset_postdata();
	return (string) ob_get_clean();
}

add_shortcode( 'rubezh_memorial', 'rubezh_shortcode_memorial' );
/**
 * [rubezh_memorial count="6"]
 *
 * @param array<string, string> $atts Атрибуты.
 * @return string
 */
function rubezh_shortcode_memorial( $atts ) {
	$atts  = shortcode_atts( array( 'count' => 6 ), $atts, 'rubezh_memorial' );
	$query = new WP_Query(
		array(
			'post_type'      => 'hero',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, min( 24, (int) $atts['count'] ) ),
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		)
	);
	ob_start();
	if ( $query->have_posts() ) {
		echo '<div class="rz-rail">';
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/content/portrait' );
		}
		echo '</div>';
	}
	wp_reset_postdata();
	return (string) ob_get_clean();
}
