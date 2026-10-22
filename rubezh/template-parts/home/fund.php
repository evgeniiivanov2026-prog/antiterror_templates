<?php
/**
 * Фонд инициатив с голосованием.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$items = rubezh_query_initiatives( 4 );
$max   = rubezh_max_votes();
$title = rubezh_option( 'rubezh_fund_title', 'Голосуйте за инициативы антитеррора' );
?>
<section id="fund" class="section section--paper" aria-labelledby="fund-title">
	<div class="shell">
		<div class="section__head reveal">
			<div>
				<p class="section-number"><?php esc_html_e( '03 / Фонд инициатив', 'rubezh' ); ?></p>
				<h2 id="fund-title"><?php echo esc_html( $title ); ?></h2>
			</div>
			<p><?php esc_html_e( 'Один голос с устройства. Лидер рейтинга рассматривается фондом. Без регистрации, без давления, в правовом поле.', 'rubezh' ); ?></p>
			<a class="section__more" href="<?php echo esc_url( rubezh_archive_link( 'initiative' ) ); ?>"><?php esc_html_e( 'Все инициативы', 'rubezh' ); ?> <span aria-hidden="true">→</span></a>
		</div>

		<?php if ( $items->have_posts() ) : ?>
			<div class="ballot-list">
				<?php
				$rank = 1;
				while ( $items->have_posts() ) :
					$items->the_post();
					get_template_part( 'template-parts/cards/initiative', null, array( 'rank' => $rank, 'max' => $max ) );
					$rank++;
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<p class="empty-line"><?php esc_html_e( 'Добавьте инициативы в типе записей «Инициативы».', 'rubezh' ); ?></p>
		<?php endif; ?>
	</div>
</section>
