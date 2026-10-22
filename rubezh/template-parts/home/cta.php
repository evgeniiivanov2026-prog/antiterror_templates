<?php
/**
 * Гражданское обращение — тон Root Академический.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$email = rubezh_option( 'rubezh_email', 'info@example.org' );
?>
<section id="report" class="section section--report" aria-labelledby="report-title">
	<div class="shell report__grid reveal">
		<div>
			<p class="section-number section-number--light"><?php esc_html_e( '04 / Обращение', 'rubezh' ); ?></p>
			<h2 id="report-title"><?php esc_html_e( 'Знаете об угрозе?', 'rubezh' ); ?></h2>
			<p><?php esc_html_e( 'Передайте информацию редакции или в компетентные органы. Не вступайте в конфликт и не проводите расследование самостоятельно.', 'rubezh' ); ?></p>
		</div>
		<div class="report__action">
			<a class="btn btn--light" href="mailto:<?php echo esc_attr( $email ); ?>?subject=<?php echo rawurlencode( 'Конфиденциальное обращение' ); ?>">
				<?php esc_html_e( 'Написать редакции', 'rubezh' ); ?> <span aria-hidden="true">→</span>
			</a>
			<p><?php esc_html_e( 'В экстренной ситуации звоните 112.', 'rubezh' ); ?></p>
		</div>
	</div>
</section>
