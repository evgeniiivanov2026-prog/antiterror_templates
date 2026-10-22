<?php
/**
 * Default page.
 *
 * @package Aegis
 */

get_header();
?>
<main id="primary" class="site-main interior">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'article' ); ?>>
			<header class="article__header shell">
				<?php aegis_breadcrumbs(); ?>
				<h1><?php the_title(); ?></h1>
			</header>
			<div class="shell">
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
