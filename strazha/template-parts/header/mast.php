<?php
/**
 * Мачта журнала.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fund_url = ( is_front_page() && strazha_flag( 'show_fund' ) ) ? '#fund' : get_post_type_archive_link( 'initiative' );
?>
<header class="mast" id="mast">
	<div class="mast__edition">
		<div class="shell mast__edition-row">
			<time datetime="<?php echo esc_attr( wp_date( 'c' ) ); ?>"><?php echo esc_html( wp_date( 'j F Y' ) ); ?></time>
			<span><?php echo esc_html( strazha_mod( 'kicker' ) ); ?></span>
			<a href="tel:112"><?php esc_html_e( 'Экстренно: 112', 'strazha' ); ?></a>
		</div>
	</div>
	<div class="mast__bar">
		<div class="shell mast__bar-row">
			<div class="mast__brand">
				<?php strazha_brand(); ?>
			</div>
			<nav class="mast__nav" aria-label="<?php esc_attr_e( 'Основная навигация', 'strazha' ); ?>">
				<?php strazha_navigation( 'primary' ); ?>
			</nav>
			<div class="mast__tools">
				<button class="tool" type="button" data-search-open aria-label="<?php esc_attr_e( 'Открыть поиск', 'strazha' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.25" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M16 16.5 20 20.5" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
				</button>
				<button class="tool theme-toggle" type="button" data-theme-toggle aria-label="<?php esc_attr_e( 'Включить тёмную тему', 'strazha' ); ?>">
					<svg class="theme-toggle__moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 3.2A8.5 8.5 0 1 0 20.8 14 7 7 0 0 1 16 3.2Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
					<svg class="theme-toggle__sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M12 3v2M12 19v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M3 12h2M19 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
				</button>
				<?php if ( $fund_url ) : ?>
					<a class="mast__fund" href="<?php echo esc_url( $fund_url ); ?>"><?php esc_html_e( 'Голосовать', 'strazha' ); ?></a>
				<?php endif; ?>
				<button class="burger" type="button" data-nav-open aria-expanded="false" aria-controls="nav-dialog" aria-label="<?php esc_attr_e( 'Открыть меню', 'strazha' ); ?>">
					<span></span><span></span>
				</button>
			</div>
		</div>
	</div>
</header>
