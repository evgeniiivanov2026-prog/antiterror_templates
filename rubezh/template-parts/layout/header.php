<?php
/**
 * Институциональная шапка: стекло, выпуск, поиск, тема.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$issue = rubezh_option( 'rubezh_issue', '№ 04 / осень 2027' );
?>
<header id="masthead" class="site-header" itemscope itemtype="https://schema.org/WPHeader">
	<div class="site-header__rail">
		<div class="shell site-header__inner">
			<div class="site-branding">
				<?php if ( is_front_page() ) : ?>
					<div class="site-logo" aria-label="<?php bloginfo( 'name' ); ?>">
				<?php else : ?>
					<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'На главную', 'rubezh' ); ?>">
				<?php endif; ?>
					<span class="site-logo__mark" aria-hidden="true">
						<svg viewBox="0 0 52 60"><path d="M26 3 48 12v16c0 14-8 25-22 29C12 53 4 42 4 28V12L26 3Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M26 14v22M17 26h18" fill="none" stroke="currentColor" stroke-width="2"/></svg>
					</span>
					<span class="site-logo__text">
						<strong><?php esc_html_e( 'Журнал', 'rubezh' ); ?></strong>
						<b><?php bloginfo( 'name' ); ?></b>
					</span>
				<?php if ( is_front_page() ) : ?></div><?php else : ?></a><?php endif; ?>
				<p class="site-logo__issue"><?php echo esc_html( $issue ); ?></p>
			</div>

			<nav class="site-primary-menu" aria-label="<?php esc_attr_e( 'Основная навигация', 'rubezh' ); ?>">
				<?php
				if ( has_nav_menu( 'primary' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'menu',
						'depth'          => 2,
					) );
				} else {
					rubezh_fallback_menu();
				}
				?>
			</nav>

			<div class="site-header__actions">
				<button class="icon-btn" type="button" data-search-open aria-expanded="false" aria-controls="site-search" aria-label="<?php esc_attr_e( 'Открыть поиск', 'rubezh' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 5 5" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
				</button>
				<button class="icon-btn theme-toggle" type="button" data-theme-toggle aria-label="<?php esc_attr_e( 'Включить тёмную тему', 'rubezh' ); ?>">
					<svg class="theme-toggle__moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.7 15.1A9 9 0 0 1 8.9 3.3 9 9 0 1 0 20.7 15.1Z" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
					<svg class="theme-toggle__sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
				</button>
				<a class="btn btn--burgundy site-header__vote" href="<?php echo esc_url( rubezh_archive_link( 'initiative' ) ); ?>">
					<?php esc_html_e( 'Голосовать', 'rubezh' ); ?>
					<span aria-hidden="true">→</span>
				</a>
				<button class="mob-hamburger" type="button" aria-expanded="false" aria-controls="site-mobile-menu" aria-label="<?php esc_attr_e( 'Открыть меню', 'rubezh' ); ?>"><span></span><span></span></button>
			</div>
		</div>
	</div>

	<div id="site-mobile-menu" class="site-mobile-menu" hidden>
		<nav class="shell" aria-label="<?php esc_attr_e( 'Мобильная навигация', 'rubezh' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 1,
				) );
			} else {
				rubezh_fallback_menu();
			}
			?>
		</nav>
	</div>

	<div id="site-search" class="site-search" hidden>
		<div class="shell site-search__inner">
			<?php get_search_form(); ?>
			<button class="icon-btn" type="button" data-search-close aria-label="<?php esc_attr_e( 'Закрыть поиск', 'rubezh' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
			</button>
		</div>
	</div>
	<div class="read-progress" data-read-progress hidden><span></span></div>
</header>
