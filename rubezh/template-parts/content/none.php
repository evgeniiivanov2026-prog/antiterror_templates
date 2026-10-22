<?php
/**
 * Пустая выборка.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="rz-empty">
	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( 'По этому запросу ничего не нашлось. Попробуйте имя, рубрику или короткую формулировку.', 'rubezh' ); ?></p>
		<?php get_search_form(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'В летописи пока нет материалов.', 'rubezh' ); ?></p>
	<?php endif; ?>
</div>
