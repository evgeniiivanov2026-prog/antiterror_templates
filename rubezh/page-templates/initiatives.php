<?php
/**
 * Template Name: Инициативы
 * Template Post Type: page
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$query = new WP_Query(
	array(
		'post_type'      => 'initiative',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	)
);
?>
<main id="main" class="rz-main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<header class="rz-shell rz-page-head">
			<?php rubezh_breadcrumbs(); ?>
			<p class="rz-index"><span>03</span> <?php esc_html_e( 'Фонд инициатив', 'rubezh' ); ?></p>
			<h1><?php the_title(); ?></h1>
			<?php if ( trim( (string) get_the_content() ) ) : ?>
				<div class="rz-deck rz-entry"><?php the_content(); ?></div>
			<?php endif; ?>
		</header>
		<?php
	endwhile;
	?>
	<div class="rz-shell">
		<?php if ( $query->have_posts() ) : ?>
			<?php rubezh_render_ballots( $query ); ?>
			<p class="rz-disclaimer"><?php esc_html_e( 'Голос — общественная поддержка, а не официальное решение.', 'rubezh' ); ?></p>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/empty', 'initiative' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
