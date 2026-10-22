<?php
/**
 * Результаты поиска.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="rz-main">
	<header class="rz-shell rz-page-head">
		<?php rubezh_breadcrumbs(); ?>
		<p class="rz-index"><?php esc_html_e( 'Поиск', 'rubezh' ); ?></p>
		<h1>
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( '«%s»', 'rubezh' ),
				esc_html( get_search_query() )
			);
			?>
		</h1>
	</header>
	<?php get_template_part( 'template-parts/content/loop' ); ?>
</main>
<?php
get_footer();
