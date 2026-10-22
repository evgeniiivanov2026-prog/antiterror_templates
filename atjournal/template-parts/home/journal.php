<?php
/**
 * Magazine spread.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$exclude = isset( $args['exclude'] ) ? (int) $args['exclude'] : 0;
$query   = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 4,
		'post__not_in'        => $exclude ? array( $exclude ) : array(),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
?>
<section class="atj-section atj-journal" id="journal" aria-labelledby="atj-journal-title">
	<div class="atj-shell">
		<div class="atj-section__head atj-reveal">
			<div>
				<p class="atj-num"><?php esc_html_e( '02  /  Журнал', 'atjournal' ); ?></p>
				<h2 id="atj-journal-title"><?php esc_html_e( 'Материалы выпуска', 'atjournal' ); ?></h2>
			</div>
			<p><?php esc_html_e( 'Проверка важнее скорости. В ленту не попадают слухи, кадры с места событий и догадки о виновных.', 'atjournal' ); ?></p>
			<a class="atj-textlink" href="<?php echo esc_url( atj_journal_url() ); ?>"><?php esc_html_e( 'Все материалы', 'atjournal' ); ?> <span aria-hidden="true">→</span></a>
		</div>
		<?php if ( $query->have_posts() ) : ?>
			<div class="atj-stack">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					get_template_part( 'template-parts/content', 'card', array( 'variant' => 'stack', 'heading' => 'h3' ) );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<div class="atj-empty"><p><?php esc_html_e( 'Дальше в выпуске пока ничего нет.', 'atjournal' ); ?></p></div>
		<?php endif; ?>
	</div>
</section>
