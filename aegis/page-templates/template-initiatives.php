<?php
/**
 * Template Name: Фонд инициатив
 *
 * @package Aegis
 */

get_header();

$initiatives = new WP_Query( array(
	'post_type'      => 'aegis_initiative',
	'posts_per_page' => 24,
	'post_status'    => 'publish',
	'meta_key'       => '_aegis_votes',
	'orderby'        => 'meta_value_num',
	'order'          => 'DESC',
) );
?>
<main id="primary" class="site-main interior">
	<div class="shell">
		<header class="archive-head">
			<p class="section-kicker"><?php esc_html_e( 'Фонд инициатив', 'aegis' ); ?></p>
			<h1><?php the_title(); ?></h1>
			<?php if ( get_the_content() ) : ?>
				<div class="archive-head__lead entry-content"><?php the_content(); ?></div>
			<?php endif; ?>
		</header>
		<?php if ( $initiatives->have_posts() ) : ?>
			<div class="ballot-list">
				<?php
				$i = 1;
				while ( $initiatives->have_posts() ) :
					$initiatives->the_post();
					get_template_part( 'template-parts/card', 'initiative', array( 'index' => $i ) );
					$i++;
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php get_template_part( 'template-parts/home', 'foundation' ); ?>
</main>
<?php
get_footer();
