<?php
/**
 * Лента записей и запасной шаблон.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="rz-main">
	<?php if ( is_home() && ! is_front_page() ) : ?>
		<header class="rz-shell rz-page-head">
			<p class="rz-index"><span>01</span> <?php esc_html_e( 'Летопись', 'rubezh' ); ?></p>
			<h1><?php single_post_title(); ?></h1>
		</header>
	<?php endif; ?>
	<?php get_template_part( 'template-parts/content/loop' ); ?>
</main>
<?php
get_footer();
