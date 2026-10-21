<?php
/**
 * Default page.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main interior">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'page-article' ); ?>>
			<div class="shell">
				<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
				<header class="archive-head">
					<h1><?php the_title(); ?></h1>
				</header>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
