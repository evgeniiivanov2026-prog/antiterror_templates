<?php
/**
 * Opening masthead — journal + civic.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = aegis_option( 'aegis_hero_title', __( 'Память держит линию. Общество держит строй.', 'aegis' ) );
$lead  = aegis_option( 'aegis_hero_lead', __( 'Aegis — журнальный шаблон антитеррора: расследования и аналитика, зал памяти героев и фонд гражданских инициатив, за которые можно проголосовать.', 'aegis' ) );
$cta   = aegis_option( 'aegis_hero_cta', __( 'Поддержать инициативу', 'aegis' ) );
$url   = aegis_option( 'aegis_hero_cta_url', '#initiatives' );
if ( 0 === strpos( $url, '#' ) ) {
	$url = home_url( '/' . $url );
}
?>
<section class="opening" aria-labelledby="opening-title">
	<div class="shell opening__grid">
		<div class="opening__copy reveal">
			<p class="section-kicker section-kicker--light"><?php echo esc_html( aegis_kicker() ); ?></p>
			<h1 id="opening-title"><?php echo wp_kses_post( $title ); ?></h1>
			<p class="opening__lead"><?php echo wp_kses_post( $lead ); ?></p>
			<div class="opening__actions">
				<a class="btn btn--primary" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $cta ); ?> <span aria-hidden="true">→</span></a>
				<a class="btn btn--ghost" href="#memory"><?php esc_html_e( 'Зал памяти', 'aegis' ); ?> <span aria-hidden="true">↘</span></a>
			</div>
			<p class="opening__note"><span aria-hidden="true"></span> <?php esc_html_e( 'Голос — гражданская поддержка проекта. При угрозе звоните 112.', 'aegis' ); ?></p>
		</div>
		<div class="opening__visual" aria-hidden="true">
			<div class="opening__shield">
				<svg viewBox="0 0 360 440">
					<path d="M180 18 326 72v120c0 102-58 183-146 230C92 375 34 294 34 192V72L180 18Z" fill="none" stroke="currentColor" stroke-width="2"/>
					<path d="M180 62 286 101v91c0 75-40 136-106 176-66-40-106-101-106-176v-91L180 62Z" fill="none" stroke="currentColor" stroke-width="1" opacity=".38"/>
					<text x="180" y="220" text-anchor="middle" fill="currentColor" font-size="54" font-family="Source Serif 4, PT Serif, serif">A</text>
				</svg>
			</div>
			<div class="opening__caption">
				<span>01</span>
				<p><?php esc_html_e( 'Редакция держит тон журнала. Фонд держит память и приоритеты общества.', 'aegis' ); ?></p>
			</div>
		</div>
	</div>
</section>
