<?php
/**
 * Footer.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer id="colophon" class="site-footer" itemscope itemtype="https://schema.org/WPFooter">
	<div class="shell site-footer__grid">
		<div class="site-footer__brand">
			<a class="site-logo site-logo--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="site-logo__mark" aria-hidden="true">
					<svg viewBox="0 0 52 60"><path d="M26 2 49 11v18c0 13-8 24-23 29C11 53 3 42 3 29V11L26 2Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M26 16v22" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="26" cy="16" r="2.2" fill="currentColor"/></svg>
				</span>
				<span class="site-logo__text">
					<strong><?php echo esc_html( vigilum_brand_small() ); ?></strong>
					<b><?php echo esc_html( vigilum_brand_large() ); ?></b>
				</span>
			</a>
			<p><?php echo esc_html( vigilum_mod( 'vigilum_mission', __( 'Мы работаем с фактами, бережно относимся к памяти и даём обществу законный канал инициатив в сфере антитеррора.', 'vigilum' ) ) ); ?></p>
		</div>
		<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Навигация в подвале', 'vigilum' ); ?>">
			<p><?php esc_html_e( 'Разделы', 'vigilum' ); ?></p>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 1,
				) );
			} else {
				vigilum_fallback_menu();
			}
			?>
		</nav>
		<div class="site-footer__contacts">
			<p><?php esc_html_e( 'Связаться', 'vigilum' ); ?></p>
			<a href="mailto:<?php echo esc_attr( vigilum_email() ); ?>"><?php echo esc_html( vigilum_email() ); ?></a>
			<a href="<?php echo esc_url( home_url( '/#report' ) ); ?>"><?php esc_html_e( 'Передать информацию', 'vigilum' ); ?></a>
			<a href="<?php echo esc_url( get_privacy_policy_url() ?: home_url( '/konfidencialnost/' ) ); ?>"><?php esc_html_e( 'Конфиденциальность', 'vigilum' ); ?></a>
		</div>
	</div>
	<div class="shell site-footer__bottom">
		<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
		<span><?php echo esc_html( vigilum_mod( 'vigilum_footer_note', __( 'Информация на сайте не заменяет обращение в экстренные службы.', 'vigilum' ) ) ); ?></span>
	</div>
</footer>
