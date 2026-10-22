<?php
/**
 * Липкая шапка.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header class="rz-bar">
	<div class="rz-shell rz-bar__inner">
		<div class="rz-brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php elseif ( is_front_page() ) : ?>
				<?php echo rubezh_icon( 'mark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'На главную', 'rubezh' ); ?>"><?php echo rubezh_icon( 'mark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			<?php endif; ?>
			<?php if ( is_front_page() ) : ?>
				<span class="rz-brand__name"><?php bloginfo( 'name' ); ?></span>
			<?php else : ?>
				<a class="rz-brand__name" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
		</div>

		<nav id="rz-nav" class="rz-nav" aria-label="<?php esc_attr_e( 'Основное меню', 'rubezh' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'rz-nav__list',
					'fallback_cb'    => 'rubezh_primary_fallback',
					'depth'          => 2,
				)
			);
			?>
		</nav>

		<div class="rz-tools">
			<a class="rz-bar__cta" href="<?php echo esc_url( get_post_type_archive_link( 'initiative' ) ?: home_url( '/iniciativy/' ) ); ?>"><?php esc_html_e( 'К голосованию', 'rubezh' ); ?></a>
			<button class="rz-tool" type="button" data-search-open aria-label="<?php esc_attr_e( 'Открыть поиск', 'rubezh' ); ?>">
				<?php echo rubezh_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<button class="rz-tool" type="button" data-theme-toggle aria-pressed="false" data-label-dark="<?php esc_attr_e( 'Включить тёмную тему', 'rubezh' ); ?>" data-label-light="<?php esc_attr_e( 'Включить светлую тему', 'rubezh' ); ?>" aria-label="<?php esc_attr_e( 'Включить тёмную тему', 'rubezh' ); ?>">
				<?php
				echo rubezh_icon( 'moon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo rubezh_icon( 'sun' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</button>
			<button class="rz-nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="rz-nav" aria-label="<?php esc_attr_e( 'Открыть меню', 'rubezh' ); ?>"><span></span><span></span></button>
		</div>
	</div>
</header>
<?php if ( rubezh_has_samples() ) : ?>
	<div class="rz-sample-banner">
		<div class="rz-shell">
			<?php esc_html_e( 'На сайте стоят демонстрационные материалы шаблона. Их нужно заменить проверенными сведениями. Карточки памяти с пометкой «Образец» не описывают реальных людей.', 'rubezh' ); ?>
		</div>
	</div>
<?php endif; ?>
<?php if ( is_front_page() && ! in_array( 'journal', rubezh_active_sections(), true ) ) : ?>
	<h1 class="rz-sr"><?php bloginfo( 'name' ); ?></h1>
<?php endif; ?>
<dialog id="rz-search" class="rz-dialog">
	<form class="rz-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<button class="rz-tool rz-dialog__close" type="button" data-search-close aria-label="<?php esc_attr_e( 'Закрыть поиск', 'rubezh' ); ?>">×</button>
		<label class="rz-sr" for="rz-dialog-search"><?php esc_html_e( 'Поиск', 'rubezh' ); ?></label>
		<input id="rz-dialog-search" type="search" name="s" placeholder="<?php esc_attr_e( 'Имя, материал, инициатива', 'rubezh' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
		<button class="rz-button" type="submit"><?php esc_html_e( 'Найти', 'rubezh' ); ?></button>
	</form>
</dialog>
