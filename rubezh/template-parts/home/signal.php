<?php
/**
 * Экстренная плашка.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$email = sanitize_email( rubezh_mod( 'email' ) );
?>
<section class="rz-signal" id="signal" aria-labelledby="rz-signal-title">
	<div class="rz-shell rz-signal__grid">
		<div>
			<p class="rz-index"><span>05</span> <?php esc_html_e( 'Сигнал', 'rubezh' ); ?></p>
			<h2 id="rz-signal-title"><?php echo esc_html( rubezh_mod( 'signal_title' ) ); ?></h2>
			<p><?php echo esc_html( rubezh_mod( 'signal_text' ) ); ?></p>
		</div>
		<div class="rz-signal__action">
			<a class="rz-button rz-button--light" href="tel:112"><?php esc_html_e( 'Позвонить 112', 'rubezh' ); ?></a>
			<?php if ( $email ) : ?>
				<a class="rz-textlink rz-textlink--light" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php esc_html_e( 'Написать в редакцию', 'rubezh' ); ?></a>
			<?php endif; ?>
			<p><?php echo esc_html( rubezh_mod( 'signal_note' ) ); ?></p>
		</div>
	</div>
</section>
