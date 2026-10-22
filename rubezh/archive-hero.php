<?php
/**
 * Раздел памяти.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="rz-main">
	<header class="rz-shell rz-page-head">
		<?php rubezh_breadcrumbs(); ?>
		<p class="rz-index"><span>02</span> <?php esc_html_e( 'Память', 'rubezh' ); ?></p>
		<h1><?php post_type_archive_title(); ?></h1>
		<p class="rz-deck"><?php esc_html_e( 'Люди, которые противостояли террору. В разделе только проверенные сведения, без сенсационных подробностей.', 'rubezh' ); ?></p>
	</header>
	<div class="rz-shell">
		<?php if ( have_posts() ) : ?>
			<div class="rz-portrait-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/portrait' );
				endwhile;
				?>
			</div>
			<?php rubezh_journal_pager( false ); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/empty', 'hero' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
