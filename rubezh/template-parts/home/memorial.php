<?php
/**
 * Стена памяти героев антитеррора.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$heroes = rubezh_query_heroes( 6 );
$title  = rubezh_option( 'rubezh_memorial_title', 'Имена, которые стоят между угрозой и людьми' );
?>
<section id="memory" class="section section--chapel" aria-labelledby="memory-title">
	<div class="shell">
		<div class="section__head section__head--light reveal">
			<div>
				<p class="section-number section-number--bronze"><?php esc_html_e( '02 / Память героев', 'rubezh' ); ?></p>
				<h2 id="memory-title"><?php echo esc_html( $title ); ?></h2>
			</div>
			<p><?php esc_html_e( 'Карточки памяти — имена, годы, служба. Публикуйте только проверяемые сведения и с уважением к семьям.', 'rubezh' ); ?></p>
			<a class="section__more section__more--light" href="<?php echo esc_url( rubezh_archive_link( 'hero' ) ); ?>"><?php esc_html_e( 'Вся стена', 'rubezh' ); ?> <span aria-hidden="true">→</span></a>
		</div>

		<?php if ( $heroes->have_posts() ) : ?>
			<div class="memorial-rail">
				<?php
				while ( $heroes->have_posts() ) :
					$heroes->the_post();
					get_template_part( 'template-parts/cards/hero' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<p class="empty-line empty-line--light"><?php esc_html_e( 'Добавьте карточки в типе записей «Герои памяти».', 'rubezh' ); ?></p>
		<?php endif; ?>
	</div>
</section>
