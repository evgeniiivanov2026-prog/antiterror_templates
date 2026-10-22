<?php
/**
 * Not found.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="content" class="atj-main">
	<header class="atj-pagehead">
		<div class="atj-shell">
			<?php atj_breadcrumbs(); ?>
			<p class="atj-kicker"><?php esc_html_e( '404', 'atjournal' ); ?></p>
			<h1><?php esc_html_e( 'Такой страницы нет', 'atjournal' ); ?></h1>
			<p class="atj-pagehead__lead"><?php esc_html_e( 'Адрес устарел или был набран с ошибкой. Поиск смотрит журнал, память и инициативы.', 'atjournal' ); ?></p>
			<?php get_search_form(); ?>
			<ul class="atj-quicklinks">
				<?php foreach ( atj_nav_links() as $link ) : ?>
					<li><a href="<?php echo esc_url( $link[1] ); ?>"><?php echo esc_html( $link[0] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</header>
</main>
<?php
get_footer();
