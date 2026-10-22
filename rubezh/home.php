<?php
/**
 * Лента записей, если главная назначена статически.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main id="primary" class="site-main shell interior">
	<header class="archive-head reveal">
		<p class="section-number"><?php esc_html_e( '01 / Журнал', 'rubezh' ); ?></p>
		<h1><?php echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) ?: __( 'Все материалы', 'rubezh' ) ); ?></h1>
		<p class="archive-head__lead"><?php esc_html_e( 'Аналитика, право, профилактика и репортажи выпуска.', 'rubezh' ); ?></p>
	</header>
	<?php if ( have_posts() ) : ?>
		<div class="journal-trio journal-trio--archive">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/cards/post', null, array( 'size' => 'card' ) );
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
