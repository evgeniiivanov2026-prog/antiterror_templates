<?php
/**
 * Результаты поиска.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main id="primary" class="site-main shell interior">
	<header class="archive-head reveal">
		<p class="section-number"><?php esc_html_e( 'Поиск', 'rubezh' ); ?></p>
		<h1>
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( 'Результаты: %s', 'rubezh' ),
				'<em>' . esc_html( get_search_query() ) . '</em>'
			);
			?>
		</h1>
	</header>
	<?php if ( have_posts() ) : ?>
		<div class="journal-trio journal-trio--archive">
			<?php
			while ( have_posts() ) :
				the_post();
				if ( 'hero' === get_post_type() ) {
					get_template_part( 'template-parts/cards/hero' );
				} elseif ( 'initiative' === get_post_type() ) {
					get_template_part( 'template-parts/cards/initiative' );
				} else {
					get_template_part( 'template-parts/cards/post', null, array( 'size' => 'card' ) );
				}
			endwhile;
			?>
		</div>
		<div class="pagination-box"><?php the_posts_pagination(); ?></div>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content/none' ); ?>
	<?php endif; ?>
</main>
<?php
get_footer();
