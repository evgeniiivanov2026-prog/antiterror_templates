<?php
/**
 * Фонд инициатив.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$max = rubezh_max_votes();
get_header();
?>
<main id="primary" class="site-main shell interior">
	<header class="archive-head reveal">
		<p class="section-number"><?php esc_html_e( '03 / Фонд инициатив', 'rubezh' ); ?></p>
		<h1><?php esc_html_e( 'Голосование граждан', 'rubezh' ); ?></h1>
		<p class="archive-head__lead"><?php esc_html_e( 'Один голос с устройства. Инициативы отсортированы по поддержке. Фонд рассматривает лидеров в правовом поле.', 'rubezh' ); ?></p>
	</header>
	<?php if ( have_posts() ) : ?>
		<div class="ballot-list">
			<?php
			$rank = 1;
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/cards/initiative', null, array( 'rank' => $rank, 'max' => $max ) );
				$rank++;
			endwhile;
			?>
		</div>
		<div class="pagination-box"><?php the_posts_pagination(); ?></div>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content/none' ); ?>
	<?php endif; ?>
</main>
<?php
get_footer();
