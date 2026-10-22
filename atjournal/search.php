<?php
/**
 * Search results across the journal, memory, and ledger.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="content" class="atj-main">
	<?php
	atj_page_header(
		__( 'Поиск', 'atjournal' ),
		sprintf( '«%s»', get_search_query() ),
		sprintf(
			/* translators: %s: result count */
			__( 'Найдено: %s', 'atjournal' ),
			number_format_i18n( (int) $GLOBALS['wp_query']->found_posts )
		)
	);
	get_template_part( 'template-parts/loop', 'journal' );
	?>
</main>
<?php
get_footer();
