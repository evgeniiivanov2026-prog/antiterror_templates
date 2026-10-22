<?php
/**
 * Материал летописи.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="rz-main">
	<div class="rz-progress" data-progress aria-hidden="true"></div>
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class(); ?>>
			<header class="rz-shell rz-entry-head">
				<?php rubezh_breadcrumbs(); ?>
				<p class="rz-kicker"><?php rubezh_category_label(); ?></p>
				<h1><?php the_title(); ?></h1>
				<?php rubezh_entry_meta(); ?>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="rz-shell rz-entry-figure">
					<?php the_post_thumbnail( 'rubezh-lead', array( 'loading' => 'eager' ) ); ?>
				</figure>
			<?php endif; ?>
			<div class="rz-shell rz-layout">
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
					<?php get_template_part( 'template-parts/content/author' ); ?>
				</div>
				<?php get_sidebar(); ?>
			</div>
			<?php get_template_part( 'template-parts/content/related' ); ?>
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
