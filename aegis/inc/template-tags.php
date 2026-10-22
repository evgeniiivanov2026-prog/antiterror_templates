<?php
/**
 * Template helpers.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aegis_option( $key, $default = '' ) {
	$value = get_theme_mod( $key, $default );
	return is_string( $value ) ? $value : $default;
}

function aegis_kicker() {
	return aegis_option( 'aegis_kicker', __( 'Журнал антитеррора • Память • Инициативы', 'aegis' ) );
}

function aegis_city() {
	return aegis_option( 'aegis_city', __( 'Россия', 'aegis' ) );
}

function aegis_email() {
	$email = aegis_option( 'aegis_email', get_option( 'admin_email' ) );
	return sanitize_email( $email );
}

function aegis_edition_line() {
	$now = wp_date( 'j F Y' );
	return sprintf(
		/* translators: 1: city, 2: formatted date */
		__( '%1$s · выпуск от %2$s', 'aegis' ),
		aegis_city(),
		$now
	);
}

function aegis_fallback_menu() {
	$links = array(
		home_url( '/#journal' )      => __( 'Журнал', 'aegis' ),
		get_post_type_archive_link( 'aegis_hero' ) ?: home_url( '/#memory' ) => __( 'Память', 'aegis' ),
		get_post_type_archive_link( 'aegis_initiative' ) ?: home_url( '/#initiatives' ) => __( 'Инициативы', 'aegis' ),
		home_url( '/#foundation' )   => __( 'Фонд', 'aegis' ),
	);
	echo '<ul class="menu">';
	foreach ( $links as $url => $label ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

function aegis_posted_on() {
	$time = sprintf(
		'<time class="entry-date" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);

	$cats = '';
	if ( 'post' === get_post_type() ) {
		$cats = get_the_category_list( ' · ' );
	}

	echo '<p class="entry-meta">';
	echo $time; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	if ( $cats ) {
		echo '<span class="entry-meta__sep" aria-hidden="true">/</span><span class="entry-cats">' . $cats . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</p>';
}

function aegis_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	echo '<nav class="aegis-crumbs" aria-label="' . esc_attr__( 'Хлебные крошки', 'aegis' ) . '">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Главная', 'aegis' ) . '</a>';

	if ( is_singular( 'aegis_hero' ) ) {
		$archive = get_post_type_archive_link( 'aegis_hero' );
		echo '<span aria-hidden="true">/</span><a href="' . esc_url( $archive ) . '">' . esc_html__( 'Память героев', 'aegis' ) . '</a>';
	} elseif ( is_singular( 'aegis_initiative' ) ) {
		$archive = get_post_type_archive_link( 'aegis_initiative' );
		echo '<span aria-hidden="true">/</span><a href="' . esc_url( $archive ) . '">' . esc_html__( 'Инициативы', 'aegis' ) . '</a>';
	} elseif ( is_singular( 'post' ) ) {
		$cats = get_the_category();
		if ( $cats ) {
			echo '<span aria-hidden="true">/</span><a href="' . esc_url( get_category_link( $cats[0] ) ) . '">' . esc_html( $cats[0]->name ) . '</a>';
		}
	} elseif ( is_archive() ) {
		echo '<span aria-hidden="true">/</span><span>' . wp_kses_post( get_the_archive_title() ) . '</span>';
	}

	if ( is_singular() ) {
		echo '<span aria-hidden="true">/</span><span>' . esc_html( get_the_title() ) . '</span>';
	}

	echo '</nav>';
}

function aegis_related_posts( $limit = 3 ) {
	$post_id = get_the_ID();
	$cats    = wp_get_post_categories( $post_id );
	$query   = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => $limit,
		'post__not_in'        => array( $post_id ),
		'category__in'        => $cats,
		'ignore_sticky_posts' => true,
	) );

	if ( ! $query->have_posts() ) {
		$query = new WP_Query( array(
			'post_type'           => 'post',
			'posts_per_page'      => $limit,
			'post__not_in'        => array( $post_id ),
			'ignore_sticky_posts' => true,
		) );
	}

	if ( ! $query->have_posts() ) {
		return;
	}

	echo '<section class="related-posts" aria-labelledby="related-title">';
	echo '<p class="section-kicker">' . esc_html__( '05 / Дальше в журнале', 'aegis' ) . '</p>';
	echo '<h2 id="related-title">' . esc_html__( 'Читать следом', 'aegis' ) . '</h2>';
	echo '<div class="related-posts__grid">';
	while ( $query->have_posts() ) {
		$query->the_post();
		get_template_part( 'template-parts/card', 'post' );
	}
	wp_reset_postdata();
	echo '</div></section>';
}

function aegis_hero_meta( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return array(
		'years'  => get_post_meta( $post_id, '_aegis_years', true ),
		'service'=> get_post_meta( $post_id, '_aegis_service', true ),
		'award'  => get_post_meta( $post_id, '_aegis_award', true ),
		'date'   => get_post_meta( $post_id, '_aegis_feat_date', true ),
	);
}

function aegis_initiative_meta( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	$status  = get_post_meta( $post_id, '_aegis_status', true );
	if ( ! $status ) {
		$status = 'open';
	}
	return array(
		'status' => $status,
		'author' => get_post_meta( $post_id, '_aegis_author_name', true ),
		'votes'  => absint( get_post_meta( $post_id, '_aegis_votes', true ) ),
	);
}

function aegis_status_label( $status ) {
	$map = array(
		'open'     => __( 'Открыта', 'aegis' ),
		'review'   => __( 'На рассмотрении', 'aegis' ),
		'accepted' => __( 'Принята', 'aegis' ),
		'closed'   => __( 'Завершена', 'aegis' ),
	);
	return isset( $map[ $status ] ) ? $map[ $status ] : $map['open'];
}
