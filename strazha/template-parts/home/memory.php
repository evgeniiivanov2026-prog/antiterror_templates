<?php
/**
 * Стена памяти на главной.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$query = new WP_Query(
	array(
		'post_type'      => 'hero',
		'post_status'    => 'publish',
		'posts_per_page' => strazha_int_mod( 'memory_count', 1, 16 ),
		'orderby'        => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
	)
);
?>
<section class="memory" id="memory">
	<div class="shell">
		<header class="section__head memory__head">
			<p class="section__num"><?php echo esc_html( strazha_mod( 'memory_kicker' ) ); ?></p>
			<div class="section__copy">
				<h2><?php echo esc_html( strazha_mod( 'memory_title' ) ); ?></h2>
				<p><?php echo esc_html( strazha_mod( 'memory_text' ) ); ?></p>
			</div>
			<?php if ( $query->have_posts() ) : ?>
				<div class="rail__nav">
					<button type="button" data-rail="memory-rail" data-dir="-1" aria-label="<?php esc_attr_e( 'Предыдущие имена', 'strazha' ); ?>">←</button>
					<button type="button" data-rail="memory-rail" data-dir="1" aria-label="<?php esc_attr_e( 'Следующие имена', 'strazha' ); ?>">→</button>
				</div>
			<?php endif; ?>
		</header>
		<?php if ( $query->have_posts() ) : ?>
			<div class="rail" id="memory-rail" tabindex="0">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					get_template_part( 'template-parts/content/hero' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<p class="memory__more"><a class="textlink textlink--inverse" href="<?php echo esc_url( get_post_type_archive_link( 'hero' ) ); ?>"><?php esc_html_e( 'Вся стена памяти', 'strazha' ); ?><span aria-hidden="true"> →</span></a></p>
		<?php else : ?>
			<div class="empty empty--inverse">
				<p><?php esc_html_e( 'Стена памяти пока пуста. Имена появятся после публикации записей типа «Герой».', 'strazha' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
