<?php
/**
 * Blog index.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main interior">
	<div class="shell">
		<header class="archive-head">
			<p class="section-kicker"><?php esc_html_e( 'Журнал', 'aegis' ); ?></p>
			<h1><?php echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) ?: __( 'Все материалы', 'aegis' ) ); ?></h1>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="story-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card', 'post' );
				endwhile;
				?>
			</div>
			<div class="pagination-box"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'Опубликуйте первую статью журнала.', 'aegis' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
