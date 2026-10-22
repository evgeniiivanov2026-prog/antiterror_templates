<?php
/**
 * Стена памяти.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main id="primary" class="site-main section--chapel chapel-page">
	<div class="shell interior">
		<header class="archive-head archive-head--light reveal">
			<p class="section-number section-number--bronze"><?php esc_html_e( '02 / Память героев', 'rubezh' ); ?></p>
			<h1><?php esc_html_e( 'Стена памяти', 'rubezh' ); ?></h1>
			<p class="archive-head__lead"><?php esc_html_e( 'Имена тех, кто встал между угрозой и людьми. Публикуйте проверяемые сведения и уважайте семьи.', 'rubezh' ); ?></p>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="memorial-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/cards/hero' );
				endwhile;
				?>
			</div>
			<div class="pagination-box pagination-box--light"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<p class="empty-line empty-line--light"><?php esc_html_e( 'Карточки героев появятся здесь.', 'rubezh' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
