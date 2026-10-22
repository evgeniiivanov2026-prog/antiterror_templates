<?php
/**
 * Template Name: Зал памяти
 *
 * @package Aegis
 */

get_header();

$heroes = new WP_Query( array(
	'post_type'      => 'aegis_hero',
	'posts_per_page' => 24,
	'post_status'    => 'publish',
) );
?>
<main id="primary" class="site-main interior interior--memory">
	<div class="shell">
		<header class="archive-head">
			<p class="section-kicker section-kicker--bronze"><?php esc_html_e( 'Память героев', 'aegis' ); ?></p>
			<h1><?php the_title(); ?></h1>
			<?php if ( get_the_content() ) : ?>
				<div class="archive-head__lead entry-content"><?php the_content(); ?></div>
			<?php endif; ?>
		</header>
		<?php if ( $heroes->have_posts() ) : ?>
			<div class="memory-grid">
				<?php
				while ( $heroes->have_posts() ) :
					$heroes->the_post();
					get_template_part( 'template-parts/card', 'hero' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
