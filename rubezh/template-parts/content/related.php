<?php
/**
 * Связанные материалы.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cats = wp_get_post_categories( get_the_ID() );
if ( ! $cats ) {
	return;
}
$related = new WP_Query( array(
	'post_type'      => 'post',
	'posts_per_page' => 3,
	'post__not_in'   => array( get_the_ID() ),
	'category__in'   => $cats,
) );
if ( ! $related->have_posts() ) {
	return;
}
?>
<section class="related" aria-labelledby="related-title">
	<p class="section-number"><?php esc_html_e( 'Ещё в выпуске', 'rubezh' ); ?></p>
	<h2 id="related-title"><?php esc_html_e( 'Связанные материалы', 'rubezh' ); ?></h2>
	<div class="journal-trio">
		<?php
		while ( $related->have_posts() ) :
			$related->the_post();
			get_template_part( 'template-parts/cards/post', null, array( 'size' => 'card' ) );
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
