<?php
/**
 * Template Name: Фонд инициатив
 * Template Post Type: page
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$paged  = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$status = isset( $_GET['istatus'] ) ? sanitize_key( wp_unslash( $_GET['istatus'] ) ) : '';
$meta   = array(
	'votes_clause' => array(
		'key'     => '_strazha_votes',
		'type'    => 'NUMERIC',
		'compare' => 'EXISTS',
	),
);
if ( isset( strazha_statuses()[ $status ] ) ) {
	$meta['status_clause'] = array(
		'key'   => '_strazha_status',
		'value' => $status,
	);
}

$query = new WP_Query(
	array(
		'post_type'      => 'initiative',
		'post_status'    => 'publish',
		'posts_per_page' => (int) apply_filters( 'strazha_initiatives_per_page', 9 ),
		'paged'          => $paged,
		'meta_query'     => $meta,
		'orderby'        => array(
			'votes_clause' => 'DESC',
			'date'         => 'DESC',
		),
	)
);
if ( $query->have_posts() ) {
	strazha_prime_ballots( wp_list_pluck( $query->posts, 'ID' ) );
}
?>
<main id="main" class="sheet">
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
			<?php strazha_status_filters( get_permalink() ); ?>
		</header>
		<?php
	endwhile;
	?>
	<div class="shell bills-wrap">
		<?php if ( $query->have_posts() ) : ?>
			<div class="bills">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					get_template_part( 'template-parts/content/initiative' );
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
					'add_args'  => $status ? array( 'istatus' => $status ) : false,
				)
			);
			if ( $links ) {
				echo '<nav class="navigation pagination">' . wp_kses_post( $links ) . '</nav>';
			}
			?>
		<?php else : ?>
			<div class="empty"><p><?php esc_html_e( 'В этой выборке инициатив нет.', 'strazha' ); ?></p></div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
