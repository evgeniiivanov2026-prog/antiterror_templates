<?php
/**
 * Posts page.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$posts_page = (int) get_option( 'page_for_posts' );
$title      = $posts_page ? get_the_title( $posts_page ) : __( 'Журнал', 'atjournal' );
?>
<main id="content" class="atj-main">
	<?php
	atj_page_header(
		atj_mod( 'issue' ),
		$title ? $title : __( 'Журнал', 'atjournal' ),
		__( 'Материалы редакции. Без сенсаций и без оперативных подробностей.', 'atjournal' )
	);
	get_template_part( 'template-parts/loop', 'journal' );
	?>
</main>
<?php
get_footer();
