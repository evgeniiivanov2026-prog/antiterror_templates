<?php
/**
 * Journal sidebar.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<aside class="atj-sidebar" aria-label="<?php esc_attr_e( 'Дополнительно', 'atjournal' ); ?>">
	<?php if ( is_active_sidebar( 'journal' ) ) : ?>
		<?php dynamic_sidebar( 'journal' ); ?>
	<?php else : ?>
		<section class="atj-widget">
			<h2 class="atj-widget__title"><?php esc_html_e( 'Поиск', 'atjournal' ); ?></h2>
			<?php get_search_form(); ?>
		</section>
		<section class="atj-widget">
			<h2 class="atj-widget__title"><?php esc_html_e( 'Рубрики', 'atjournal' ); ?></h2>
			<ul class="atj-widget__list">
				<?php
				wp_list_categories(
					array(
						'title_li' => '',
						'number'   => 8,
						'orderby'  => 'name',
					)
				);
				?>
			</ul>
		</section>
		<?php
		$ledger = new WP_Query(
			array(
				'post_type'      => 'atj_initiative',
				'posts_per_page' => 3,
				'meta_key'       => '_atj_vote_count',
				'orderby'        => array(
					'meta_value_num' => 'DESC',
					'date'           => 'DESC',
				),
				'no_found_rows'  => true,
			)
		);
		if ( $ledger->have_posts() ) :
			?>
			<section class="atj-widget">
				<h2 class="atj-widget__title"><?php esc_html_e( 'Инициативы', 'atjournal' ); ?></h2>
				<ul class="atj-widget__list">
					<?php
					while ( $ledger->have_posts() ) :
						$ledger->the_post();
						?>
						<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
					<?php endwhile; ?>
				</ul>
			</section>
			<?php
			wp_reset_postdata();
		endif;
		?>
	<?php endif; ?>
</aside>
