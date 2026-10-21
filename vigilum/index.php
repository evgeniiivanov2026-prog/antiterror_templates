<?php
/**
 * Fallback archive / blog index.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main interior">
	<div class="shell">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<header class="archive-head">
			<p class="section-number"><?php esc_html_e( 'Журнал', 'vigilum' ); ?></p>
			<h1><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
			<?php the_archive_description( '<p class="archive-desc">', '</p>' ); ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="journal-grid journal-grid--3">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/card', 'post', array( 'variant' => 'standard' ) );
				}
				?>
			</div>
			<?php vigilum_pagination(); ?>
		<?php else : ?>
			<div class="empty-state">
				<p><?php esc_html_e( 'Материалов пока нет.', 'vigilum' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
