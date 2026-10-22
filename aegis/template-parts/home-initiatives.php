<?php
/**
 * Foundation ballot.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$initiatives = new WP_Query( array(
	'post_type'      => 'aegis_initiative',
	'posts_per_page' => 6,
	'post_status'    => 'publish',
	'meta_key'       => '_aegis_votes',
	'orderby'        => 'meta_value_num',
	'order'          => 'DESC',
) );
?>
<section id="initiatives" class="band band--ballot" aria-labelledby="initiatives-title">
	<div class="shell">
		<div class="band__head reveal">
			<div>
				<p class="section-kicker"><?php esc_html_e( '04 / Фонд инициатив', 'aegis' ); ?></p>
				<h2 id="initiatives-title"><?php esc_html_e( 'Голосование граждан', 'aegis' ); ?></h2>
			</div>
			<p><?php esc_html_e( 'Один голос на инициативу. Это приоритет фонда, а не рейтинг популярности. Заявки проходят модерацию.', 'aegis' ); ?></p>
			<a class="band__more" href="<?php echo esc_url( get_post_type_archive_link( 'aegis_initiative' ) ); ?>"><?php esc_html_e( 'Все инициативы', 'aegis' ); ?> <span aria-hidden="true">→</span></a>
		</div>

		<?php if ( $initiatives->have_posts() ) : ?>
			<div class="ballot-list">
				<?php
				$i = 1;
				while ( $initiatives->have_posts() ) :
					$initiatives->the_post();
					get_template_part( 'template-parts/card', 'initiative', array( 'index' => $i ) );
					$i++;
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'Опубликуйте инициативы — здесь появится гражданский бюллетень.', 'aegis' ); ?></p>
		<?php endif; ?>
	</div>
</section>
