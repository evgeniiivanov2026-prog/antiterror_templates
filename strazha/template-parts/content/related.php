<?php
/**
 * Похожие материалы.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current = get_the_ID();
$cats    = wp_get_post_categories( $current );
$args    = array(
	'post_type'           => 'post',
	'posts_per_page'      => 3,
	'post__not_in'        => array( $current ),
	'ignore_sticky_posts' => true,
);
if ( $cats ) {
	$args['category__in'] = $cats;
}
$related = new WP_Query( $args );
if ( ! $related->have_posts() && $cats ) {
	unset( $args['category__in'] );
	$related = new WP_Query( $args );
}
if ( ! $related->have_posts() ) {
	return;
}
?>
<section class="related" aria-labelledby="related-title">
	<h2 id="related-title"><?php esc_html_e( 'Ещё в журнале', 'strazha' ); ?></h2>
	<div class="cards">
		<?php
		while ( $related->have_posts() ) :
			$related->the_post();
			get_template_part( 'template-parts/content/card' );
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
