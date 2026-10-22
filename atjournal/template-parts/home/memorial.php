<?php
/**
 * Memorial rail.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$query = new WP_Query(
	array(
		'post_type'      => 'atj_hero',
		'posts_per_page' => 8,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'no_found_rows'  => true,
	)
);
$archive = get_post_type_archive_link( 'atj_hero' );
?>
<section class="atj-memorial" id="pamyat" aria-labelledby="atj-memory-title">
	<div class="atj-shell">
		<div class="atj-section__head atj-section__head--light atj-reveal">
			<div>
				<p class="atj-num"><?php esc_html_e( '03  /  Память', 'atjournal' ); ?></p>
				<h2 id="atj-memory-title"><?php esc_html_e( 'Герои антитеррора', 'atjournal' ); ?></h2>
			</div>
			<p><?php esc_html_e( 'Архив, а не лента. Редакция публикует подтверждённые карточки и не собирает их из комментариев.', 'atjournal' ); ?></p>
			<?php if ( $archive ) : ?>
				<a class="atj-textlink atj-textlink--light" href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( 'Весь архив', 'atjournal' ); ?> <span aria-hidden="true">→</span></a>
			<?php endif; ?>
		</div>
		<?php if ( $query->have_posts() ) : ?>
			<div class="atj-railwrap">
				<div class="atj-rail" tabindex="0">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						get_template_part( 'template-parts/content', 'hero' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
				<div class="atj-rail__nav">
					<button type="button" data-rail="prev" aria-label="<?php esc_attr_e( 'Предыдущие карточки', 'atjournal' ); ?>">←</button>
					<button type="button" data-rail="next" aria-label="<?php esc_attr_e( 'Следующие карточки', 'atjournal' ); ?>">→</button>
				</div>
			</div>
		<?php else : ?>
			<div class="atj-empty atj-empty--inverse">
				<p><?php esc_html_e( 'Редакция ещё не опубликовала карточки памяти.', 'atjournal' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
