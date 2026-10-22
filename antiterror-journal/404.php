<?php
/**
 * 404.
 *
 * @package AntiterrorJournal
 */

get_header();
?>
<main id="primary" class="atj-page atj-shell">
	<section class="atj-empty atj-empty--page">
		<p class="atj-kicker">404</p>
		<h1><?php esc_html_e( 'Страница не найдена', 'antiterror-journal' ); ?></h1>
		<p><?php esc_html_e( 'Материал снят, перенесён или ещё не опубликован. Воспользуйтесь поиском по журналу.', 'antiterror-journal' ); ?></p>
		<?php get_search_form(); ?>
	</section>
</main>
<?php
get_footer();
