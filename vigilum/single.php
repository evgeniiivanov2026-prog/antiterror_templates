<?php
/**
 * Single journal article.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main interior article-layout">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'article' ); ?>>
			<header class="article-hero">
				<div class="shell">
					<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
					<p class="kicker">
						<?php
						$cats = get_the_category();
						echo $cats ? esc_html( $cats[0]->name ) : esc_html__( 'Журнал', 'vigilum' );
						?>
					</p>
					<h1><?php the_title(); ?></h1>
					<p class="article-meta">
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></time>
						<span><?php echo esc_html( vigilum_reading_time() ); ?></span>
						<span><?php the_author(); ?></span>
					</p>
				</div>
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="article-cover">
						<?php the_post_thumbnail( 'vigilum-hero' ); ?>
						<?php if ( get_the_post_thumbnail_caption() ) : ?>
							<figcaption><?php echo esc_html( get_the_post_thumbnail_caption() ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>
			</header>
			<div class="shell article-grid">
				<div class="entry-content">
					<?php the_content(); ?>
					<?php wp_link_pages(); ?>
				</div>
				<aside class="article-aside">
					<div class="share">
						<p><?php esc_html_e( 'Поделиться', 'vigilum' ); ?></p>
						<?php $share = vigilum_share_links(); ?>
						<a href="<?php echo esc_url( $share['telegram'] ); ?>" rel="noopener noreferrer"><?php esc_html_e( 'Telegram', 'vigilum' ); ?></a>
						<a href="<?php echo esc_url( $share['vk'] ); ?>" rel="noopener noreferrer"><?php esc_html_e( 'ВКонтакте', 'vigilum' ); ?></a>
						<button type="button" data-copy="<?php echo esc_attr( $share['copy'] ); ?>"><?php esc_html_e( 'Копировать ссылку', 'vigilum' ); ?></button>
					</div>
					<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
						<div class="widgets"><?php dynamic_sidebar( 'sidebar-1' ); ?></div>
					<?php endif; ?>
				</aside>
			</div>
			<div class="shell">
				<?php get_template_part( 'template-parts/related' ); ?>
				<?php if ( comments_open() || get_comments_number() ) : ?>
					<div class="comments-wrap"><?php comments_template(); ?></div>
				<?php endif; ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
