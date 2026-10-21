<?php
/**
 * Initiatives archive with vote.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$order = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'votes';
if ( ! in_array( $order, array( 'votes', 'date' ), true ) ) {
	$order = 'votes';
}

$paged = max( 1, (int) get_query_var( 'paged' ) );
$args  = array(
	'post_type'      => 'vigilum_initiative',
	'posts_per_page' => 9,
	'paged'          => $paged,
	'post_status'    => 'publish',
	'order'          => 'DESC',
);
if ( 'votes' === $order ) {
	$args['meta_key'] = '_vigilum_votes';
	$args['orderby']  = 'meta_value_num';
} else {
	$args['orderby'] = 'date';
}
$query = new WP_Query( $args );
?>
<main id="primary" class="site-main interior initiatives-archive">
	<div class="shell">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<header class="archive-head">
			<p class="section-number">03 / <?php esc_html_e( 'Фонд', 'vigilum' ); ?></p>
			<h1><?php esc_html_e( 'Гражданские инициативы', 'vigilum' ); ?></h1>
			<p class="archive-desc"><?php esc_html_e( 'Один голос на инициативу. Поддержите то, что укрепляет безопасность в правовом поле.', 'vigilum' ); ?></p>
			<nav class="filter-row" aria-label="<?php esc_attr_e( 'Сортировка', 'vigilum' ); ?>">
				<a href="<?php echo esc_url( add_query_arg( 'sort', 'votes', get_post_type_archive_link( 'vigilum_initiative' ) ) ); ?>"<?php echo 'votes' === $order ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'По голосам', 'vigilum' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( 'sort', 'date', get_post_type_archive_link( 'vigilum_initiative' ) ) ); ?>"<?php echo 'date' === $order ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Новые', 'vigilum' ); ?></a>
			</nav>
		</header>
		<?php if ( $query->have_posts() ) : ?>
			<div class="initiative-grid">
				<?php
				while ( $query->have_posts() ) {
					$query->the_post();
					get_template_part( 'template-parts/card', 'initiative' );
				}
				wp_reset_postdata();
				?>
			</div>
			<?php
			echo wp_kses_post( paginate_links( array(
				'total'   => $query->max_num_pages,
				'current' => $paged,
			) ) ?: '' );
			?>
		<?php else : ?>
			<div class="empty-state">
				<p><?php esc_html_e( 'Инициатив пока нет. Первая карточка появится после публикации в «Фонде инициатив».', 'vigilum' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
