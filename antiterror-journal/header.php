<?php
/**
 * Site header.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?> data-theme="light">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="atj-grain" aria-hidden="true"></div>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'К содержанию', 'antiterror-journal' ); ?></a>

<header class="atj-header" id="masthead">
	<div class="atj-ticker" aria-hidden="true">
		<div class="atj-ticker__track">
			<?php
			$issue  = atj_mod( 'atj_issue', __( 'Выпуск 01', 'antiterror-journal' ) );
			$kicker = atj_mod( 'atj_kicker', __( 'Журнал антитеррора', 'antiterror-journal' ) );
			$bits   = array( $issue, $kicker, __( 'Память героев', 'antiterror-journal' ), __( 'Фонд инициатив', 'antiterror-journal' ), $issue, $kicker );
			echo esc_html( implode( '  ·  ', $bits ) . '  ·  ' . implode( '  ·  ', $bits ) );
			?>
		</div>
	</div>

	<div class="atj-header__bar atj-shell">
		<?php if ( is_front_page() ) : ?>
			<div class="atj-logo">
		<?php else : ?>
			<a class="atj-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'На главную', 'antiterror-journal' ); ?>">
		<?php endif; ?>
			<span class="atj-logo__mark" aria-hidden="true">
				<svg viewBox="0 0 52 60"><path d="M26 2 49 11v18c0 13-8 24-23 29C11 53 3 42 3 29V11L26 2Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M26 16v20M16 26h20" fill="none" stroke="currentColor" stroke-width="2.4"/></svg>
			</span>
			<span class="atj-logo__text">
				<strong><?php echo esc_html( atj_mod( 'atj_kicker', __( 'Журнал', 'antiterror-journal' ) ) ); ?></strong>
				<b><?php echo esc_html( atj_mod( 'atj_logo_line', get_bloginfo( 'name' ) ) ); ?></b>
			</span>
		<?php if ( is_front_page() ) : ?>
			</div>
		<?php else : ?>
			</a>
		<?php endif; ?>

		<nav class="atj-nav" aria-label="<?php esc_attr_e( 'Основная навигация', 'antiterror-journal' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'atj-nav__list',
						'depth'          => 2,
					)
				);
			} else {
				echo '<ul class="atj-nav__list">';
				foreach ( atj_default_nav_items() as $item ) {
					printf( '<li><a href="%s">%s</a></li>', esc_url( $item[0] ), esc_html( $item[1] ) );
				}
				echo '</ul>';
			}
			?>
		</nav>

		<div class="atj-header__actions">
			<button class="atj-icon-btn" type="button" data-search-open aria-expanded="false" aria-controls="atj-search" aria-label="<?php esc_attr_e( 'Открыть поиск', 'antiterror-journal' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 5 5" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
			</button>
			<button class="atj-icon-btn" type="button" data-theme-toggle aria-label="<?php esc_attr_e( 'Включить тёмную тему', 'antiterror-journal' ); ?>">
				<svg class="atj-icon-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.7 15.1A9 9 0 0 1 8.9 3.3 9 9 0 1 0 20.7 15.1Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
				<svg class="atj-icon-sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
			</button>
			<a class="atj-btn atj-btn--solid" href="<?php echo esc_url( home_url( '/#report' ) ); ?>"><?php esc_html_e( 'Сообщить', 'antiterror-journal' ); ?> <span aria-hidden="true">→</span></a>
			<button class="atj-burger" type="button" aria-expanded="false" aria-controls="atj-mobile" aria-label="<?php esc_attr_e( 'Открыть меню', 'antiterror-journal' ); ?>"><span></span><span></span></button>
		</div>
	</div>

	<div id="atj-mobile" class="atj-mobile" hidden>
		<nav class="atj-shell" aria-label="<?php esc_attr_e( 'Мобильная навигация', 'antiterror-journal' ); ?>">
			<?php foreach ( atj_default_nav_items() as $item ) : ?>
				<a href="<?php echo esc_url( $item[0] ); ?>"><?php echo esc_html( $item[1] ); ?></a>
			<?php endforeach; ?>
			<a href="<?php echo esc_url( home_url( '/#report' ) ); ?>"><?php esc_html_e( 'Передать информацию', 'antiterror-journal' ); ?></a>
		</nav>
	</div>
</header>

<div id="atj-search" class="atj-search" hidden>
	<div class="atj-search__panel">
		<p class="atj-kicker"><?php esc_html_e( 'Поиск по журналу', 'antiterror-journal' ); ?></p>
		<?php get_search_form(); ?>
		<button class="atj-search__close" type="button" data-search-close><?php esc_html_e( 'Закрыть', 'antiterror-journal' ); ?></button>
	</div>
</div>

<div id="content" class="atj-content">
