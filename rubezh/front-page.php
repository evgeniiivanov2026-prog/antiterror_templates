<?php
/**
 * Главная: обложка выпуска + журнал + память + фонд.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main foundation-home">
	<?php get_template_part( 'template-parts/home/cover' ); ?>
	<?php if ( rubezh_option( 'rubezh_show_journal', true ) ) : ?>
		<?php get_template_part( 'template-parts/home/journal' ); ?>
	<?php endif; ?>
	<?php if ( rubezh_option( 'rubezh_show_memorial', true ) ) : ?>
		<?php get_template_part( 'template-parts/home/memorial' ); ?>
	<?php endif; ?>
	<?php if ( rubezh_option( 'rubezh_show_fund', true ) ) : ?>
		<?php get_template_part( 'template-parts/home/fund' ); ?>
	<?php endif; ?>
	<?php if ( rubezh_option( 'rubezh_show_cta', true ) ) : ?>
		<?php get_template_part( 'template-parts/home/cta' ); ?>
	<?php endif; ?>
</main>
<?php
get_footer();
