<?php
/**
 * Magazine feed.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$featured = atj_query_posts( array( 'posts_per_page' => 1 ) );
$rest     = atj_query_posts(
	array(
		'posts_per_page' => 4,
		'offset'         => 1,
	)
);
?>
<section id="journal" class="atj-section atj-magazine" aria-labelledby="journal-title" data-observe>
	<div class="atj-shell">
		<div class="atj-section__head">
			<div>
				<p class="atj-section-number">01 / <?php esc_html_e( 'Журнал', 'antiterror-journal' ); ?></p>
				<h2 id="journal-title"><?php esc_html_e( 'Материалы выпуска', 'antiterror-journal' ); ?></h2>
			</div>
			<p><?php esc_html_e( 'Редакционные расследования, хроника, право и профилактика. Без сенсаций и вымышленных цифр.', 'antiterror-journal' ); ?></p>
		</div>

		<?php if ( $featured->have_posts() ) : ?>
			<div class="atj-bento">
				<?php
				while ( $featured->have_posts() ) :
					$featured->the_post();
					get_template_part( 'template-parts/content/card', 'featured' );
				endwhile;
				wp_reset_postdata();
				?>
				<div class="atj-bento__stack">
					<?php
					if ( $rest->have_posts() ) :
						while ( $rest->have_posts() ) :
							$rest->the_post();
							get_template_part( 'template-parts/content/card' );
						endwhile;
						wp_reset_postdata();
					endif;
					?>
				</div>
			</div>
			<p class="atj-section__more">
				<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/?post_type=post' ) ); ?>"><?php esc_html_e( 'Все материалы', 'antiterror-journal' ); ?> <span aria-hidden="true">→</span></a>
			</p>
		<?php else : ?>
			<div class="atj-empty">
				<p><?php esc_html_e( 'Здесь появятся материалы редакции. Добавьте записи WordPress — карточки журнала соберутся автоматически.', 'antiterror-journal' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
