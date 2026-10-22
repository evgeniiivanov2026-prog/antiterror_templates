<?php
/**
 * Материал журнала.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main id="primary" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'article-wrap' ); ?>>
			<header class="article-hero<?php echo has_post_thumbnail() ? ' has-media' : ''; ?>">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="article-hero__media"><?php the_post_thumbnail( 'rubezh-cover' ); ?></div>
				<?php endif; ?>
				<div class="shell article-hero__copy">
					<?php get_template_part( 'template-parts/content/breadcrumbs' ); ?>
					<?php get_template_part( 'template-parts/content/entry-meta' ); ?>
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="article-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</div>
			</header>
			<div class="shell article-layout">
				<div class="entry-content">
					<?php the_content(); ?>
					<?php
					wp_link_pages( array(
						'before' => '<nav class="page-links">' . esc_html__( 'Страницы:', 'rubezh' ),
						'after'  => '</nav>',
					) );
					?>
					<?php the_tags( '<p class="tag-list">', ' ', '</p>' ); ?>
				</div>
				<?php get_sidebar(); ?>
			</div>
			<div class="shell">
				<?php get_template_part( 'template-parts/content/author' ); ?>
				<?php get_template_part( 'template-parts/content/related' ); ?>
				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
