<?php
/**
 * Civic-editorial masthead.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header id="masthead" class="site-header" itemscope itemtype="https://schema.org/WPHeader">
	<div class="site-header__edition shell">
		<span><?php echo esc_html( aegis_edition_line() ); ?></span>
		<span class="site-header__kicker"><?php echo esc_html( aegis_kicker() ); ?></span>
	</div>
	<div class="site-header__bar shell">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span class="site-logo__mark" aria-hidden="true">
						<svg viewBox="0 0 52 60"><path d="M26 2 49 11v18c0 13-8 24-23 29C11 53 3 42 3 29V11L26 2Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M26 14 40 20v12c0 9-5 16-14 21-9-5-14-12-14-21V20L26 14Z" fill="none" stroke="currentColor" stroke-width="1" opacity=".45"/></svg>
					</span>
					<span class="site-logo__text">
						<strong><?php esc_html_e( 'Журнал', 'aegis' ); ?></strong>
						<b><?php bloginfo( 'name' ); ?></b>
					</span>
				</a>
			<?php endif; ?>
		</div>

		<nav class="site-primary-menu" aria-label="<?php esc_attr_e( 'Основная навигация', 'aegis' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 2,
				) );
			} else {
				aegis_fallback_menu();
			}
			?>
		</nav>

		<div class="site-header__actions">
			<button class="icon-btn search-toggle" type="button" data-search-open aria-expanded="false" aria-controls="site-search">
				<span class="screen-reader-text"><?php esc_html_e( 'Поиск', 'aegis' ); ?></span>
				<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="m16 16 5 5" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
			</button>
			<button class="icon-btn theme-toggle" type="button" data-theme-toggle aria-label="<?php esc_attr_e( 'Включить тёмную тему', 'aegis' ); ?>">
				<svg class="theme-toggle__moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.7 15.1A9 9 0 0 1 8.9 3.3 9 9 0 1 0 20.7 15.1Z"/></svg>
				<svg class="theme-toggle__sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
			</button>
			<a class="site-header__cta" href="<?php echo esc_url( home_url( '/#initiatives' ) ); ?>"><?php esc_html_e( 'Голосовать', 'aegis' ); ?> <span aria-hidden="true">→</span></a>
			<button class="mob-hamburger" type="button" aria-expanded="false" aria-controls="site-mobile-menu" aria-label="<?php esc_attr_e( 'Открыть меню', 'aegis' ); ?>"><span></span><span></span></button>
		</div>
	</div>
	<div id="site-mobile-menu" class="site-mobile-menu" hidden>
		<nav class="shell" aria-label="<?php esc_attr_e( 'Мобильная навигация', 'aegis' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 1,
				) );
			} else {
				aegis_fallback_menu();
			}
			?>
		</nav>
	</div>
	<div id="site-search" class="site-search" hidden>
		<div class="shell">
			<?php get_search_form(); ?>
			<button class="site-search__close" type="button" data-search-close><?php esc_html_e( 'Закрыть', 'aegis' ); ?></button>
		</div>
	</div>
</header>
