<?php
/**
 * Cover / hero.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = atj_mod( 'atj_hero_title', __( "Память сильнее\nстраха.", 'antiterror-journal' ) );
$lead  = atj_mod( 'atj_hero_lead', __( 'Редакция собирает проверяемые материалы о противодействии терроризму, сохраняет память героев и открывает фонд гражданских инициатив.', 'antiterror-journal' ) );
?>
<section id="cover" class="atj-cover" aria-labelledby="atj-cover-title">
	<div class="atj-shell atj-cover__grid">
		<div class="atj-cover__copy">
			<p class="atj-kicker atj-kicker--light"><?php echo esc_html( atj_mod( 'atj_issue', __( 'Выпуск 01', 'antiterror-journal' ) ) ); ?> · <?php echo esc_html( atj_mod( 'atj_kicker', __( 'Журнал антитеррора', 'antiterror-journal' ) ) ); ?></p>
			<h1 id="atj-cover-title"><?php echo nl2br( esc_html( $title ) ); ?></h1>
			<p class="atj-cover__lead"><?php echo esc_html( $lead ); ?></p>
			<div class="atj-actions">
				<a class="atj-btn atj-btn--solid" href="#journal"><?php echo esc_html( atj_mod( 'atj_hero_primary', __( 'Читать выпуск', 'antiterror-journal' ) ) ); ?> <span aria-hidden="true">→</span></a>
				<a class="atj-btn atj-btn--ghost" href="#fund"><?php esc_html_e( 'Фонд инициатив', 'antiterror-journal' ); ?> <span aria-hidden="true">↘</span></a>
			</div>
			<p class="atj-live"><span></span><?php esc_html_e( 'Конфиденциально. Каждое обращение рассматривается.', 'antiterror-journal' ); ?></p>
		</div>
		<aside class="atj-cover__aside" aria-label="<?php esc_attr_e( 'О выпуске', 'antiterror-journal' ); ?>">
			<div class="atj-cover__mark" aria-hidden="true">
				<svg viewBox="0 0 360 440">
					<path d="M180 18 326 72v120c0 102-58 183-146 230C92 375 34 294 34 192V72L180 18Z" fill="none" stroke="currentColor" stroke-width="2"/>
					<path d="M180 62 286 101v91c0 75-40 136-106 176-66-40-106-101-106-176v-91L180 62Z" fill="none" stroke="currentColor" stroke-width="1" opacity=".38"/>
					<path d="M180 128v120M120 188h120" fill="none" stroke="currentColor" stroke-width="10"/>
				</svg>
			</div>
			<div class="atj-cover__note">
				<span>01</span>
				<p><?php esc_html_e( 'Журнал. Память. Гражданский фонд — три главы одного выпуска.', 'antiterror-journal' ); ?></p>
			</div>
		</aside>
	</div>
</section>
