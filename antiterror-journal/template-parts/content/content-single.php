<?php
/**
 * Single article body.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'atj-article' ); ?>>
	<header class="atj-article__header">
		<?php atj_breadcrumbs(); ?>
		<?php atj_posted_on(); ?>
		<h1 class="atj-article__title"><?php the_title(); ?></h1>
		<?php if ( has_excerpt() ) : ?>
			<p class="atj-article__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="atj-article__hero"><?php the_post_thumbnail( 'atj-featured' ); ?></figure>
	<?php endif; ?>

	<div class="atj-article__progress" data-progress aria-hidden="true"><span></span></div>

	<div class="atj-article__grid">
		<div class="atj-article__content entry-content">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>
		<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
			<aside class="atj-sidebar" aria-label="<?php esc_attr_e( 'Боковая колонка', 'antiterror-journal' ); ?>">
				<?php dynamic_sidebar( 'sidebar-1' ); ?>
			</aside>
		<?php endif; ?>
	</div>
</article>
