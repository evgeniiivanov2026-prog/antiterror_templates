<?php
/**
 * Пустая выборка.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="empty">
	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( 'По этому запросу ничего не нашлось. Попробуйте другие слова или откройте рубрику.', 'strazha' ); ?></p>
	<?php else : ?>
		<p><?php esc_html_e( 'В этом разделе пока нет опубликованных материалов.', 'strazha' ); ?></p>
	<?php endif; ?>
	<?php get_search_form(); ?>
</div>
