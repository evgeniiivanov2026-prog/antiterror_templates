<?php
/**
 * Колонка, пока редакция не собрала виджеты.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$initiatives = new WP_Query(
	array(
		'post_type'      => 'initiative',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'no_found_rows'  => true,
		'meta_key'       => '_rubezh_status',
		'meta_value'     => 'open',
	)
);
$heroes = wp_count_posts( 'hero' );
$hero_count = isset( $heroes->publish ) ? (int) $heroes->publish : 0;
?>
<section class="rz-widget">
	<h2 class="rz-widget__title"><?php esc_html_e( 'Открытые инициативы', 'rubezh' ); ?></h2>
	<?php if ( $initiatives->have_posts() ) : ?>
		<ul class="rz-side__list">
			<?php
			while ( $initiatives->have_posts() ) :
				$initiatives->the_post();
				?>
				<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
			<?php endwhile; ?>
		</ul>
	<?php else : ?>
		<p><?php esc_html_e( 'Инициатив пока нет.', 'rubezh' ); ?></p>
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
</section>
<section class="rz-widget">
	<h2 class="rz-widget__title"><?php esc_html_e( 'Память', 'rubezh' ); ?></h2>
	<p>
		<?php
		echo esc_html(
			sprintf(
				/* translators: %d: number of memorial cards */
				__( 'В разделе %d записей.', 'rubezh' ),
				$hero_count
			)
		);
		?>
	</p>
	<a class="rz-textlink" href="<?php echo esc_url( get_post_type_archive_link( 'hero' ) ?: home_url( '/pamyat/' ) ); ?>"><?php esc_html_e( 'Открыть раздел', 'rubezh' ); ?></a>
</section>
