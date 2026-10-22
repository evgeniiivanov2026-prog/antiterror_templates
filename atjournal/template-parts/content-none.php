<?php
/**
 * Empty state.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="atj-empty">
	<?php if ( is_search() ) : ?>
		<h2><?php esc_html_e( 'Ничего не нашлось', 'atjournal' ); ?></h2>
		<p><?php esc_html_e( 'Попробуйте другое слово: имя, рубрику или формулировку инициативы.', 'atjournal' ); ?></p>
	<?php else : ?>
		<h2><?php esc_html_e( 'Здесь пока пусто', 'atjournal' ); ?></h2>
		<p><?php esc_html_e( 'Материалы появятся, когда редакция их опубликует.', 'atjournal' ); ?></p>
	<?php endif; ?>
	<?php get_search_form(); ?>
</section>
