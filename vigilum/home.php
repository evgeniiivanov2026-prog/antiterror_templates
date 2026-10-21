<?php
/**
 * Posts page — journal.
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
			<p class="section-number">01 / <?php esc_html_e( 'Выпуск', 'vigilum' ); ?></p>
			<h1><?php esc_html_e( 'Журнал', 'vigilum' ); ?></h1>
			<p class="archive-desc"><?php esc_html_e( 'Аналитика, хроника и разборы. Только проверяемые сведения.', 'vigilum' ); ?></p>
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
			<div class="empty-state"><p><?php esc_html_e( 'Журнал ещё не наполнен.', 'vigilum' ); ?></p></div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
