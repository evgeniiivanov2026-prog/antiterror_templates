<?php
/**
 * Fallback archive.
 *
 * @package AntiterrorJournal
 */

get_header();
?>
<main id="primary" class="atj-page atj-shell">
	<header class="atj-page__head">
		<?php atj_breadcrumbs(); ?>
		<h1><?php echo wp_kses_post( get_the_archive_title() ?: __( 'Журнал', 'antiterror-journal' ) ); ?></h1>
	</header>
	<?php if ( have_posts() ) : ?>
		<div class="atj-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/card' );
			endwhile;
			?>
		</div>
		<?php atj_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content/none' ); ?>
	<?php endif; ?>
</main>
<?php
get_footer();
