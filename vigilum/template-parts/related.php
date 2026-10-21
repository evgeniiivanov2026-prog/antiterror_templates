<?php
/**
 * Related journal posts.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cats = wp_get_post_categories( get_the_ID() );
$query = new WP_Query( array(
	'post_type'           => 'post',
	'posts_per_page'      => 3,
	'post__not_in'        => array( get_the_ID() ),
	'category__in'        => $cats,
	'ignore_sticky_posts' => true,
) );

if ( ! $query->have_posts() ) {
	$query = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'post__not_in'        => array( get_the_ID() ),
		'ignore_sticky_posts' => true,
	) );
}

if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="related" aria-labelledby="related-title">
	<div class="section-head">
		<p class="section-number"><?php esc_html_e( 'Далее', 'vigilum' ); ?></p>
		<h2 id="related-title"><?php esc_html_e( 'Читать следом', 'vigilum' ); ?></h2>
	</div>
	<div class="journal-grid journal-grid--3">
		<?php
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/card', 'post', array( 'variant' => 'compact' ) );
		}
		wp_reset_postdata();
		?>
	</div>
</section>
