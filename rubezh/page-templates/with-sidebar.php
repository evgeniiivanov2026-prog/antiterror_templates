<?php
/**
 * Template Name: Страница с колонкой
 * Template Post Type: page
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="rz-main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<div class="rz-shell rz-layout">
			<article <?php post_class( 'rz-entry' ); ?>>
				<header class="rz-page-head">
					<?php rubezh_breadcrumbs(); ?>
					<h1><?php the_title(); ?></h1>
				</header>
				<?php the_content(); ?>
			</article>
			<?php get_sidebar(); ?>
		</div>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
