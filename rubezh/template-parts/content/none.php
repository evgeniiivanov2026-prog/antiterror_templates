<?php
/**
 * Пустое состояние.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="empty-state">
	<h2><?php esc_html_e( 'Пока пусто', 'rubezh' ); ?></h2>
	<p><?php esc_html_e( 'Добавьте записи, карточки героев или инициативы — шаблон сам соберёт выпуск.', 'rubezh' ); ?></p>
	<?php get_search_form(); ?>
</div>
