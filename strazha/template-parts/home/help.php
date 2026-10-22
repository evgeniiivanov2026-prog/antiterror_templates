<?php
/**
 * Напоминание об экстренной службе.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="help" id="help">
	<div class="shell help__row">
		<p class="section__num"><?php esc_html_e( '05 / Если есть угроза', 'strazha' ); ?></p>
		<div>
			<h2><?php esc_html_e( 'Звоните 112.', 'strazha' ); ?></h2>
			<p><?php esc_html_e( 'Сайт не принимает экстренные сообщения и не заменяет компетентные органы. Не вступайте в конфликт и не проводите проверку самостоятельно.', 'strazha' ); ?></p>
		</div>
		<a class="button button--light" href="tel:112"><?php esc_html_e( 'Позвонить 112', 'strazha' ); ?></a>
	</div>
</section>
