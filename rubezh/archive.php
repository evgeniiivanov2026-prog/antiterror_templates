<?php
/**
 * Архивы рубрик, меток, дат и авторов.
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
		<p class="rz-index"><span>01</span> <?php esc_html_e( 'Летопись', 'rubezh' ); ?></p>
		<h1><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<div class="rz-deck">', '</div>' ); ?>
	</header>
	<?php get_template_part( 'template-parts/content/loop' ); ?>
</main>
<?php
get_footer();
