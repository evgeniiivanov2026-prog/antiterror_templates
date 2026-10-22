<?php
/**
 * Singular views, archives, search and the 404.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Journal essay.
 *
 * @return string
 */
function rubezh_render_article() {
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return rubezh_render_not_found();
	}
	$categories = get_the_category( $post_id );
	$crumbs     = array(
		array(
			'label' => __( 'Журнал', 'rubezh' ),
			'url'   => rubezh_journal_url(),
		),
	);
	if ( ! empty( $categories ) ) {
		$crumbs[] = array(
			'label' => $categories[0]->name,
			'url'   => get_category_link( $categories[0] ),
		);
	}
	$crumbs[] = array( 'label' => get_the_title( $post_id ) );
	ob_start();
	?>
	<div class="rz-progress" data-reading-progress hidden><span></span></div>
	<article class="rz-read" itemscope itemtype="https://schema.org/Article">
		<?php rubezh_breadcrumbs( $crumbs ); ?>
		<p class="rz-kicker"><?php echo esc_html( rubezh_kicker_label( $post_id ) ); ?></p>
		<h1 itemprop="headline"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
		<?php if ( has_excerpt( $post_id ) ) : ?>
			<p class="rz-standfirst" itemprop="description"><?php echo esc_html( get_the_excerpt( $post_id ) ); ?></p>
		<?php endif; ?>
		<p class="rz-meta">
			<?php rubezh_sample_chip( $post_id ); ?>
			<span itemprop="author"><?php echo esc_html( get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ) ); ?></span>
			<time itemprop="datePublished" datetime="<?php echo esc_attr( rubezh_date_iso( $post_id ) ); ?>"><?php echo esc_html( rubezh_date( $post_id ) ); ?></time>
			<span><?php echo esc_html( sprintf( _n( '%d мин', '%d мин', rubezh_reading_minutes( $post_id ), 'rubezh' ), rubezh_reading_minutes( $post_id ) ) ); ?></span>
		</p>
		<?php if ( has_post_thumbnail( $post_id ) ) : ?>
			<figure class="rz-figure"><?php echo get_the_post_thumbnail( $post_id, 'rubezh-lead' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figure>
		<?php endif; ?>
		<?php edit_post_link( __( 'Править материал', 'rubezh' ), '<p class="rz-edit">', '</p>', $post_id ); ?>
	</article>
	<div class="rz-prose rz-drop" itemprop="articleBody">
		<?php the_content(); ?>
	</div>
	<p class="rz-end"><?php esc_html_e( 'Конец материала', 'rubezh' ); ?></p>
	<?php
	rubezh_related( $post_id );
	if ( comments_open( $post_id ) || get_comments_number( $post_id ) ) {
		comments_template();
	}
	return (string) ob_get_clean();
}

/**
 * Three related essays from the same category.
 *
 * @param int $post_id Current post.
 */
function rubezh_related( $post_id ) {
	$categories = wp_get_post_categories( $post_id );
	$args       = array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post__not_in'   => array( $post_id ),
		'no_found_rows'  => true,
	);
	if ( $categories ) {
		$args['category__in'] = $categories;
	}
	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		return;
	}
	echo '<section class="rz-related" aria-labelledby="rz-related-title"><h2 id="rz-related-title">' . esc_html__( 'Рядом в номере', 'rubezh' ) . '</h2><div class="rz-stories">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$id = get_the_ID();
		echo '<article class="rz-story">';
		echo '<a class="rz-card-media" href="' . esc_url( get_permalink( $id ) ) . '" tabindex="-1" aria-hidden="true">' . rubezh_media_html( $id, 'rubezh-card' ) . '</a>';
		echo '<p class="rz-meta">' . esc_html( rubezh_kicker_label( $id ) ) . '</p>';
		echo '<h3><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3>';
		echo '</article>';
	}
	wp_reset_postdata();
	echo '</div></section>';
}

/**
 * Hero profile.
 *
 * @return string
 */
function rubezh_render_hero_story() {
	$post_id = get_the_ID();
	if ( ! $post_id || 'rubezh_hero' !== get_post_type( $post_id ) ) {
		return rubezh_render_not_found();
	}
	$archive = get_post_type_archive_link( 'rubezh_hero' );
	ob_start();
	?>
	<article class="rz-monument">
		<?php
		rubezh_breadcrumbs(
			array(
				array(
					'label' => __( 'Память', 'rubezh' ),
					'url'   => $archive ? $archive : home_url( '/pamyat/' ),
				),
				array( 'label' => get_the_title( $post_id ) ),
			)
		);
		?>
		<p class="rz-kicker"><?php echo esc_html( (string) get_post_meta( $post_id, '_rubezh_role', true ) ); ?></p>
		<h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
		<p class="rz-monument__meta">
			<?php rubezh_sample_chip( $post_id ); ?>
			<span><?php echo esc_html( (string) get_post_meta( $post_id, '_rubezh_years', true ) ); ?></span>
			<span><?php echo esc_html( (string) get_post_meta( $post_id, '_rubezh_place', true ) ); ?></span>
		</p>
		<?php $epitaph = (string) get_post_meta( $post_id, '_rubezh_epitaph', true ); ?>
		<?php if ( $epitaph ) : ?>
			<p class="rz-epitaph"><?php echo esc_html( $epitaph ); ?></p>
		<?php endif; ?>
		<?php if ( has_post_thumbnail( $post_id ) ) : ?>
			<figure class="rz-figure"><?php echo get_the_post_thumbnail( $post_id, 'rubezh-lead' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figure>
		<?php endif; ?>
		<div class="rz-prose"><?php the_content(); ?></div>
		<?php edit_post_link( __( 'Править профиль', 'rubezh' ), '<p class="rz-edit">', '</p>', $post_id ); ?>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * Single initiative with a sticky ballot.
 *
 * @return string
 */
function rubezh_render_initiative_story() {
	$post_id = get_the_ID();
	if ( ! $post_id || 'rubezh_initiative' !== get_post_type( $post_id ) ) {
		return rubezh_render_not_found();
	}
	$archive = get_post_type_archive_link( 'rubezh_initiative' );
	$status  = rubezh_initiative_status( $post_id );
	ob_start();
	?>
	<article class="rz-case">
		<div>
			<?php
			rubezh_breadcrumbs(
				array(
					array(
						'label' => __( 'Инициативы', 'rubezh' ),
						'url'   => $archive ? $archive : home_url( '/iniciativy/' ),
					),
					array( 'label' => get_the_title( $post_id ) ),
				)
			);
			?>
			<p class="rz-kicker"><?php echo esc_html( rubezh_track_label( $post_id ) ); ?></p>
			<h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
			<p class="rz-status rz-status--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( rubezh_status_label( $status ) ); ?></p>
			<?php rubezh_sample_chip( $post_id ); ?>
			<?php if ( has_excerpt( $post_id ) ) : ?>
				<p class="rz-standfirst"><?php echo esc_html( get_the_excerpt( $post_id ) ); ?></p>
			<?php endif; ?>
			<div class="rz-prose"><?php the_content(); ?></div>
			<?php edit_post_link( __( 'Править инициативу', 'rubezh' ), '<p class="rz-edit">', '</p>', $post_id ); ?>
		</div>
		<aside class="rz-case__vote" aria-label="<?php esc_attr_e( 'Голосование', 'rubezh' ); ?>">
			<p class="rz-kicker"><?php esc_html_e( 'Ваш голос', 'rubezh' ); ?></p>
			<?php echo rubezh_vote_markup( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</aside>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * Ordinary page.
 *
 * @return string
 */
function rubezh_render_page() {
	$post_id = get_the_ID();
	ob_start();
	?>
	<article class="rz-page">
		<?php rubezh_breadcrumbs( array( array( 'label' => get_the_title( $post_id ) ) ) ); ?>
		<h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
		<?php if ( has_post_thumbnail( $post_id ) ) : ?>
			<figure class="rz-figure"><?php echo get_the_post_thumbnail( $post_id, 'rubezh-lead' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figure>
		<?php endif; ?>
	</article>
	<div class="rz-prose"><?php the_content(); ?></div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Journal index and term archives.
 *
 * @return string
 */
function rubezh_render_journal_archive() {
	$paged = rubezh_paged();
	$title = __( 'Журнал', 'rubezh' );
	$intro = __( 'Хроника, память и право — материалы редакции.', 'rubezh' );
	$args  = array(
		'post_type'      => 'post',
		'paged'          => $paged,
		'posts_per_page' => (int) get_option( 'posts_per_page' ),
	);
	if ( is_category() ) {
		$title         = single_cat_title( '', false );
		$args['cat']   = get_queried_object_id();
		$intro         = wp_strip_all_tags( (string) category_description() );
	} elseif ( is_tag() ) {
		$title            = single_tag_title( '', false );
		$args['tag_id']   = get_queried_object_id();
		$intro            = wp_strip_all_tags( (string) tag_description() );
	} elseif ( is_author() ) {
		$title            = get_the_author();
		$args['author']   = get_queried_object_id();
	} elseif ( is_date() ) {
		$title              = wp_strip_all_tags( get_the_archive_title() );
		$args['year']       = get_query_var( 'year' );
		$args['monthnum']   = get_query_var( 'monthnum' );
		$args['day']        = get_query_var( 'day' );
	}
	$query = new WP_Query( $args );
	ob_start();
	?>
	<header class="rz-archive-head">
		<p class="rz-kicker"><?php esc_html_e( 'Журнал', 'rubezh' ); ?></p>
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro ) : ?>
			<p class="rz-standfirst"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
	</header>
	<div class="rz-archive-list">
		<?php if ( $query->have_posts() ) : ?>
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$id = get_the_ID();
				?>
				<a class="rz-archive-row" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
					<time datetime="<?php echo esc_attr( rubezh_date_iso( $id ) ); ?>"><?php echo esc_html( rubezh_date( $id ) ); ?></time>
					<strong><?php echo esc_html( get_the_title( $id ) ); ?></strong>
					<span><?php echo esc_html( rubezh_kicker_label( $id ) ); ?></span>
				</a>
				<?php
			endwhile;
			wp_reset_postdata();
			rubezh_pagination( $query, $paged );
			?>
		<?php else : ?>
			<p class="rz-empty"><?php esc_html_e( 'В этой полосе пока пусто.', 'rubezh' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Search results across the journal, memory and the fund.
 *
 * @return string
 */
function rubezh_render_search() {
	global $wp_query;
	ob_start();
	?>
	<header class="rz-search-head">
		<p class="rz-kicker"><?php esc_html_e( 'Поиск', 'rubezh' ); ?></p>
		<h1><?php echo esc_html( sprintf( /* translators: search query */ __( '«%s»', 'rubezh' ), get_search_query() ) ); ?></h1>
		<p class="rz-standfirst"><?php echo esc_html( sprintf( /* translators: result count */ _n( '%d совпадение', '%d совпадений', (int) $wp_query->found_posts, 'rubezh' ), (int) $wp_query->found_posts ) ); ?></p>
	</header>
	<div class="rz-archive-list">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				$id    = get_the_ID();
				$type  = get_post_type( $id );
				$names = array(
					'post'               => __( 'Журнал', 'rubezh' ),
					'rubezh_hero'        => __( 'Память', 'rubezh' ),
					'rubezh_initiative'  => __( 'Инициатива', 'rubezh' ),
					'page'               => __( 'Страница', 'rubezh' ),
				);
				$label = isset( $names[ $type ] ) ? $names[ $type ] : $type;
				?>
				<a class="rz-archive-row" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
					<span><?php echo esc_html( $label ); ?></span>
					<strong><?php echo esc_html( get_the_title( $id ) ); ?></strong>
					<span><?php echo esc_html( rubezh_date( $id ) ); ?></span>
				</a>
				<?php
			endwhile;
			rubezh_pagination( $wp_query, rubezh_paged() );
			?>
		<?php else : ?>
			<p class="rz-empty"><?php esc_html_e( 'Ничего не нашлось. Попробуйте имя, тему номера или слово из инициативы.', 'rubezh' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Missing page.
 *
 * @return string
 */
function rubezh_render_not_found() {
	ob_start();
	?>
	<section class="rz-lost">
		<p class="rz-kicker">404</p>
		<h1><?php esc_html_e( 'Этой страницы нет в выпуске.', 'rubezh' ); ?></h1>
		<p><?php esc_html_e( 'Ссылка устарела или адрес набран с ошибкой. Вернитесь на полосу, в память или в фонд инициатив.', 'rubezh' ); ?></p>
		<div class="rz-lost__links">
			<a class="rz-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'На обложку', 'rubezh' ); ?></a>
			<a class="rz-button-text" href="<?php echo esc_url( rubezh_journal_url() ); ?>"><?php esc_html_e( 'Журнал', 'rubezh' ); ?></a>
			<a class="rz-button-text" href="<?php echo esc_url( get_post_type_archive_link( 'rubezh_hero' ) ); ?>"><?php esc_html_e( 'Память', 'rubezh' ); ?></a>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Fallback router for index.html.
 *
 * @return string
 */
function rubezh_render_route() {
	if ( is_404() ) {
		return rubezh_render_not_found();
	}
	if ( is_search() ) {
		return rubezh_render_search();
	}
	if ( is_singular( 'rubezh_hero' ) ) {
		return rubezh_render_hero_story();
	}
	if ( is_singular( 'rubezh_initiative' ) ) {
		return rubezh_render_initiative_story();
	}
	if ( is_singular( 'post' ) ) {
		return rubezh_render_article();
	}
	if ( is_singular( 'page' ) ) {
		return rubezh_render_page();
	}
	if ( is_post_type_archive( 'rubezh_hero' ) ) {
		return rubezh_render_memorial( array( 'count' => 12, 'archive' => true ) );
	}
	if ( is_post_type_archive( 'rubezh_initiative' ) ) {
		return rubezh_render_initiatives( array( 'count' => 12, 'archive' => true ) );
	}
	return rubezh_render_journal_archive();
}
