<?php
/**
 * Empty state.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="atj-empty atj-empty--page">
	<p class="atj-kicker"><?php esc_html_e( 'Пусто', 'antiterror-journal' ); ?></p>
	<h1><?php esc_html_e( 'Материалов пока нет', 'antiterror-journal' ); ?></h1>
	<p><?php esc_html_e( 'Редакция публикует только проверяемые сведения. Загляните позже или воспользуйтесь поиском.', 'antiterror-journal' ); ?></p>
	<?php get_search_form(); ?>
</section>
