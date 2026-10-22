<?php
/**
 * Архив рубрик и дат.
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
		<p class="section-number"><?php esc_html_e( 'Рубрика', 'rubezh' ); ?></p>
		<h1><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<p class="archive-head__lead">', '</p>' ); ?>
	</header>
	<?php if ( have_posts() ) : ?>
		<div class="journal-trio journal-trio--archive">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/cards/post', null, array( 'size' => 'card' ) );
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
