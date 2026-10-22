<?php
/**
 * Confidential report CTA.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="report" class="atj-report" aria-labelledby="report-title" data-observe>
	<div class="atj-shell atj-report__grid">
		<div>
			<p class="atj-section-number atj-section-number--light">04 / <?php esc_html_e( 'Обращение', 'antiterror-journal' ); ?></p>
			<h2 id="report-title"><?php esc_html_e( 'Знаете об угрозе?', 'antiterror-journal' ); ?></h2>
			<p><?php echo esc_html( atj_mod( 'atj_report_text', __( 'Если вам известны сведения об угрозе, передайте их редакции. Не вступайте в конфликт и не проводите расследование самостоятельно.', 'antiterror-journal' ) ) ); ?></p>
		</div>
		<div class="atj-report__action">
			<a class="atj-btn atj-btn--light" href="<?php echo esc_url( atj_mailto_report() ); ?>"><?php esc_html_e( 'Написать редакции', 'antiterror-journal' ); ?> <span aria-hidden="true">→</span></a>
			<p><?php esc_html_e( 'В экстренной ситуации звоните 112.', 'antiterror-journal' ); ?></p>
		</div>
	</div>
</section>
