<?php
/**
 * Обложка выпуска и лента.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$query = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 7,
	)
);
$posts = $query->posts;
$lead  = $posts[0] ?? null;
$grid  = array_slice( $posts, 1, 3 );
$rows  = array_slice( $posts, 4, 3 );
$all   = rubezh_journal_url();
$show_all = untrailingslashit( $all ) !== untrailingslashit( home_url( '/' ) );
?>
<section class="rz-section rz-journal" id="letopis" aria-labelledby="rz-journal-title">
	<div class="rz-shell">
		<div class="rz-cover">
			<p class="rz-dateline"><?php echo esc_html( rubezh_dateline() ); ?></p>
			<p class="rz-index"><span>01</span> <?php esc_html_e( 'Летопись', 'rubezh' ); ?></p>
			<h1 class="rz-cover__title" id="rz-journal-title"><?php bloginfo( 'name' ); ?></h1>
			<p class="rz-cover__deck"><?php echo esc_html( rubezh_tagline() ); ?></p>
		</div>
		<?php if ( $lead ) : ?>
			<?php
			global $post;
			$post = $lead;
			setup_postdata( $post );
			get_template_part( 'template-parts/content/lead' );
			wp_reset_postdata();
			?>
		<?php else : ?>
			<div class="rz-empty">
				<p><?php esc_html_e( 'В летописи пока нет материалов. Первая публикация станет главным материалом выпуска.', 'rubezh' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( $grid ) : ?>
			<div class="rz-grid">
				<?php
				$number = 2;
				foreach ( $grid as $grid_post ) {
					$post = $grid_post;
					setup_postdata( $post );
					get_template_part(
						'template-parts/content/card',
						null,
						array(
							'variant' => 'grid',
							'index'   => sprintf( '%02d', $number ),
						)
					);
					++$number;
				}
				wp_reset_postdata();
				?>
			</div>
		<?php endif; ?>

		<?php if ( $rows ) : ?>
			<div class="rz-rows">
				<?php
				foreach ( $rows as $row_post ) {
					$post = $row_post;
					setup_postdata( $post );
					get_template_part(
						'template-parts/content/card',
						null,
						array( 'variant' => 'row' )
					);
				}
				wp_reset_postdata();
				?>
			</div>
		<?php endif; ?>

		<?php if ( $show_all ) : ?>
			<p class="rz-section-link"><a class="rz-textlink" href="<?php echo esc_url( $all ); ?>"><?php esc_html_e( 'Вся летопись', 'rubezh' ); ?></a></p>
		<?php endif; ?>
	</div>
</section>
