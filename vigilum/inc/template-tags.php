<?php
/**
 * Template helpers.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function vigilum_brand_small() {
	return vigilum_mod( 'vigilum_brand_small', __( 'Журнал · Фонд', 'vigilum' ) );
}

function vigilum_brand_large() {
	return vigilum_mod( 'vigilum_brand_large', 'Vigilum' );
}

function vigilum_email() {
	$email = vigilum_mod( 'vigilum_email', 'editor@example.org' );
	return is_email( $email ) ? $email : 'editor@example.org';
}

function vigilum_query_posts( $count = 6, $offset = 0 ) {
	return new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => (int) $count,
		'offset'              => (int) $offset,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => false,
	) );
}

function vigilum_query_heroes( $count = 4 ) {
	return new WP_Query( array(
		'post_type'      => 'vigilum_hero',
		'posts_per_page' => (int) $count,
		'post_status'    => 'publish',
	) );
}

function vigilum_query_initiatives( $count = 3, $orderby = 'votes' ) {
	$args = array(
		'post_type'      => 'vigilum_initiative',
		'posts_per_page' => (int) $count,
		'post_status'    => 'publish',
	);

	if ( 'votes' === $orderby ) {
		$args['meta_key'] = '_vigilum_votes';
		$args['orderby']  = 'meta_value_num';
		$args['order']    = 'DESC';
	} elseif ( 'date' === $orderby ) {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
	}

	return new WP_Query( $args );
}

function vigilum_reading_time( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	$text    = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
	$words   = str_word_count( $text );
	$minutes = max( 1, (int) ceil( $words / 180 ) );
	return sprintf(
		/* translators: %s: minutes */
		_n( '%s мин чтения', '%s мин чтения', $minutes, 'vigilum' ),
		number_format_i18n( $minutes )
	);
}

function vigilum_share_links( $url = '', $title = '' ) {
	$url   = $url ?: get_permalink();
	$title = $title ?: get_the_title();
	$enc   = rawurlencode( $url );
	$text  = rawurlencode( $title );
	return array(
		'telegram' => 'https://t.me/share/url?url=' . $enc . '&text=' . $text,
		'vk'       => 'https://vk.com/share.php?url=' . $enc . '&title=' . $text,
		'copy'     => $url,
	);
}

function vigilum_fallback_menu() {
	$items = array(
		array( __( 'Журнал', 'vigilum' ), get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/#journal' ) ),
		array( __( 'Память', 'vigilum' ), get_post_type_archive_link( 'vigilum_hero' ) ?: home_url( '/geroi/' ) ),
		array( __( 'Инициативы', 'vigilum' ), get_post_type_archive_link( 'vigilum_initiative' ) ?: home_url( '/iniciativy/' ) ),
		array( __( 'О проекте', 'vigilum' ), home_url( '/#mission' ) ),
	);
	echo '<ul class="menu">';
	foreach ( $items as $item ) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $item[1] ),
			esc_html( $item[0] )
		);
	}
	echo '</ul>';
}

function vigilum_pagination() {
	the_posts_pagination( array(
		'mid_size'  => 1,
		'prev_text' => '←',
		'next_text' => '→',
	) );
}

add_filter( 'get_the_archive_title', 'vigilum_archive_title' );
function vigilum_archive_title( $title ) {
	if ( is_post_type_archive( 'vigilum_hero' ) ) {
		return __( 'Память героев антитеррора', 'vigilum' );
	}
	if ( is_post_type_archive( 'vigilum_initiative' ) ) {
		return __( 'Фонд инициатив', 'vigilum' );
	}
	if ( is_home() ) {
		return __( 'Журнал', 'vigilum' );
	}
	return $title;
}

add_shortcode( 'vigilum_heroes', 'vigilum_shortcode_heroes' );
function vigilum_shortcode_heroes( $atts ) {
	$atts  = shortcode_atts( array( 'count' => 4 ), $atts, 'vigilum_heroes' );
	$query = vigilum_query_heroes( (int) $atts['count'] );
	ob_start();
	if ( $query->have_posts() ) {
		echo '<div class="heroes-mosaic">';
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/card', 'hero' );
		}
		echo '</div>';
		wp_reset_postdata();
	}
	return ob_get_clean();
}

add_shortcode( 'vigilum_initiatives', 'vigilum_shortcode_initiatives' );
function vigilum_shortcode_initiatives( $atts ) {
	$atts  = shortcode_atts( array( 'count' => 3, 'orderby' => 'votes' ), $atts, 'vigilum_initiatives' );
	$query = vigilum_query_initiatives( (int) $atts['count'], $atts['orderby'] );
	ob_start();
	if ( $query->have_posts() ) {
		echo '<div class="initiative-grid">';
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/card', 'initiative' );
		}
		echo '</div>';
		wp_reset_postdata();
	}
	return ob_get_clean();
}
