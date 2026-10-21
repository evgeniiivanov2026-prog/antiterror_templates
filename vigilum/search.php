<?php
/**
 * Search.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main interior">
	<div class="shell">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<header class="archive-head">
			<p class="section-number"><?php esc_html_e( 'Поиск', 'vigilum' ); ?></p>
			<h1><?php printf( esc_html__( 'Результаты: %s', 'vigilum' ), '<em>' . esc_html( get_search_query() ) . '</em>' ); ?></h1>
			<?php get_search_form(); ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="journal-grid journal-grid--3">
				<?php
				while ( have_posts() ) {
					the_post();
					$type = get_post_type();
					if ( 'vigilum_hero' === $type ) {
						get_template_part( 'template-parts/card', 'hero' );
					} elseif ( 'vigilum_initiative' === $type ) {
						get_template_part( 'template-parts/card', 'initiative' );
					} else {
						get_template_part( 'template-parts/card', 'post', array( 'variant' => 'standard' ) );
					}
				}
				?>
			</div>
			<?php vigilum_pagination(); ?>
		<?php else : ?>
			<div class="empty-state"><p><?php esc_html_e( 'Ничего не найдено. Измените запрос.', 'vigilum' ); ?></p></div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
