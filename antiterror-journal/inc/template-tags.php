<?php
/**
 * Helpers for dates, media, settings and small markup.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme options with defaults.
 *
 * @return array<string, string>
 */
function rubezh_settings() {
	$defaults = array(
		'issue_no'    => '07',
		'issue_label' => 'Осень 2026',
		'email'       => '',
		'lead'        => '',
	);
	$saved    = get_option( 'rubezh_settings', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return array_merge( $defaults, $saved );
}

/**
 * Single option.
 *
 * @param string $key Option key.
 * @return string
 */
function rubezh_setting( $key ) {
	$all = rubezh_settings();
	return isset( $all[ $key ] ) ? (string) $all[ $key ] : '';
}

/**
 * Russian date that does not depend on the site locale pack.
 *
 * @param int|WP_Post|null $post Post.
 * @return string
 */
function rubezh_date( $post = null ) {
	$timestamp = get_post_timestamp( $post );
	if ( ! $timestamp ) {
		return '';
	}
	$months = array(
		1  => 'января',
		2  => 'февраля',
		3  => 'марта',
		4  => 'апреля',
		5  => 'мая',
		6  => 'июня',
		7  => 'июля',
		8  => 'августа',
		9  => 'сентября',
		10 => 'октября',
		11 => 'ноября',
		12 => 'декабря',
	);
	$day   = wp_date( 'j', $timestamp );
	$month = (int) wp_date( 'n', $timestamp );
	$year  = wp_date( 'Y', $timestamp );
	$name  = isset( $months[ $month ] ) ? $months[ $month ] : wp_date( 'F', $timestamp );
	return $day . ' ' . $name . ' ' . $year;
}

/**
 * ISO-8601 datetime.
 *
 * @param int|WP_Post|null $post Post.
 * @return string
 */
function rubezh_date_iso( $post = null ) {
	return (string) get_post_time( 'c', true, $post );
}

/**
 * Reading time in minutes for Cyrillic text.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function rubezh_reading_minutes( $post_id ) {
	$text = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
	preg_match_all( '/[\p{L}\p{N}]+/u', $text, $matches );
	$words = isset( $matches[0] ) ? count( $matches[0] ) : 0;
	return max( 1, (int) ceil( $words / 180 ) );
}

/**
 * First category or a calm fallback.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function rubezh_kicker_label( $post_id ) {
	$categories = get_the_category( $post_id );
	if ( ! empty( $categories ) ) {
		return $categories[0]->name;
	}
	$tracks = get_the_terms( $post_id, 'rubezh_track' );
	if ( ! empty( $tracks ) && ! is_wp_error( $tracks ) ) {
		return $tracks[0]->name;
	}
	return __( 'Журнал', 'rubezh' );
}

/**
 * Initiative direction name.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function rubezh_track_label( $post_id ) {
	$terms = get_the_terms( $post_id, 'rubezh_track' );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}
	return $terms[0]->name;
}

/**
 * Whether the entry is seeded sample content.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function rubezh_is_sample( $post_id ) {
	return '1' === (string) get_post_meta( $post_id, '_rubezh_sample', true );
}

/**
 * Trimmed excerpt.
 *
 * @param int $post_id Post ID.
 * @param int $words   Word count.
 * @return string
 */
function rubezh_excerpt( $post_id, $words = 28 ) {
	$raw = get_the_excerpt( $post_id );
	if ( '' === trim( wp_strip_all_tags( (string) $raw ) ) ) {
		$raw = get_post_field( 'post_content', $post_id );
	}
	return wp_trim_words( wp_strip_all_tags( (string) $raw ), $words, '…' );
}

/**
 * Featured image or a typographic monogram.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Image size.
 * @return string
 */
function rubezh_media_html( $post_id, $size = 'large' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail(
			$post_id,
			$size,
			array(
				'class'   => 'rz-media',
				'loading' => 'lazy',
			)
		);
	}
	$title  = get_the_title( $post_id );
	$letter = function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 );
	$letter = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $letter ) : strtoupper( $letter );
	return '<span class="rz-monogram" aria-hidden="true">' . esc_html( $letter ) . '</span>';
}

/**
 * Published count for a post type.
 *
 * @param string $post_type Post type.
 * @return int
 */
function rubezh_published_count( $post_type ) {
	$counts = wp_count_posts( $post_type );
	return ( $counts && isset( $counts->publish ) ) ? (int) $counts->publish : 0;
}

/**
 * Padded number for the cover facts.
 *
 * @param int $number Number.
 * @return string
 */
function rubezh_pad( $number ) {
	return str_pad( (string) max( 0, (int) $number ), 2, '0', STR_PAD_LEFT );
}

/**
 * Russian plural form.
 *
 * @param int    $number Count.
 * @param string $one    Form for 1.
 * @param string $few    Form for 2–4.
 * @param string $many   Form for 5+.
 * @return string
 */
function rubezh_plural( $number, $one, $few, $many ) {
	$n  = abs( (int) $number ) % 100;
	$n1 = $n % 10;
	if ( $n > 10 && $n < 20 ) {
		return $many;
	}
	if ( 1 === $n1 ) {
		return $one;
	}
	if ( $n1 >= 2 && $n1 <= 4 ) {
		return $few;
	}
	return $many;
}

/**
 * Status label for an initiative.
 *
 * @param string $status Status slug.
 * @return string
 */
function rubezh_status_label( $status ) {
	$map = array(
		'collecting' => __( 'Сбор голосов', 'rubezh' ),
		'review'     => __( 'На рассмотрении', 'rubezh' ),
		'adopted'    => __( 'Принята', 'rubezh' ),
	);
	return isset( $map[ $status ] ) ? $map[ $status ] : $map['collecting'];
}

/**
 * Initiative status slug.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function rubezh_initiative_status( $post_id ) {
	$status = (string) get_post_meta( $post_id, '_rubezh_status', true );
	$known  = array( 'collecting', 'review', 'adopted' );
	return in_array( $status, $known, true ) ? $status : 'collecting';
}

/**
 * Breadcrumbs.
 *
 * @param array<int, array<string, string>> $items Trail after the home link.
 */
function rubezh_breadcrumbs( $items ) {
	echo '<nav class="rz-crumbs" aria-label="' . esc_attr__( 'Путь по сайту', 'rubezh' ) . '">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Главная', 'rubezh' ) . '</a>';
	foreach ( $items as $item ) {
		echo '<span aria-hidden="true">/</span>';
		if ( ! empty( $item['url'] ) ) {
			echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
		} else {
			echo '<span>' . esc_html( $item['label'] ) . '</span>';
		}
	}
	echo '</nav>';
}

/**
 * Pagination for a query.
 *
 * @param WP_Query $query Query.
 * @param int      $paged Current page.
 */
function rubezh_pagination( $query, $paged ) {
	if ( (int) $query->max_num_pages < 2 ) {
		return;
	}
	$big   = 999999999;
	$links = paginate_links(
		array(
			'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'    => '?paged=%#%',
			'current'   => max( 1, (int) $paged ),
			'total'     => (int) $query->max_num_pages,
			'type'      => 'list',
			'prev_text' => '←',
			'next_text' => '→',
		)
	);
	if ( $links ) {
		echo '<nav class="rz-pager" aria-label="' . esc_attr__( 'Страницы', 'rubezh' ) . '">' . wp_kses_post( $links ) . '</nav>';
	}
}

/**
 * Current page number for archives and paged pages.
 *
 * @return int
 */
function rubezh_paged() {
	return max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
}

/**
 * Arrow icon.
 *
 * @return string
 */
function rubezh_arrow() {
	return '<svg class="rz-arrow" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>';
}

/**
 * Shield mark: a threshold, not a copied emblem.
 *
 * @return string
 */
function rubezh_mark() {
	return '<svg class="rz-logo__mark" viewBox="0 0 48 56" aria-hidden="true"><path d="M8 4h32v34c0 7-7 12-16 16C15 50 8 45 8 38V4Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M8 22h32M24 22v18" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>';
}

/**
 * Fallback primary menu before a menu is assigned.
 *
 * @param array<string, mixed> $args Menu args.
 */
function rubezh_fallback_menu( $args = array() ) {
	$class = isset( $args['menu_class'] ) ? $args['menu_class'] : 'rz-nav__list';
	$items = rubezh_default_links();
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $items as $item ) {
		echo '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Default section links.
 *
 * @return array<int, array<string, string>>
 */
function rubezh_default_links() {
	$journal = rubezh_journal_url();
	$memory  = get_post_type_archive_link( 'rubezh_hero' );
	$fund    = get_post_type_archive_link( 'rubezh_initiative' );
	$about   = rubezh_page_url( 'o-zhurnale' );
	return array(
		array(
			'label' => __( 'Журнал', 'rubezh' ),
			'url'   => $journal ? $journal : home_url( '/' ),
		),
		array(
			'label' => __( 'Память', 'rubezh' ),
			'url'   => $memory ? $memory : home_url( '/pamyat/' ),
		),
		array(
			'label' => __( 'Инициативы', 'rubezh' ),
			'url'   => $fund ? $fund : home_url( '/iniciativy/' ),
		),
		array(
			'label' => __( 'О журнале', 'rubezh' ),
			'url'   => $about ? $about : home_url( '/' ),
		),
	);
}

/**
 * URL of the journal page if it exists.
 *
 * @return string
 */
function rubezh_journal_url() {
	$url = rubezh_page_url( 'zhurnal' );
	return $url ? $url : home_url( '/' );
}

/**
 * Permalink for a page slug.
 *
 * @param string $slug Page slug.
 * @return string
 */
function rubezh_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page instanceof WP_Post ) {
		return (string) get_permalink( $page );
	}
	return '';
}

/**
 * Site name shown in the wordmark.
 *
 * @return string
 */
function rubezh_site_name() {
	$name = (string) get_bloginfo( 'name' );
	if ( '' === $name || 'WordPress' === $name ) {
		return 'Рубеж';
	}
	return $name;
}

/**
 * Lead paragraph for the cover.
 *
 * @return string
 */
function rubezh_lead_text() {
	$custom = trim( rubezh_setting( 'lead' ) );
	if ( '' !== $custom ) {
		return $custom;
	}
	return __( 'Редакционная полоса о противодействии терроризму: хроника без сенсации, память людей и открытый фонд, где жители поддерживают общественные инициативы голосом.', 'rubezh' );
}

/**
 * Sample chip.
 *
 * @param int $post_id Post ID.
 */
function rubezh_sample_chip( $post_id ) {
	if ( ! rubezh_is_sample( $post_id ) ) {
		return;
	}
	echo '<span class="rz-sample">' . esc_html__( 'Образец', 'rubezh' ) . '</span>';
}
