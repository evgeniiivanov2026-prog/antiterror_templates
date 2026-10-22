<?php
/**
 * Template Name: Блоковый холст
 * Template Post Type: page
 *
 * A wide canvas for patterns and shortcodes.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="content" class="atj-main atj-main--canvas">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'atj-canvas entry-content' ); ?>>
			<div class="atj-shell atj-canvas__head">
				<?php atj_breadcrumbs(); ?>
				<h1><?php the_title(); ?></h1>
			</div>
			<?php the_content(); ?>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
