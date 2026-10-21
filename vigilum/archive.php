<?php
/**
 * Generic archive (categories, taxonomies).
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$is_hero = is_tax( 'hero_role' );
$is_init = is_tax( 'initiative_topic' ) || is_tax( 'initiative_status' );
?>
<main id="primary" class="site-main interior">
	<div class="shell">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<header class="archive-head">
			<p class="section-number"><?php echo $is_hero ? esc_html__( 'Память', 'vigilum' ) : ( $is_init ? esc_html__( 'Фонд', 'vigilum' ) : esc_html__( 'Журнал', 'vigilum' ) ); ?></p>
			<h1><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
			<?php the_archive_description( '<p class="archive-desc">', '</p>' ); ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="<?php echo $is_hero ? 'heroes-mosaic' : ( $is_init ? 'initiative-grid' : 'journal-grid journal-grid--3' ); ?>">
				<?php
				while ( have_posts() ) {
					the_post();
					if ( $is_hero ) {
						get_template_part( 'template-parts/card', 'hero' );
					} elseif ( $is_init ) {
						get_template_part( 'template-parts/card', 'initiative' );
					} else {
						get_template_part( 'template-parts/card', 'post', array( 'variant' => 'standard' ) );
					}
				}
				?>
			</div>
			<?php vigilum_pagination(); ?>
		<?php else : ?>
			<div class="empty-state"><p><?php esc_html_e( 'В этом разделе пока пусто.', 'vigilum' ); ?></p></div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
