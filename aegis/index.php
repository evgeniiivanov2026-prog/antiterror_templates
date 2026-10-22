<?php
/**
 * Fallback index.
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
			<h1><?php echo wp_kses_post( get_the_archive_title() ?: __( 'Материалы', 'aegis' ) ); ?></h1>
			<?php the_archive_description( '<div class="archive-head__lead">', '</div>' ); ?>
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
			<p class="empty-note"><?php esc_html_e( 'Пока нет опубликованных материалов.', 'aegis' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
