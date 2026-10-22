<?php
/**
 * Подвал: мачта журнала + гражданская оговорка.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$email = rubezh_option( 'rubezh_email', 'info@example.org' );
$city  = rubezh_option( 'rubezh_city', 'Россия' );
?>
<footer id="colophon" class="site-footer" itemscope itemtype="https://schema.org/WPFooter">
	<div class="shell site-footer__grid">
		<div class="site-footer__brand">
			<a class="site-logo site-logo--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="site-logo__mark" aria-hidden="true">
					<svg viewBox="0 0 52 60"><path d="M26 3 48 12v16c0 14-8 25-22 29C12 53 4 42 4 28V12L26 3Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M26 14v22M17 26h18" fill="none" stroke="currentColor" stroke-width="2"/></svg>
				</span>
				<span class="site-logo__text">
					<strong><?php esc_html_e( 'Журнал', 'rubezh' ); ?></strong>
					<b><?php bloginfo( 'name' ); ?></b>
				</span>
			</a>
			<p><?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Журнал антитеррора: факты, память героев и фонд гражданских инициатив.', 'rubezh' ) ); ?></p>
			<p class="site-footer__city"><?php echo esc_html( $city ); ?></p>
		</div>
		<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Разделы', 'rubezh' ); ?>">
			<p><?php esc_html_e( 'Разделы', 'rubezh' ); ?></p>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'depth'          => 1,
				) );
			} else {
				rubezh_fallback_menu();
			}
			?>
		</nav>
		<div class="site-footer__contacts">
			<p><?php esc_html_e( 'Связаться', 'rubezh' ); ?></p>
			<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
			<a href="<?php echo esc_url( home_url( '/#report' ) ); ?>"><?php esc_html_e( 'Передать информацию', 'rubezh' ); ?></a>
			<a href="<?php echo esc_url( rubezh_archive_link( 'initiative' ) ); ?>"><?php esc_html_e( 'Поддержать инициативу', 'rubezh' ); ?></a>
		</div>
	</div>
	<div class="shell site-footer__bottom">
		<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
		<span><?php esc_html_e( 'Информация на сайте не заменяет обращение в экстренные службы. Телефон 112.', 'rubezh' ); ?></span>
	</div>
</footer>
