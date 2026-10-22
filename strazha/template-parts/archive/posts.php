<?php
/**
 * Лента записей: журнал, рубрики, поиск.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_search() ) {
	$title = sprintf(
		/* translators: %s: search query. */
		__( 'Поиск: %s', 'strazha' ),
		get_search_query()
	);
	$desc = '';
} elseif ( is_home() ) {
	$title = __( 'Журнал', 'strazha' );
	$desc  = __( 'Все материалы редакции.', 'strazha' );
} elseif ( is_category() || is_tag() || is_tax() ) {
	$title = single_term_title( '', false );
	$desc  = term_description();
} elseif ( is_author() ) {
	$title = get_the_author();
	$desc  = get_the_author_meta( 'description' );
} elseif ( is_post_type_archive() ) {
	$title = post_type_archive_title( '', false );
	$desc  = '';
} else {
	$title = wp_strip_all_tags( get_the_archive_title() );
	$desc  = get_the_archive_description();
}
?>
<main id="main" class="sheet">
	<header class="pagehead shell">
		<?php strazha_breadcrumbs(); ?>
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( $desc ) : ?>
			<div class="pagehead__lead"><?php echo wp_kses_post( wpautop( $desc ) ); ?></div>
		<?php endif; ?>
	</header>
	<div class="layout shell">
		<div>
			<?php if ( have_posts() ) : ?>
				<div class="cards" data-cards>
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content/card' );
					endwhile;
					?>
				</div>
				<?php strazha_feed_more(); ?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content/none' ); ?>
			<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</main>
