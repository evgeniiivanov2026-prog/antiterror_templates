<?php
/**
 * 404.
 *
 * @package Aegis
 */

get_header();
?>
<main id="primary" class="site-main interior">
	<div class="shell archive-head">
		<p class="section-kicker"><?php esc_html_e( '404', 'aegis' ); ?></p>
		<h1><?php esc_html_e( 'Страница не найдена', 'aegis' ); ?></h1>
		<p class="archive-head__lead"><?php esc_html_e( 'Материал снят, перенесён или ещё не опубликован. Вернитесь в журнал, зал памяти или бюллетень инициатив.', 'aegis' ); ?></p>
		<div class="opening__actions">
			<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'На главную', 'aegis' ); ?></a>
			<a class="btn btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'aegis_hero' ) ); ?>"><?php esc_html_e( 'Зал памяти', 'aegis' ); ?></a>
		</div>
	</div>
</main>
<?php
get_footer();
