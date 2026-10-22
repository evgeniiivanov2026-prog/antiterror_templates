<?php
/**
 * Template Name: Стена памяти
 * Template Post Type: page
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$query = new WP_Query(
	array(
		'post_type'      => 'hero',
		'post_status'    => 'publish',
		'posts_per_page' => (int) apply_filters( 'strazha_heroes_per_page', 12 ),
		'paged'          => $paged,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
	)
);
?>
<main id="main" class="sheet sheet--memory">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<header class="pagehead shell">
			<?php strazha_breadcrumbs(); ?>
			<h1><?php the_title(); ?></h1>
			<?php if ( get_the_content() ) : ?>
				<div class="pagehead__lead entry__content"><?php the_content(); ?></div>
			<?php endif; ?>
		</header>
		<?php
	endwhile;
	?>
	<div class="shell">
		<?php if ( $query->have_posts() ) : ?>
			<div class="wall">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					get_template_part( 'template-parts/content/hero' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<?php
			$links = paginate_links(
				array(
					'total'     => $query->max_num_pages,
					'current'   => $paged,
					'type'      => 'list',
					'prev_text' => __( 'Назад', 'strazha' ),
					'next_text' => __( 'Дальше', 'strazha' ),
				)
			);
			if ( $links ) {
				echo '<nav class="navigation pagination">' . wp_kses_post( $links ) . '</nav>';
			}
			?>
		<?php else : ?>
			<div class="empty"><p><?php esc_html_e( 'Имён пока нет.', 'strazha' ); ?></p></div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
