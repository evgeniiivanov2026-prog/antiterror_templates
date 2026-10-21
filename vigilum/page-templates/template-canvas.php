<?php
/**
 * Template Name: Холст (без шапки раздела)
 * Template Post Type: page
 *
 * Full-width Gutenberg canvas for landing pages.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main canvas">
	<?php
	while ( have_posts() ) {
		the_post();
		echo '<div class="entry-content">';
		the_content();
		echo '</div>';
	}
	?>
</main>
<?php
get_footer();
