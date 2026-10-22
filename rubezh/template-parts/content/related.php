<?php
/**
 * Ещё три материала.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories = wp_get_post_categories( get_the_ID() );
$args       = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 3,
	'post__not_in'        => array( get_the_ID() ),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);
if ( $categories ) {
	$args['category__in'] = $categories;
}
$query = new WP_Query( $args );
if ( ! $query->have_posts() ) {
	unset( $args['category__in'] );
	$query = new WP_Query( $args );
}
if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="rz-shell rz-related" aria-labelledby="rz-related-title">
	<h2 id="rz-related-title"><?php esc_html_e( 'В этом выпуске ещё', 'rubezh' ); ?></h2>
	<div class="rz-grid">
		<?php
		$index = 1;
		while ( $query->have_posts() ) :
			$query->the_post();
			get_template_part(
				'template-parts/content/card',
				null,
				array(
					'variant' => 'grid',
					'index'   => sprintf( '%02d', $index ),
				)
			);
			++$index;
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
