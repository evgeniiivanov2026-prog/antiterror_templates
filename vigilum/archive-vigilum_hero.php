<?php
/**
 * Memory archive.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main interior memory-archive">
	<div class="shell">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<header class="archive-head">
			<p class="section-number">02 / <?php esc_html_e( 'Память', 'vigilum' ); ?></p>
			<h1><?php esc_html_e( 'Герои антитеррора', 'vigilum' ); ?></h1>
			<p class="archive-desc"><?php esc_html_e( 'Карточки памяти — факты службы и поступка. Без сенсаций и вымысла.', 'vigilum' ); ?></p>
			<?php
			$roles = get_terms( array( 'taxonomy' => 'hero_role', 'hide_empty' => true ) );
			if ( $roles && ! is_wp_error( $roles ) ) :
				?>
				<nav class="filter-row" aria-label="<?php esc_attr_e( 'Роли', 'vigilum' ); ?>">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'vigilum_hero' ) ); ?>"><?php esc_html_e( 'Все', 'vigilum' ); ?></a>
					<?php foreach ( $roles as $role ) : ?>
						<a href="<?php echo esc_url( get_term_link( $role ) ); ?>"><?php echo esc_html( $role->name ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="heroes-mosaic">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/card', 'hero' );
				}
				?>
			</div>
			<?php vigilum_pagination(); ?>
		<?php else : ?>
			<div class="empty-state empty-state--memory">
				<p><?php esc_html_e( 'Архив памяти пуст. Добавьте карточки героев в разделе «Память героев».', 'vigilum' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
