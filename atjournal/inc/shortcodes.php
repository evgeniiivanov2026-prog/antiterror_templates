<?php
/**
 * Shortcodes so any page can host the journal, the memorial, or the ledger.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'atj_journal', 'atj_shortcode_journal' );
add_shortcode( 'atj_heroes', 'atj_shortcode_heroes' );
add_shortcode( 'atj_initiatives', 'atj_shortcode_initiatives' );
add_shortcode( 'atj_vote', 'atj_shortcode_vote' );

/**
 * Journal cards.
 *
 * @param array<string, string> $atts Attributes.
 * @return string
 */
function atj_shortcode_journal( $atts ) {
	$atts  = shortcode_atts( array( 'count' => 4 ), $atts, 'atj_journal' );
	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => max( 1, min( 12, (int) $atts['count'] ) ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	ob_start();
	if ( $query->have_posts() ) {
		echo '<div class="atj-cards">';
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/content', 'card', array( 'variant' => 'tile' ) );
		}
		echo '</div>';
	}
	wp_reset_postdata();
	return (string) ob_get_clean();
}

/**
 * Memorial cards.
 *
 * @param array<string, string> $atts Attributes.
 * @return string
 */
function atj_shortcode_heroes( $atts ) {
	$atts  = shortcode_atts( array( 'count' => 6 ), $atts, 'atj_heroes' );
	$query = new WP_Query(
		array(
			'post_type'      => 'atj_hero',
			'posts_per_page' => max( 1, min( 24, (int) $atts['count'] ) ),
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'no_found_rows'  => true,
		)
	);
	ob_start();
	if ( $query->have_posts() ) {
		echo '<div class="atj-hero-grid">';
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/content', 'hero' );
		}
		echo '</div>';
	}
	wp_reset_postdata();
	return (string) ob_get_clean();
}

/**
 * Initiative ledger.
 *
 * @param array<string, string> $atts Attributes.
 * @return string
 */
function atj_shortcode_initiatives( $atts ) {
	$atts  = shortcode_atts( array( 'count' => 5 ), $atts, 'atj_initiatives' );
	$query = new WP_Query(
		array(
			'post_type'      => 'atj_initiative',
			'posts_per_page' => max( 1, min( 20, (int) $atts['count'] ) ),
			'meta_key'       => '_atj_vote_count',
			'orderby'        => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
			'no_found_rows'  => true,
		)
	);
	$ids = wp_list_pluck( $query->posts, 'ID' );
	atj_prime_votes( $ids );

	ob_start();
	if ( $query->have_posts() ) {
		echo '<div class="atj-ledger">';
		$index = 0;
		while ( $query->have_posts() ) {
			$query->the_post();
			++$index;
			get_template_part( 'template-parts/content', 'initiative', array( 'index' => $index ) );
		}
		echo '</div>';
	}
	wp_reset_postdata();
	return (string) ob_get_clean();
}

/**
 * A single vote control.
 *
 * @param array<string, string> $atts Attributes.
 * @return string
 */
function atj_shortcode_vote( $atts ) {
	$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'atj_vote' );
	$id   = absint( $atts['id'] );
	if ( ! $id || 'atj_initiative' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
		return '';
	}
	atj_prime_votes( array( $id ) );
	return atj_vote_markup( $id );
}
