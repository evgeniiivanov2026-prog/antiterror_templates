<?php
/**
 * Template helpers.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customizer defaults.
 *
 * @return array<string, string>
 */
function atj_defaults() {
	return array(
		'kicker'   => 'Общественный журнал',
		'issue'    => 'Живой выпуск',
		'headline' => "Помнить.\nРазбирать.\nУчаствовать.",
		'lead'     => 'Материалы о противодействии терроризму, раздел памяти и открытый фонд инициатив, за которые читатели голосуют сами.',
		'email'    => '',
	);
}

/**
 * Theme mod with a built-in default.
 *
 * @param string $key Key without the atj_ prefix.
 * @return string
 */
function atj_mod( $key ) {
	$defaults = atj_defaults();
	$default  = $defaults[ $key ] ?? '';
	$value    = get_theme_mod( 'atj_' . $key, $default );
	return is_string( $value ) ? $value : $default;
}

/**
 * Headline with the last line set in italic.
 *
 * @param string $text Raw headline.
 * @return string
 */
function atj_headline_html( $text ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $text );
	$lines = array_values( array_filter( array_map( 'trim', $lines ) ) );
	if ( ! $lines ) {
		$lines = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', atj_defaults()['headline'] ) ) ) );
	}
	$last = count( $lines ) - 1;
	$html = array();
	foreach ( $lines as $index => $line ) {
		$safe   = esc_html( $line );
		$html[] = ( $index === $last && $last > 0 ) ? '<em>' . $safe . '</em>' : $safe;
	}
	return implode( '<br>', $html );
}

/**
 * Journal listing URL. Falls back to the homepage anchor when there is no posts page.
 *
 * @return string
 */
function atj_journal_url() {
	$posts_page = (int) get_option( 'page_for_posts' );
	if ( $posts_page ) {
		$link = get_permalink( $posts_page );
		if ( $link ) {
			return $link;
		}
	}
	return home_url( '/#journal' );
}

/**
 * Primary destinations used by fallback menus.
 *
 * @return array<int, array{0: string, 1: string}>
 */
function atj_nav_links() {
	$links   = array();
	$links[] = array( __( 'Журнал', 'atjournal' ), atj_journal_url() );
	$memory  = get_post_type_archive_link( 'atj_hero' );
	$ledger  = get_post_type_archive_link( 'atj_initiative' );
	$links[] = array( __( 'Память', 'atjournal' ), $memory ? $memory : home_url( '/#pamyat' ) );
	$links[] = array( __( 'Инициативы', 'atjournal' ), $ledger ? $ledger : home_url( '/#iniciativy' ) );

	$about = get_page_by_path( 'o-proekte' );
	if ( $about instanceof WP_Post ) {
		$links[] = array( __( 'О проекте', 'atjournal' ), get_permalink( $about ) );
	}
	return $links;
}

/**
 * Fallback primary menu.
 *
 * @param array<string, mixed> $args Menu args.
 */
function atj_menu_fallback( $args = array() ) {
	$class = isset( $args['menu_class'] ) ? $args['menu_class'] : 'atj-menu';
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( atj_nav_links() as $link ) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $link[1] ),
			esc_html( $link[0] )
		);
	}
	echo '</ul>';
}

/**
 * Inline icon set. Static markup only.
 *
 * @param string $name Icon name.
 * @return string
 */
function atj_icon( $name ) {
	$icons = array(
		'mark'   => '<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M11 7h20.5L37 12.5V41H11V7Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M31.5 7V13H37" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M18 23h12M18 29h8" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>',
		'search' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.25" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M16 16.5 20 20.5" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
		'moon'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 14.8A8.2 8.2 0 0 1 9.2 3.8 8.2 8.2 0 1 0 20.2 14.8Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
		'sun'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3.4" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M12 3.2v2.1M12 18.7v2.1M4.8 4.8l1.5 1.5M17.7 17.7l1.5 1.5M3.2 12h2.1M18.7 12h2.1M4.8 19.2l1.5-1.5M17.7 6.3l1.5-1.5" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
		'arrow'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
		'close'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
	);
	return $icons[ $name ] ?? '';
}

/**
 * Whether a post is seeded demonstration content.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function atj_is_sample( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return false;
	}
	return '1' === (string) get_post_meta( $post_id, '_atj_sample', true );
}

/**
 * Sample badge.
 */
function atj_sample_badge() {
	if ( ! atj_is_sample() ) {
		return;
	}
	echo '<p class="atj-sample">' . esc_html__( 'Образец', 'atjournal' ) . '</p>';
}

/**
 * Reading time in minutes.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function atj_reading_minutes( $post_id = 0 ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return 1;
	}
	$text = wp_strip_all_tags( $post->post_content );
	preg_match_all( '/[\p{L}\p{N}]+/u', $text, $matches );
	$words = isset( $matches[0] ) ? count( $matches[0] ) : 0;
	return max( 1, (int) ceil( $words / 180 ) );
}

/**
 * Human type label for mixed search results.
 *
 * @param string $type Post type.
 * @return string
 */
function atj_type_label( $type = '' ) {
	$type = $type ? $type : get_post_type();
	$labels = array(
		'post'            => __( 'Журнал', 'atjournal' ),
		'page'            => __( 'Страница', 'atjournal' ),
		'atj_hero'        => __( 'Память', 'atjournal' ),
		'atj_initiative'  => __( 'Инициатива', 'atjournal' ),
	);
	return $labels[ $type ] ?? '';
}

/**
 * Category or type kicker.
 *
 * @return string
 */
function atj_kicker_text() {
	if ( 'post' === get_post_type() ) {
		$categories = get_the_category();
		if ( $categories ) {
			return $categories[0]->name;
		}
	}
	if ( 'atj_initiative' === get_post_type() ) {
		$terms = get_the_terms( get_the_ID(), 'atj_area' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			return $terms[0]->name;
		}
	}
	if ( 'atj_hero' === get_post_type() ) {
		$role = (string) get_post_meta( get_the_ID(), '_atj_role', true );
		if ( $role ) {
			return $role;
		}
	}
	return atj_type_label();
}

/**
 * Card excerpt that prefers the manual excerpt.
 *
 * @param int $words Word count.
 * @return string
 */
function atj_card_text( $words = 24 ) {
	if ( has_excerpt() ) {
		return get_the_excerpt();
	}
	if ( 'atj_hero' === get_post_type() ) {
		$role = (string) get_post_meta( get_the_ID(), '_atj_role', true );
		if ( $role ) {
			return $role;
		}
	}
	return wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ), $words, '…' );
}

/**
 * Breadcrumbs.
 */
function atj_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$crumbs = array(
		array(
			'label' => __( 'Главная', 'atjournal' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( is_singular( 'post' ) ) {
		$crumbs[] = array(
			'label' => __( 'Журнал', 'atjournal' ),
			'url'   => atj_journal_url(),
		);
		$categories = get_the_category();
		if ( $categories ) {
			$crumbs[] = array(
				'label' => $categories[0]->name,
				'url'   => get_category_link( $categories[0] ),
			);
		}
		$crumbs[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_singular( 'atj_hero' ) ) {
		$archive  = get_post_type_archive_link( 'atj_hero' );
		$crumbs[] = array( 'label' => __( 'Память', 'atjournal' ), 'url' => $archive ? $archive : '' );
		$crumbs[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_singular( 'atj_initiative' ) ) {
		$archive  = get_post_type_archive_link( 'atj_initiative' );
		$crumbs[] = array( 'label' => __( 'Инициативы', 'atjournal' ), 'url' => $archive ? $archive : '' );
		$crumbs[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_singular( 'page' ) ) {
		$crumbs[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_search() ) {
		$crumbs[] = array(
			'label' => sprintf( /* translators: %s search query */ __( 'Поиск: %s', 'atjournal' ), get_search_query() ),
			'url'   => '',
		);
	} elseif ( is_post_type_archive() || is_category() || is_tag() || is_tax() || is_author() || is_date() ) {
		$crumbs[] = array( 'label' => wp_strip_all_tags( get_the_archive_title() ), 'url' => '' );
	} elseif ( is_404() ) {
		$crumbs[] = array( 'label' => __( 'Страница не найдена', 'atjournal' ), 'url' => '' );
	}

	echo '<nav class="atj-crumbs" aria-label="' . esc_attr__( 'Навигация по сайту', 'atjournal' ) . '"><ol>';
	$last = count( $crumbs ) - 1;
	foreach ( $crumbs as $index => $crumb ) {
		echo '<li>';
		if ( $crumb['url'] && $index !== $last ) {
			printf( '<a href="%s">%s</a>', esc_url( $crumb['url'] ), esc_html( $crumb['label'] ) );
		} else {
			printf( '<span aria-current="page">%s</span>', esc_html( $crumb['label'] ) );
		}
		echo '</li>';
	}
	echo '</ol></nav>';
}

/**
 * Archive or page heading block.
 *
 * @param string $kicker Kicker.
 * @param string $title Title. Already escaped if $title_html.
 * @param string $text Lead.
 * @param bool   $title_is_html Whether the title contains trusted markup from core archive titles.
 */
function atj_page_header( $kicker, $title, $text = '', $title_is_html = false ) {
	?>
	<header class="atj-pagehead">
		<div class="atj-shell">
			<?php atj_breadcrumbs(); ?>
			<?php if ( $kicker ) : ?>
				<p class="atj-kicker"><?php echo esc_html( $kicker ); ?></p>
			<?php endif; ?>
			<h1><?php echo $title_is_html ? wp_kses_post( $title ) : esc_html( $title ); ?></h1>
			<?php if ( $text ) : ?>
				<p class="atj-pagehead__lead"><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>
		</div>
	</header>
	<?php
}

/**
 * Visible page content, ignoring editor comments.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function atj_has_visible_content( $post_id = 0 ) {
	$raw = (string) get_post_field( 'post_content', $post_id ? $post_id : get_the_ID() );
	$raw = preg_replace( '/<!--(.|\s)*?-->/', '', $raw );
	return '' !== trim( wp_strip_all_tags( (string) $raw ) );
}

/**
 * Section heading used on the homepage.
 *
 * @param string $number Number.
 * @param string $title Title.
 * @param string $text Text.
 * @param string $url Link URL.
 * @param string $label Link label.
 */
function atj_section_head( $number, $title, $text = '', $url = '', $label = '' ) {
	?>
	<div class="atj-section__head">
		<div>
			<p class="atj-num"><?php echo esc_html( $number ); ?></p>
			<h2><?php echo esc_html( $title ); ?></h2>
		</div>
		<?php if ( $text ) : ?>
			<p><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
		<?php if ( $url && $label ) : ?>
			<a class="atj-textlink" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?> <span aria-hidden="true">→</span></a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Monogram for a card without a portrait.
 *
 * @param string $title Title.
 * @return string
 */
function atj_monogram( $title ) {
	$title = trim( wp_strip_all_tags( $title ) );
	if ( '' === $title ) {
		return '·';
	}
	if ( function_exists( 'mb_substr' ) ) {
		return mb_strtoupper( mb_substr( $title, 0, 1 ) );
	}
	return strtoupper( substr( $title, 0, 1 ) );
}

/**
 * Related journal posts.
 *
 * @param int $post_id Current post.
 * @param int $count How many.
 * @return WP_Query
 */
function atj_related_query( $post_id, $count = 3 ) {
	$categories = wp_get_post_categories( $post_id );
	$args       = array(
		'post_type'           => 'post',
		'posts_per_page'      => $count,
		'post__not_in'        => array( (int) $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( $categories ) {
		$args['category__in'] = $categories;
	}
	return new WP_Query( $args );
}

/**
 * The lead story: a sticky post, otherwise the latest one.
 *
 * @return WP_Query
 */
function atj_lead_query() {
	$sticky = array_values( array_filter( array_map( 'intval', (array) get_option( 'sticky_posts' ) ) ) );
	if ( $sticky ) {
		$query = new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => 1,
				'post__in'            => $sticky,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		if ( $query->have_posts() ) {
			return $query;
		}
	}

	return new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => 1,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
}

/**
 * JSON-LD for singular views.
 */
function atj_schema() {
	if ( ! is_singular() ) {
		return;
	}

	$post_id = get_queried_object_id();
	if ( atj_is_sample( $post_id ) ) {
		return;
	}

	$data = array(
		'@context' => 'https://schema.org',
	);

	if ( is_singular( 'post' ) ) {
		$data['@type']         = 'NewsArticle';
		$data['headline']      = get_the_title( $post_id );
		$data['datePublished'] = get_the_date( 'c', $post_id );
		$data['dateModified']  = get_the_modified_date( 'c', $post_id );
		$data['description']   = wp_strip_all_tags( get_the_excerpt( $post_id ) );
		$data['author']        = array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ),
		);
	} elseif ( is_singular( 'atj_hero' ) ) {
		$data['@type']       = 'Person';
		$data['name']        = get_the_title( $post_id );
		$data['description'] = wp_strip_all_tags( get_the_excerpt( $post_id ) );
		$role                = (string) get_post_meta( $post_id, '_atj_role', true );
		if ( $role ) {
			$data['jobTitle'] = $role;
		}
	} elseif ( is_singular( 'atj_initiative' ) ) {
		$data['@type']       = 'Article';
		$data['headline']    = get_the_title( $post_id );
		$data['description'] = wp_strip_all_tags( get_the_excerpt( $post_id ) );
	} else {
		return;
	}

	$data['mainEntityOfPage'] = get_permalink( $post_id );
	if ( has_post_thumbnail( $post_id ) ) {
		$image = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'full' );
		if ( $image ) {
			$data['image'] = $image;
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode(
		$data,
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	) . '</script>' . "\n";
}
add_action( 'wp_head', 'atj_schema', 20 );

/**
 * A Russian date, independent of the site locale.
 *
 * @param int $post_id Post ID, or 0 for today.
 * @return string
 */
function atj_human_date( $post_id = 0 ) {
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
	$timestamp = $post_id ? get_post_timestamp( $post_id ) : current_time( 'timestamp' );
	if ( ! $timestamp ) {
		return '';
	}
	$month = (int) wp_date( 'n', $timestamp );
	return wp_date( 'j', $timestamp ) . ' ' . ( $months[ $month ] ?? '' ) . ' ' . wp_date( 'Y', $timestamp );
}

/**
 * Russian comment count.
 *
 * @param int $count Count.
 * @return string
 */
function atj_comments_label( $count ) {
	$count = abs( (int) $count );
	$n10   = $count % 10;
	$n100  = $count % 100;
	if ( 1 === $n10 && 11 !== $n100 ) {
		return sprintf( '%d комментарий', $count );
	}
	if ( $n10 >= 2 && $n10 <= 4 && ( $n100 < 12 || $n100 > 14 ) ) {
		return sprintf( '%d комментария', $count );
	}
	return sprintf( '%d комментариев', $count );
}

/**
 * Posts pagination with editorial labels.
 */
function atj_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => __( '← Назад', 'atjournal' ),
			'next_text' => __( 'Дальше →', 'atjournal' ),
		)
	);
}
