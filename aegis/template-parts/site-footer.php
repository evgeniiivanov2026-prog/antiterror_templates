<?php
/**
 * Footer.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email = aegis_email();
?>
<footer id="colophon" class="site-footer" itemscope itemtype="https://schema.org/WPFooter">
	<div class="shell site-footer__grid">
		<div class="site-footer__brand">
			<a class="site-logo site-logo--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="site-logo__mark" aria-hidden="true">
					<svg viewBox="0 0 52 60"><path d="M26 2 49 11v18c0 13-8 24-23 29C11 53 3 42 3 29V11L26 2Z" fill="none" stroke="currentColor" stroke-width="2"/></svg>
				</span>
				<span class="site-logo__text">
					<strong><?php esc_html_e( 'Журнал', 'aegis' ); ?></strong>
					<b><?php bloginfo( 'name' ); ?></b>
				</span>
			</a>
			<p><?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Журнал антитеррора, зал памяти героев и фонд гражданских инициатив.', 'aegis' ) ); ?></p>
			<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
				<div class="site-footer__widgets"><?php dynamic_sidebar( 'footer-1' ); ?></div>
			<?php endif; ?>
		</div>
		<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Навигация в подвале', 'aegis' ); ?>">
			<p><?php esc_html_e( 'Разделы', 'aegis' ); ?></p>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'depth'          => 1,
				) );
			} else {
				aegis_fallback_menu();
			}
			?>
		</nav>
		<div class="site-footer__contacts">
			<p><?php esc_html_e( 'Связаться', 'aegis' ); ?></p>
			<?php if ( $email ) : ?>
				<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
			<?php endif; ?>
			<a href="<?php echo esc_url( home_url( '/#foundation' ) ); ?>"><?php esc_html_e( 'Передать информацию', 'aegis' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/#propose' ) ); ?>"><?php esc_html_e( 'Предложить инициативу', 'aegis' ); ?></a>
		</div>
	</div>
	<div class="shell site-footer__bottom">
		<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
		<span><?php esc_html_e( 'Информация на сайте не заменяет обращение в экстренные службы. При угрозе звоните 112.', 'aegis' ); ?></span>
	</div>
</footer>
