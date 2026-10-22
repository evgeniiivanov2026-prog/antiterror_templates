<?php
/**
 * Template Name: О фонде
 *
 * @package Aegis
 */

get_header();
?>
<main id="primary" class="site-main">
	<section class="band band--paper">
		<div class="shell archive-head">
			<p class="section-kicker"><?php esc_html_e( 'Фонд', 'aegis' ); ?></p>
			<h1><?php the_title(); ?></h1>
			<div class="entry-content">
				<?php
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
				?>
			</div>
		</div>
	</section>
	<?php get_template_part( 'template-parts/home', 'foundation' ); ?>
</main>
<?php
get_footer();
