<?php
/**
 * Category, tag, author, and date archives.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( is_category() ) {
	$kicker = __( 'Рубрика', 'atjournal' );
	$title  = single_cat_title( '', false );
} elseif ( is_tag() ) {
	$kicker = __( 'Метка', 'atjournal' );
	$title  = single_tag_title( '', false );
} elseif ( is_author() ) {
	$kicker = __( 'Автор', 'atjournal' );
	$user   = get_queried_object();
	$title  = ( $user instanceof WP_User ) ? $user->display_name : '';
} else {
	$kicker = __( 'Архив', 'atjournal' );
	$title  = wp_strip_all_tags( get_the_archive_title() );
}
$description = wp_strip_all_tags( (string) get_the_archive_description() );
?>
<main id="content" class="atj-main">
	<?php
	atj_page_header( $kicker, $title, $description );
	get_template_part( 'template-parts/loop', 'journal' );
	?>
</main>
<?php
get_footer();
