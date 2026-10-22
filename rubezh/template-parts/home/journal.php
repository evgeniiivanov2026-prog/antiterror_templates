<?php
/**
 * Журнальная мозаика выпуска — язык JournalX: feature + stack + trio.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$feature = rubezh_query_posts( array( 'posts_per_page' => 1 ) );
$stack   = rubezh_query_posts( array( 'posts_per_page' => 2, 'offset' => 1 ) );
$trio    = rubezh_query_posts( array( 'posts_per_page' => 3, 'offset' => 3 ) );
?>
<section id="journal" class="section section--paper" aria-labelledby="journal-title">
	<div class="shell">
		<div class="section__head reveal">
			<div>
				<p class="section-number"><?php esc_html_e( '01 / Журнал', 'rubezh' ); ?></p>
				<h2 id="journal-title"><?php esc_html_e( 'Выпуск: факты вместо шума', 'rubezh' ); ?></h2>
			</div>
			<p><?php esc_html_e( 'Мозаика материалов разного масштаба — как в JournalX: обложка, колонка, карточки. Аналитика, право, профилактика, репортаж.', 'rubezh' ); ?></p>
			<a class="section__more" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/?post_type=post' ) ); ?>"><?php esc_html_e( 'Все материалы', 'rubezh' ); ?> <span aria-hidden="true">→</span></a>
		</div>

		<?php if ( $feature->have_posts() ) : ?>
			<div class="journal-mosaic">
				<div class="journal-mosaic__feature reveal">
					<?php
					$feature->the_post();
					get_template_part( 'template-parts/cards/post', null, array( 'size' => 'feature' ) );
					wp_reset_postdata();
					?>
				</div>
				<div class="journal-mosaic__stack">
					<?php
					while ( $stack->have_posts() ) :
						$stack->the_post();
						get_template_part( 'template-parts/cards/post', null, array( 'size' => 'stack' ) );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $trio->have_posts() ) : ?>
			<div class="journal-trio">
				<?php
				while ( $trio->have_posts() ) :
					$trio->the_post();
					get_template_part( 'template-parts/cards/post', null, array( 'size' => 'card' ) );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<p class="empty-line"><?php esc_html_e( 'Здесь появятся материалы выпуска. Добавьте записи в «Записи».', 'rubezh' ); ?></p>
		<?php endif; ?>
	</div>
</section>
