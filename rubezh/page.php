<?php
/**
 * Обычная страница.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main id="primary" class="site-main shell interior">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'page-sheet' ); ?>>
			<?php get_template_part( 'template-parts/content/breadcrumbs' ); ?>
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
