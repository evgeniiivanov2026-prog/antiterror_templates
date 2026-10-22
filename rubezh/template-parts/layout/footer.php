<?php
/**
 * Подвал издания.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$email = sanitize_email( rubezh_mod( 'email' ) );
$legal = trim( rubezh_mod( 'legal' ) );
?>
<footer class="rz-footer">
	<div class="rz-shell rz-footer__grid">
		<div>
			<a class="rz-brand rz-brand--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php echo rubezh_icon( 'mark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="rz-brand__name"><?php bloginfo( 'name' ); ?></span>
			</a>
			<p><?php echo esc_html( rubezh_tagline() ); ?></p>
		</div>
		<nav aria-label="<?php esc_attr_e( 'Меню в подвале', 'rubezh' ); ?>">
			<p class="rz-footer__label"><?php esc_html_e( 'Разделы', 'rubezh' ); ?></p>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'rz-footer__links',
					'fallback_cb'    => 'rubezh_footer_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>
		<div>
			<p class="rz-footer__label"><?php esc_html_e( 'Связь', 'rubezh' ); ?></p>
			<div class="rz-footer__links">
				<?php if ( $email ) : ?>
					<a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
				<?php else : ?>
					<span><?php esc_html_e( 'Адрес редакции задаётся в настройщике.', 'rubezh' ); ?></span>
				<?php endif; ?>
				<a href="<?php echo esc_url( home_url( '/#signal' ) ); ?>"><?php esc_html_e( 'Экстренная плашка', 'rubezh' ); ?></a>
			</div>
		</div>
	</div>
	<div class="rz-shell rz-footer__bottom">
		<span>
			<?php
			echo esc_html( wp_date( 'Y' ) );
			echo ' ';
			bloginfo( 'name' );
			?>
		</span>
		<span><?php echo esc_html( $legal ? $legal : rubezh_mod( 'signal_note' ) ); ?></span>
	</div>
</footer>
