<?php
/**
 * Поиск и мобильное меню.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<dialog id="search-dialog" class="search-dialog" aria-labelledby="search-dialog-title">
	<div class="search-dialog__bar">
		<h2 id="search-dialog-title"><?php esc_html_e( 'Поиск по журналу', 'strazha' ); ?></h2>
		<button type="button" class="tool" data-dialog-close aria-label="<?php esc_attr_e( 'Закрыть', 'strazha' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
		</button>
	</div>
	<?php get_search_form(); ?>
	<p class="search-dialog__hint"><?php esc_html_e( 'Клавиша / открывает поиск', 'strazha' ); ?></p>
</dialog>

<dialog id="nav-dialog" class="nav-dialog" aria-label="<?php esc_attr_e( 'Меню', 'strazha' ); ?>">
	<div class="nav-dialog__bar shell">
		<?php strazha_brand( true ); ?>
		<button type="button" class="tool" data-dialog-close aria-label="<?php esc_attr_e( 'Закрыть меню', 'strazha' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
		</button>
	</div>
	<nav class="shell nav-dialog__links" aria-label="<?php esc_attr_e( 'Мобильная навигация', 'strazha' ); ?>">
		<?php strazha_navigation( 'primary' ); ?>
		<a class="nav-dialog__fund" href="<?php echo esc_url( get_post_type_archive_link( 'initiative' ) ); ?>"><?php esc_html_e( 'Фонд инициатив', 'strazha' ); ?></a>
	</nav>
</dialog>
