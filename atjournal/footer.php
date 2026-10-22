<?php
/**
 * Footer.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email = sanitize_email( atj_mod( 'email' ) );
?>
<footer class="atj-footer">
	<div class="atj-shell atj-footer__grid">
		<div class="atj-footer__brand">
			<a class="atj-logo atj-logo--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<span class="atj-logo__mark"><?php echo atj_icon( 'mark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="atj-logo__text">
					<strong><?php echo esc_html( atj_mod( 'kicker' ) ); ?></strong>
					<b><?php bloginfo( 'name' ); ?></b>
				</span>
			</a>
			<p><?php echo esc_html( atj_mod( 'lead' ) ); ?></p>
		</div>
		<nav class="atj-footer__nav" aria-label="<?php esc_attr_e( 'Навигация в подвале', 'atjournal' ); ?>">
			<p><?php esc_html_e( 'Разделы', 'atjournal' ); ?></p>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'atj-footer__menu',
					'fallback_cb'    => 'atj_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
		</nav>
		<div class="atj-footer__signal">
			<p><?php esc_html_e( 'Экстренно', 'atjournal' ); ?></p>
			<a href="tel:112">112</a>
			<p><?php esc_html_e( 'Сайт не принимает оперативные сообщения и не заменяет службы.', 'atjournal' ); ?></p>
			<?php if ( $email ) : ?>
				<a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
			<?php endif; ?>
		</div>
	</div>
	<div class="atj-shell atj-footer__bar">
		<p>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		<?php
		$privacy = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '';
		if ( $privacy ) :
			?>
			<a href="<?php echo esc_url( $privacy ); ?>"><?php esc_html_e( 'Конфиденциальность', 'atjournal' ); ?></a>
		<?php else : ?>
			<p><?php esc_html_e( 'Политику конфиденциальности добавьте до запуска.', 'atjournal' ); ?></p>
		<?php endif; ?>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
