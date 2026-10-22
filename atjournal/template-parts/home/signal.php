<?php
/**
 * Emergency boundary. No tip form.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email = sanitize_email( atj_mod( 'email' ) );
?>
<section class="atj-signal" id="signal" aria-labelledby="atj-signal-title">
	<div class="atj-shell atj-signal__grid">
		<div>
			<p class="atj-num atj-num--light"><?php esc_html_e( '06  /  Сигнал', 'atjournal' ); ?></p>
			<h2 id="atj-signal-title"><?php esc_html_e( 'Если есть угроза', 'atjournal' ); ?></h2>
			<p><?php esc_html_e( 'Звоните 112. Не подходите ближе, не выясняйте обстоятельства сами и не публикуйте имена, фотографии, адреса и догадки.', 'atjournal' ); ?></p>
		</div>
		<div class="atj-signal__actions">
			<a class="atj-button atj-button--light" href="tel:112"><?php esc_html_e( 'Позвонить 112', 'atjournal' ); ?> <span aria-hidden="true">→</span></a>
			<?php if ( $email ) : ?>
				<a class="atj-button atj-button--ghostlight" href="<?php echo esc_url( 'mailto:' . $email . '?subject=' . rawurlencode( 'Редакции журнала' ) ); ?>"><?php esc_html_e( 'Написать редакции', 'atjournal' ); ?></a>
				<p><?php esc_html_e( 'Письмо — для тем материалов, не для оперативных сообщений.', 'atjournal' ); ?></p>
			<?php else : ?>
				<p><?php esc_html_e( 'Почту редакции можно указать в настройщике. Пока поле пустое, нерабочий адрес не показывается.', 'atjournal' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
