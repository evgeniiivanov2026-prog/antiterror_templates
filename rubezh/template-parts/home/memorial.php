<?php
/**
 * Лента памяти на главной.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$query = new WP_Query(
	array(
		'post_type'      => 'hero',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'no_found_rows'  => true,
	)
);
$archive = get_post_type_archive_link( 'hero' );
?>
<section class="rz-section rz-memorial" id="pamyat" aria-labelledby="rz-memory-title">
	<div class="rz-shell">
		<div class="rz-section-head">
			<div>
				<p class="rz-index"><span>02</span> <?php esc_html_e( 'Память', 'rubezh' ); ?></p>
				<h2 id="rz-memory-title"><?php esc_html_e( 'Люди, которые держали рубеж', 'rubezh' ); ?></h2>
				<p class="rz-deck"><?php esc_html_e( 'Короткие проверенные справки. Без лишних подробностей и без непроверенных биографий.', 'rubezh' ); ?></p>
			</div>
			<?php if ( $archive ) : ?>
				<a class="rz-textlink" href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( 'Весь раздел', 'rubezh' ); ?></a>
			<?php endif; ?>
		</div>
		<?php if ( $query->have_posts() ) : ?>
			<div class="rz-rail" tabindex="0">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					get_template_part( 'template-parts/content/portrait' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/empty', 'hero' ); ?>
		<?php endif; ?>
	</div>
</section>
