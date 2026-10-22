<?php
/**
 * Страница не найдена.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="rz-main">
	<div class="rz-shell rz-missing">
		<p class="rz-index">404</p>
		<h1><?php esc_html_e( 'Этой страницы нет в летописи', 'rubezh' ); ?></h1>
		<p class="rz-deck"><?php esc_html_e( 'Проверьте адрес или найдите материал, карточку памяти и инициативу поиском.', 'rubezh' ); ?></p>
		<?php get_search_form(); ?>
	</div>
</main>
<?php
get_footer();
