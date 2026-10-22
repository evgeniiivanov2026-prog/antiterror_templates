<?php
/**
 * Hall of memory.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heroes = new WP_Query( array(
	'post_type'      => 'aegis_hero',
	'posts_per_page' => 8,
	'post_status'    => 'publish',
) );
?>
<section id="memory" class="band band--memory" aria-labelledby="memory-title">
	<div class="shell">
		<div class="band__head reveal">
			<div>
				<p class="section-kicker section-kicker--bronze"><?php esc_html_e( '03 / Память героев', 'aegis' ); ?></p>
				<h2 id="memory-title"><?php esc_html_e( 'Зал имён', 'aegis' ); ?></h2>
			</div>
			<p><?php esc_html_e( 'Сдержанный портретный ритм. Без счётчиков популярности и без сенсации. Публикуйте только согласованные материалы.', 'aegis' ); ?></p>
			<a class="band__more" href="<?php echo esc_url( get_post_type_archive_link( 'aegis_hero' ) ); ?>"><?php esc_html_e( 'Весь зал памяти', 'aegis' ); ?> <span aria-hidden="true">→</span></a>
		</div>
	</div>
	<?php if ( $heroes->have_posts() ) : ?>
		<div class="memory-rail" tabindex="0" aria-label="<?php esc_attr_e( 'Карточки памяти', 'aegis' ); ?>">
			<?php
			while ( $heroes->have_posts() ) :
				$heroes->the_post();
				get_template_part( 'template-parts/card', 'hero' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	<?php else : ?>
		<div class="shell"><p class="empty-note"><?php esc_html_e( 'Добавьте карточки типа «Герои» — зал памяти соберётся сам.', 'aegis' ); ?></p></div>
	<?php endif; ?>
</section>
