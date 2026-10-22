<?php
/**
 * Страница 404.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main id="primary" class="site-main shell interior error-404">
	<p class="section-number"><?php esc_html_e( '404', 'rubezh' ); ?></p>
	<h1><?php esc_html_e( 'Страница не найдена', 'rubezh' ); ?></h1>
	<p><?php esc_html_e( 'Материал снят, переименован или ещё не опубликован. Вернитесь в выпуск, к стене памяти или к фонду инициатив.', 'rubezh' ); ?></p>
	<div class="cover__actions">
		<a class="btn btn--burgundy" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'На обложку', 'rubezh' ); ?></a>
		<a class="btn btn--ghost" href="<?php echo esc_url( rubezh_archive_link( 'hero' ) ); ?>"><?php esc_html_e( 'Память', 'rubezh' ); ?></a>
		<a class="btn btn--ghost" href="<?php echo esc_url( rubezh_archive_link( 'initiative' ) ); ?>"><?php esc_html_e( 'Фонд', 'rubezh' ); ?></a>
	</div>
	<?php get_search_form(); ?>
</main>
<?php
get_footer();
