<?php
/**
 * Подвал.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email = sanitize_email( (string) strazha_mod( 'email' ) );
?>
<footer class="foot" id="site-footer">
	<div class="shell foot__grid">
		<div class="foot__brand">
			<?php strazha_brand( true ); ?>
			<p><?php echo esc_html( strazha_mod( 'footer_text' ) ); ?></p>
		</div>
		<nav class="foot__nav" aria-label="<?php esc_attr_e( 'Навигация в подвале', 'strazha' ); ?>">
			<p><?php esc_html_e( 'Разделы', 'strazha' ); ?></p>
			<?php strazha_navigation( 'footer' ); ?>
		</nav>
		<div class="foot__contact">
			<p><?php esc_html_e( 'Связь', 'strazha' ); ?></p>
			<?php if ( $email ) : ?>
				<a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
			<?php endif; ?>
			<a href="tel:112"><?php esc_html_e( 'Экстренная служба 112', 'strazha' ); ?></a>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'initiative' ) ); ?>"><?php esc_html_e( 'Фонд инициатив', 'strazha' ); ?></a>
		</div>
	</div>
	<div class="shell foot__base">
		<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
		<span><?php esc_html_e( 'При непосредственной угрозе звоните 112. Не вмешивайтесь самостоятельно.', 'strazha' ); ?></span>
	</div>
</footer>
