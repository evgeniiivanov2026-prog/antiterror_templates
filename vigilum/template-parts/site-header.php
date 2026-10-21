<?php
/**
 * Sticky glass header.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header id="masthead" class="site-header" itemscope itemtype="https://schema.org/WPHeader">
	<div class="shell site-header__inner">
		<div class="site-branding">
			<?php if ( is_front_page() ) : ?>
				<div class="site-logo" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php else : ?>
				<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'На главную', 'vigilum' ); ?>">
			<?php endif; ?>
				<span class="site-logo__mark" aria-hidden="true">
					<svg viewBox="0 0 52 60"><path d="M26 2 49 11v18c0 13-8 24-23 29C11 53 3 42 3 29V11L26 2Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M26 16v22" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="26" cy="16" r="2.2" fill="currentColor"/></svg>
				</span>
				<span class="site-logo__text">
					<strong><?php echo esc_html( vigilum_brand_small() ); ?></strong>
					<b><?php echo esc_html( vigilum_brand_large() ); ?></b>
				</span>
			<?php if ( is_front_page() ) : ?>
				</div>
			<?php else : ?>
				</a>
			<?php endif; ?>
		</div>

		<nav class="site-primary-menu" aria-label="<?php esc_attr_e( 'Основная навигация', 'vigilum' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 2,
				) );
			} else {
				vigilum_fallback_menu();
			}
			?>
		</nav>

		<div class="site-header__actions">
			<button class="search-toggle" type="button" data-search-toggle aria-expanded="false" aria-controls="header-search" aria-label="<?php esc_attr_e( 'Открыть поиск', 'vigilum' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 4 4" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
			</button>
			<button class="theme-toggle" type="button" data-theme-toggle aria-label="<?php esc_attr_e( 'Переключить тему', 'vigilum' ); ?>">
				<svg class="theme-toggle__moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.7 15.1A9 9 0 0 1 8.9 3.3 9 9 0 1 0 20.7 15.1Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
				<svg class="theme-toggle__sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
			</button>
			<a class="site-header__cta" href="<?php echo esc_url( home_url( '/#report' ) ); ?>"><?php esc_html_e( 'Обратиться', 'vigilum' ); ?> <span aria-hidden="true">→</span></a>
			<button class="mob-hamburger" type="button" aria-expanded="false" aria-controls="site-mobile-menu" aria-label="<?php esc_attr_e( 'Открыть меню', 'vigilum' ); ?>"><span></span><span></span></button>
		</div>
	</div>

	<div id="header-search" class="header-search" hidden>
		<div class="shell"><?php get_search_form(); ?></div>
	</div>

	<div id="site-mobile-menu" class="site-mobile-menu" hidden>
		<nav class="shell" aria-label="<?php esc_attr_e( 'Мобильная навигация', 'vigilum' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 1,
				) );
			} else {
				vigilum_fallback_menu();
			}
			?>
			<a class="site-header__cta" href="<?php echo esc_url( home_url( '/#report' ) ); ?>"><?php esc_html_e( 'Обратиться', 'vigilum' ); ?></a>
		</nav>
	</div>
</header>
