<?php
/**
 * Лента на главной.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cover = strazha_cover_id();
$query = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => strazha_int_mod( 'journal_count', 1, 8 ),
		'post__not_in'        => $cover ? array( $cover ) : array(),
		'ignore_sticky_posts' => true,
	)
);
?>
<section class="section journal" id="journal">
	<div class="shell">
		<?php
		strazha_section_heading(
			__( '02 / Журнал', 'strazha' ),
			__( 'Материалы выпуска', 'strazha' ),
			__( 'Хроника, объяснения и городские заметки этого выпуска.', 'strazha' ),
			array(
				'url'   => strazha_journal_url(),
				'label' => __( 'Вся лента', 'strazha' ),
			)
		);
		?>
		<?php if ( $query->have_posts() ) : ?>
			<div class="bento">
				<?php
				$query->the_post();
				get_template_part(
					'template-parts/content/card',
					null,
					array( 'variant' => 'lead' )
				);
				if ( $query->have_posts() ) :
					?>
					<div class="bento__stack">
						<?php
						while ( $query->have_posts() ) :
							$query->the_post();
							get_template_part(
								'template-parts/content/card',
								null,
								array( 'variant' => 'compact' )
							);
						endwhile;
						?>
					</div>
					<?php
				endif;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<div class="empty">
				<p><?php esc_html_e( 'Лента ждёт первые материалы. Опубликуйте запись — и она встанет в сетку выпуска.', 'strazha' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
