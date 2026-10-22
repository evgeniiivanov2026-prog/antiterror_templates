<?php
/**
 * Initiative fund.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$initiatives = atj_query_featured( 'atj_initiative', 3 );
?>
<section id="fund" class="atj-section atj-fund" aria-labelledby="fund-title" data-observe>
	<div class="atj-shell">
		<div class="atj-section__head">
			<div>
				<p class="atj-section-number">03 / <?php esc_html_e( 'Фонд', 'antiterror-journal' ); ?></p>
				<h2 id="fund-title"><?php esc_html_e( 'Голос за инициативу', 'antiterror-journal' ); ?></h2>
			</div>
			<p><?php echo esc_html( atj_mod( 'atj_fund_intro', __( 'Читатели голосуют за инициативы в сфере антитеррора.', 'antiterror-journal' ) ) ); ?></p>
		</div>

		<?php if ( $initiatives->have_posts() ) : ?>
			<div class="atj-ballot">
				<?php
				$index = 1;
				while ( $initiatives->have_posts() ) :
					$initiatives->the_post();
					set_query_var( 'atj_rank', $index );
					get_template_part( 'template-parts/content/initiative', 'card' );
					$index++;
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<p class="atj-section__more">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'atj_initiative' ) ); ?>"><?php esc_html_e( 'Все инициативы', 'antiterror-journal' ); ?> <span aria-hidden="true">→</span></a>
			</p>
		<?php else : ?>
			<div class="atj-empty">
				<p><?php esc_html_e( 'Фонд откроет голосование после модерации первых инициатив. Добавьте записи типа «Инициативы».', 'antiterror-journal' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
