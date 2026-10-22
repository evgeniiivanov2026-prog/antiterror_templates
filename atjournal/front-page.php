<?php
/**
 * Homepage: institution, journal, memorial, ledger.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$lead    = atj_lead_query();
$lead_id = ( $lead->have_posts() && isset( $lead->posts[0] ) ) ? (int) $lead->posts[0]->ID : 0;
?>
<main id="content" class="atj-main atj-main--home">
	<?php get_template_part( 'template-parts/home/hero', null, array( 'query' => $lead ) ); ?>
	<?php get_template_part( 'template-parts/home/principles' ); ?>
	<?php get_template_part( 'template-parts/home/journal', null, array( 'exclude' => $lead_id ) ); ?>
	<?php get_template_part( 'template-parts/home/memorial' ); ?>
	<?php get_template_part( 'template-parts/home/initiatives' ); ?>
	<?php get_template_part( 'template-parts/home/directions' ); ?>
	<?php get_template_part( 'template-parts/home/signal' ); ?>

	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			if ( atj_has_visible_content() ) :
				?>
				<section class="atj-section">
					<div class="atj-shell entry-content">
						<?php the_content(); ?>
					</div>
				</section>
				<?php
			endif;
		endwhile;
	endif;
	?>
</main>
<?php
get_footer();
