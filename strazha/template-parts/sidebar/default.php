<?php
/**
 * Колонка по умолчанию, пока в неё не положили виджеты.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$initiatives = new WP_Query(
	array(
		'post_type'      => 'initiative',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'meta_query'     => array(
			array(
				'key'   => '_strazha_status',
				'value' => 'collecting',
			),
		),
		'meta_key'       => '_strazha_votes',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	)
);
?>
<section class="widget">
	<h2 class="widget__title"><?php esc_html_e( 'Поиск', 'strazha' ); ?></h2>
	<?php get_search_form(); ?>
</section>
<section class="widget">
	<h2 class="widget__title"><?php esc_html_e( 'Рубрики', 'strazha' ); ?></h2>
	<ul>
		<?php
		wp_list_categories(
			array(
				'title_li' => '',
				'number'   => 8,
			)
		);
		?>
	</ul>
</section>
<?php if ( $initiatives->have_posts() ) : ?>
	<section class="widget">
		<h2 class="widget__title"><?php esc_html_e( 'Открытые инициативы', 'strazha' ); ?></h2>
		<ul class="side-list">
			<?php
			while ( $initiatives->have_posts() ) :
				$initiatives->the_post();
				?>
				<li>
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					<span><?php echo esc_html( number_format_i18n( strazha_vote_count( get_the_ID() ) ) ); ?></span>
				</li>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</ul>
		<a class="textlink" href="<?php echo esc_url( get_post_type_archive_link( 'initiative' ) ); ?>"><?php esc_html_e( 'Весь фонд', 'strazha' ); ?></a>
	</section>
<?php endif; ?>
