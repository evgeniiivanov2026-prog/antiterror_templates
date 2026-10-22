<?php
/**
 * Site footer.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</div><!-- #content -->

<footer class="atj-footer" id="colophon">
	<div class="atj-shell atj-footer__grid">
		<div class="atj-footer__brand">
			<a class="atj-logo atj-logo--light" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="atj-logo__mark" aria-hidden="true">
					<svg viewBox="0 0 52 60"><path d="M26 2 49 11v18c0 13-8 24-23 29C11 53 3 42 3 29V11L26 2Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M26 16v20M16 26h20" fill="none" stroke="currentColor" stroke-width="2.4"/></svg>
				</span>
				<span class="atj-logo__text">
					<strong><?php echo esc_html( atj_mod( 'atj_kicker', __( 'Журнал', 'antiterror-journal' ) ) ); ?></strong>
					<b><?php echo esc_html( get_bloginfo( 'name' ) ); ?></b>
				</span>
			</a>
			<p><?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Журнальная редакция антитеррора: материалы, память героев и фонд гражданских инициатив.', 'antiterror-journal' ) ); ?></p>
		</div>

		<nav class="atj-footer__nav" aria-label="<?php esc_attr_e( 'Навигация в подвале', 'antiterror-journal' ); ?>">
			<p><?php esc_html_e( 'Разделы', 'antiterror-journal' ); ?></p>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 1,
					)
				);
			} else {
				foreach ( atj_default_nav_items() as $item ) {
					printf( '<a href="%s">%s</a>', esc_url( $item[0] ), esc_html( $item[1] ) );
				}
			}
			?>
		</nav>

		<div class="atj-footer__contacts">
			<p><?php esc_html_e( 'Связаться', 'antiterror-journal' ); ?></p>
			<a href="mailto:<?php echo esc_attr( atj_contact_email() ); ?>"><?php echo esc_html( atj_contact_email() ); ?></a>
			<a href="<?php echo esc_url( home_url( '/#report' ) ); ?>"><?php esc_html_e( 'Передать информацию', 'antiterror-journal' ); ?></a>
			<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
				<?php dynamic_sidebar( 'footer-1' ); ?>
			<?php endif; ?>
		</div>
	</div>
	<div class="atj-shell atj-footer__bottom">
		<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
		<span><?php esc_html_e( 'Информация на сайте не заменяет обращение в экстренные службы. В угрозе жизни звоните 112.', 'antiterror-journal' ); ?></span>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
