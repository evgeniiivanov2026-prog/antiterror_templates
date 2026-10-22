<?php
/**
 * Template helpers.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function atj_contact_email() {
	$email = atj_mod( 'atj_contact_email', get_option( 'admin_email' ) );
	return is_email( $email ) ? $email : get_option( 'admin_email' );
}

function atj_mailto_report() {
	$subject = rawurlencode( __( 'Конфиденциальное обращение', 'antiterror-journal' ) );
	return 'mailto:' . antispambot( atj_contact_email() ) . '?subject=' . $subject;
}

function atj_query_posts( $args = array() ) {
	return new WP_Query(
		wp_parse_args(
			$args,
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => false,
				'no_found_rows'       => true,
			)
		)
	);
}

function atj_query_featured( $post_type, $fallback_count = 4 ) {
	$featured = new WP_Query(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => $fallback_count,
			'meta_key'       => '_atj_featured',
			'meta_value'     => '1',
			'no_found_rows'  => true,
		)
	);

	if ( $featured->have_posts() ) {
		return $featured;
	}

	$orderby = 'atj_initiative' === $post_type
		? array( 'meta_value_num' => 'DESC', 'date' => 'DESC' )
		: 'date';

	$query_args = array(
		'post_type'      => $post_type,
		'post_status'    => 'publish',
		'posts_per_page' => $fallback_count,
		'no_found_rows'  => true,
		'orderby'        => $orderby,
	);

	if ( 'atj_initiative' === $post_type ) {
		$query_args['meta_key'] = '_atj_votes';
	}

	return new WP_Query( $query_args );
}

function atj_posted_on() {
	$time_string = sprintf(
		'<time class="atj-meta__time" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date( 'd.m.Y' ) )
	);

	$categories = '';
	if ( 'post' === get_post_type() ) {
		$categories = get_the_category_list( ' · ' );
	}

	echo '<p class="atj-meta">';
	echo $time_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	if ( $categories ) {
		echo ' <span class="atj-meta__cats">' . wp_kses_post( $categories ) . '</span>';
	}
	echo '</p>';
}

function atj_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	echo '<nav class="atj-breadcrumbs" aria-label="' . esc_attr__( 'Навигационная цепочка', 'antiterror-journal' ) . '">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Журнал', 'antiterror-journal' ) . '</a>';
	echo '<span aria-hidden="true">/</span>';

	if ( is_singular( 'atj_hero' ) ) {
		echo '<a href="' . esc_url( get_post_type_archive_link( 'atj_hero' ) ) . '">' . esc_html__( 'Память героев', 'antiterror-journal' ) . '</a>';
		echo '<span aria-hidden="true">/</span>';
		echo '<span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_singular( 'atj_initiative' ) ) {
		echo '<a href="' . esc_url( get_post_type_archive_link( 'atj_initiative' ) ) . '">' . esc_html__( 'Фонд инициатив', 'antiterror-journal' ) . '</a>';
		echo '<span aria-hidden="true">/</span>';
		echo '<span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_singular() ) {
		$cats = get_the_category();
		if ( $cats ) {
			echo '<a href="' . esc_url( get_category_link( $cats[0] ) ) . '">' . esc_html( $cats[0]->name ) . '</a>';
			echo '<span aria-hidden="true">/</span>';
		}
		echo '<span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_post_type_archive() ) {
		echo '<span>' . esc_html( post_type_archive_title( '', false ) ) . '</span>';
	} elseif ( is_search() ) {
		echo '<span>' . esc_html__( 'Поиск', 'antiterror-journal' ) . '</span>';
	} elseif ( is_archive() ) {
		echo '<span>' . wp_kses_post( get_the_archive_title() ) . '</span>';
	}

	echo '</nav>';
}

function atj_default_nav_items() {
	return array(
		array( home_url( '/#journal' ), __( 'Журнал', 'antiterror-journal' ) ),
		array( get_post_type_archive_link( 'atj_hero' ) ?: home_url( '/#memory' ), __( 'Память', 'antiterror-journal' ) ),
		array( get_post_type_archive_link( 'atj_initiative' ) ?: home_url( '/#fund' ), __( 'Фонд', 'antiterror-journal' ) ),
	);
}

function atj_vote_button( $post_id, $variant = 'card' ) {
	$votes  = (int) get_post_meta( $post_id, '_atj_votes', true );
	$open   = atj_initiative_is_open( $post_id );
	$voted  = in_array( (int) $post_id, atj_get_voted_ids(), true );
	$label  = $voted ? __( 'Голос учтён', 'antiterror-journal' ) : __( 'Поддержать', 'antiterror-journal' );

	printf(
		'<div class="atj-vote atj-vote--%1$s" data-vote-root data-voted="%2$s">
			<button class="atj-vote__button" type="button" data-vote="%3$d"%4$s>
				<span class="atj-vote__mark" aria-hidden="true">+</span>
				<span class="atj-vote__text">%5$s</span>
			</button>
			<p class="atj-vote__count" data-vote-count="%3$d"><strong>%6$d</strong> <span>%7$s</span></p>
		</div>',
		esc_attr( $variant ),
		$voted ? 'true' : 'false',
		(int) $post_id,
		( $open && ! $voted ) ? '' : ' disabled',
		esc_html( $open ? $label : __( 'Голосование закрыто', 'antiterror-journal' ) ),
		$votes,
		esc_html__( 'голосов', 'antiterror-journal' )
	);
}

function atj_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => __( 'Назад', 'antiterror-journal' ),
			'next_text' => __( 'Дальше', 'antiterror-journal' ),
		)
	);
}
