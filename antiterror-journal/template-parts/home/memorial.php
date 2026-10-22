<?php
/**
 * Heroes memorial.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heroes = atj_query_featured( 'atj_hero', 4 );
?>
<section id="memory" class="atj-section atj-memorial" aria-labelledby="memory-title" data-observe>
	<div class="atj-shell">
		<div class="atj-section__head atj-section__head--light">
			<div>
				<p class="atj-section-number atj-section-number--gold">02 / <?php esc_html_e( 'Память', 'antiterror-journal' ); ?></p>
				<h2 id="memory-title"><?php esc_html_e( 'Герои антитеррора', 'antiterror-journal' ); ?></h2>
			</div>
			<p><?php echo esc_html( atj_mod( 'atj_memorial_intro', __( 'Карточки памяти публикуются только по проверяемым сведениям.', 'antiterror-journal' ) ) ); ?></p>
		</div>

		<?php if ( $heroes->have_posts() ) : ?>
			<div class="atj-memorial__wall">
				<?php
				while ( $heroes->have_posts() ) :
					$heroes->the_post();
					get_template_part( 'template-parts/content/hero', 'card' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<p class="atj-section__more">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'atj_hero' ) ); ?>"><?php esc_html_e( 'Стена памяти', 'antiterror-journal' ); ?> <span aria-hidden="true">→</span></a>
			</p>
		<?php else : ?>
			<div class="atj-empty atj-empty--dark">
				<p><?php esc_html_e( 'Раздел памяти пуст, пока редакция не внесёт проверяемые карточки героев. Не публикуйте вымышленные имена.', 'antiterror-journal' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
