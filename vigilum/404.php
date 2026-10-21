<?php
/**
 * 404.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main interior">
	<div class="shell">
		<header class="archive-head">
			<p class="section-number">404</p>
			<h1><?php esc_html_e( 'Страница не найдена', 'vigilum' ); ?></h1>
			<p class="archive-desc"><?php esc_html_e( 'Материал снят, переименован или ещё не опубликован.', 'vigilum' ); ?></p>
			<p><a class="button button--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'На главную', 'vigilum' ); ?></a></p>
			<?php get_search_form(); ?>
		</header>
	</div>
</main>
<?php
get_footer();
