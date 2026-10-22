<?php
/**
 * Composed front: journal + memory + initiatives + foundation.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main">
	<?php get_template_part( 'template-parts/home', 'hero' ); ?>

	<?php if ( get_theme_mod( 'aegis_show_journal', true ) ) : ?>
		<?php get_template_part( 'template-parts/home', 'journal' ); ?>
	<?php endif; ?>

	<?php if ( get_theme_mod( 'aegis_show_memory', true ) ) : ?>
		<?php get_template_part( 'template-parts/home', 'memory' ); ?>
	<?php endif; ?>

	<?php if ( get_theme_mod( 'aegis_show_initiatives', true ) ) : ?>
		<?php get_template_part( 'template-parts/home', 'initiatives' ); ?>
	<?php endif; ?>

	<?php if ( get_theme_mod( 'aegis_show_foundation', true ) ) : ?>
		<?php get_template_part( 'template-parts/home', 'foundation' ); ?>
	<?php endif; ?>
</main>
<?php
get_footer();
