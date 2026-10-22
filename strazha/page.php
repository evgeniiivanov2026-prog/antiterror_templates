<?php
/**
 * Страница.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="sheet">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'page-sheet shell' ); ?>>
			<?php strazha_breadcrumbs(); ?>
			<h1><?php the_title(); ?></h1>
			<div class="entry">
				<div class="entry__content">
					<?php the_content(); ?>
				</div>
				<?php
				wp_link_pages(
					array(
						'before' => '<nav class="pagesplit">',
						'after'  => '</nav>',
					)
				);
				?>
			</div>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
