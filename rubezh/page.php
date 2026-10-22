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
<main id="main" class="rz-main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'rz-shell rz-essay' ); ?>>
			<header class="rz-page-head">
				<?php rubezh_breadcrumbs(); ?>
				<h1><?php the_title(); ?></h1>
			</header>
			<div class="rz-entry">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before' => '<nav class="rz-pager">',
						'after'  => '</nav>',
					)
				);
				?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
