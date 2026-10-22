<?php
/**
 * Document head and masthead.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>
		(function () {
			var root = document.documentElement;
			root.classList.add('has-js');
			try {
				var stored = localStorage.getItem('atj-theme');
				var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
				root.setAttribute('data-theme', theme);
			} catch (error) {}
		}());
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="atj-skip" href="#content"><?php esc_html_e( 'К содержанию', 'atjournal' ); ?></a>
<?php if ( is_singular() ) : ?>
	<div class="atj-progress" aria-hidden="true"><span></span></div>
<?php endif; ?>

<div class="atj-utility">
	<div class="atj-shell atj-utility__inner">
		<p><?php echo esc_html( atj_mod( 'issue' ) ); ?> · <time datetime="<?php echo esc_attr( wp_date( 'c' ) ); ?>"><?php echo esc_html( atj_human_date() ); ?></time></p>
		<p class="atj-utility__note"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
	</div>
</div>

<header class="atj-header">
	<div class="atj-shell atj-header__inner">
		<a class="atj-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<span class="atj-logo__mark">
				<?php
				$logo_id = (int) get_theme_mod( 'custom_logo' );
				if ( $logo_id ) {
					echo wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'atj-logo__img', 'alt' => '' ) );
				} else {
					echo atj_icon( 'mark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static icon.
				}
				?>
			</span>
			<span class="atj-logo__text">
				<strong><?php echo esc_html( atj_mod( 'kicker' ) ); ?></strong>
				<b><?php bloginfo( 'name' ); ?></b>
			</span>
		</a>

		<nav class="atj-nav" aria-label="<?php esc_attr_e( 'Основная навигация', 'atjournal' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'atj-menu',
					'fallback_cb'    => 'atj_menu_fallback',
					'depth'          => 2,
				)
			);
			?>
		</nav>

		<div class="atj-tools">
			<button class="atj-iconbtn" type="button" data-search-open aria-controls="atj-search-layer" aria-expanded="false">
				<?php echo atj_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Открыть поиск', 'atjournal' ); ?></span>
			</button>
			<button class="atj-iconbtn atj-theme" type="button" data-theme-toggle aria-label="<?php esc_attr_e( 'Включить тёмную тему', 'atjournal' ); ?>">
				<span class="atj-theme__moon"><?php echo atj_icon( 'moon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="atj-theme__sun"><?php echo atj_icon( 'sun' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</button>
			<a class="atj-call" href="tel:112"><?php esc_html_e( '112', 'atjournal' ); ?><span class="screen-reader-text"> <?php esc_html_e( '— экстренная служба', 'atjournal' ); ?></span></a>
			<button class="atj-burger" type="button" data-menu-toggle aria-expanded="false" aria-controls="atj-drawer">
				<span class="atj-burger__bar"></span><span class="atj-burger__bar"></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Открыть меню', 'atjournal' ); ?></span>
			</button>
		</div>
	</div>
</header>

<div id="atj-drawer" class="atj-drawer" hidden>
	<nav class="atj-shell" aria-label="<?php esc_attr_e( 'Мобильная навигация', 'atjournal' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'atj-drawer__menu',
				'fallback_cb'    => 'atj_menu_fallback',
				'depth'          => 2,
			)
		);
		?>
		<a class="atj-call atj-call--block" href="tel:112"><?php esc_html_e( 'Экстренно: 112', 'atjournal' ); ?></a>
	</nav>
</div>

<div id="atj-search-layer" class="atj-search-layer" hidden>
	<div class="atj-shell atj-search-layer__inner">
		<form class="atj-searchform atj-searchform--overlay" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="atj-search-field"><?php esc_html_e( 'Поиск', 'atjournal' ); ?></label>
			<input id="atj-search-field" type="search" name="s" placeholder="<?php esc_attr_e( 'Материал, имя или инициатива', 'atjournal' ); ?>" autocomplete="off">
			<button type="submit"><?php esc_html_e( 'Найти', 'atjournal' ); ?></button>
		</form>
		<button class="atj-textbtn" type="button" data-search-close><?php esc_html_e( 'Закрыть', 'atjournal' ); ?></button>
	</div>
</div>
