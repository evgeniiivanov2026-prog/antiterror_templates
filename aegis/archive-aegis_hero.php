<?php
/**
 * Memory hall archive.
 *
 * @package Aegis
 */

get_header();
?>
<main id="primary" class="site-main interior interior--memory">
	<div class="shell">
		<header class="archive-head">
			<?php aegis_breadcrumbs(); ?>
			<p class="section-kicker section-kicker--bronze"><?php esc_html_e( 'Память героев', 'aegis' ); ?></p>
			<h1><?php esc_html_e( 'Зал имён', 'aegis' ); ?></h1>
			<p class="archive-head__lead"><?php esc_html_e( 'Сдержанные карточки памяти. Публикуйте только согласованные с семьями и проверяемые сведения.', 'aegis' ); ?></p>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="memory-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card', 'hero' );
				endwhile;
				?>
			</div>
			<div class="pagination-box"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'Зал памяти ещё не заполнен.', 'aegis' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
