<?php
/**
 * Fallback index.
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
		__( 'Журнал', 'atjournal' ),
		get_bloginfo( 'name' ),
		get_bloginfo( 'description' )
	);
	get_template_part( 'template-parts/loop', 'journal' );
	?>
</main>
<?php
get_footer();
