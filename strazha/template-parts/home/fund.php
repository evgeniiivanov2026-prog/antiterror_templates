<?php
/**
 * Фонд на главной.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$query = new WP_Query(
	array(
		'post_type'      => 'initiative',
		'post_status'    => 'publish',
		'posts_per_page' => strazha_int_mod( 'fund_count', 1, 8 ),
		'meta_query'     => array(
			'status_clause' => array(
				'key'   => '_strazha_status',
				'value' => 'collecting',
			),
			'votes_clause'  => array(
				'key'     => '_strazha_votes',
				'type'    => 'NUMERIC',
				'compare' => 'EXISTS',
			),
		),
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
<section class="section fund" id="fund">
	<div class="shell">
		<?php
		strazha_section_heading(
			(string) strazha_mod( 'fund_kicker' ),
			(string) strazha_mod( 'fund_title' ),
			(string) strazha_mod( 'fund_text' ),
			array(
				'url'   => get_post_type_archive_link( 'initiative' ),
				'label' => __( 'Все инициативы', 'strazha' ),
			)
		);
		?>
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
			<p class="fund__fine"><?php esc_html_e( 'Голос можно отозвать, пока инициатива открыта. Повторный голос с того же браузера не дублируется.', 'strazha' ); ?></p>
		<?php else : ?>
			<div class="empty">
				<p><?php esc_html_e( 'Сейчас нет инициатив, открытых для голосования. Новую можно добавить в разделе «Фонд инициатив» в консоли.', 'strazha' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
