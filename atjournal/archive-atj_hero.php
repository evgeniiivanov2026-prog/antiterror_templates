<?php
/**
 * Memorial archive.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="content" class="atj-main">
	<?php
	atj_page_header(
		__( 'Архив', 'atjournal' ),
		__( 'Память', 'atjournal' ),
		__( 'Карточки, которые ведёт редакция. Образцы демонстрационной установки помечены и не описывают реальных людей.', 'atjournal' )
	);
	?>
	<div class="atj-shell atj-loop">
		<?php if ( have_posts() ) : ?>
			<div class="atj-hero-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'hero' );
				endwhile;
				?>
			</div>
			<?php atj_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
