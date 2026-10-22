<?php
/**
 * Journal article.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main interior">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'article' ); ?>>
			<header class="article__header shell">
				<?php aegis_breadcrumbs(); ?>
				<?php aegis_posted_on(); ?>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="article__standfirst"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="article__hero">
					<?php the_post_thumbnail( 'aegis-featured' ); ?>
					<?php if ( get_the_post_thumbnail_caption() ) : ?>
						<figcaption class="shell"><?php echo esc_html( get_the_post_thumbnail_caption() ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>
			<div class="article__layout shell">
				<div class="entry-content">
					<?php the_content(); ?>
					<?php wp_link_pages(); ?>
				</div>
				<aside class="article__aside" aria-label="<?php esc_attr_e( 'Колонка статьи', 'aegis' ); ?>">
					<div class="article__share">
						<p><?php esc_html_e( 'Автор', 'aegis' ); ?></p>
						<strong><?php the_author(); ?></strong>
					</div>
					<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
						<?php dynamic_sidebar( 'sidebar-1' ); ?>
					<?php endif; ?>
				</aside>
			</div>
			<?php if ( has_tag() ) : ?>
				<div class="shell article__tags"><?php the_tags( '', '' ); ?></div>
			<?php endif; ?>
		</article>
		<div class="shell">
			<?php aegis_related_posts(); ?>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
