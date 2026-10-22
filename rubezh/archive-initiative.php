<?php
/**
 * Фонд инициатив.
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
		<p class="rz-index"><span>03</span> <?php esc_html_e( 'Фонд инициатив', 'rubezh' ); ?></p>
		<h1><?php post_type_archive_title(); ?></h1>
		<p class="rz-deck"><?php esc_html_e( 'Поддержите предложение в сфере антитеррора. Голос можно снять тем же действием.', 'rubezh' ); ?></p>
	</header>
	<div class="rz-shell">
		<?php if ( have_posts() ) : ?>
			<?php rubezh_render_ballots( $GLOBALS['wp_query'] ); ?>
			<p class="rz-disclaimer"><?php esc_html_e( 'Голос — общественная поддержка. Он не заменяет решение уполномоченного органа.', 'rubezh' ); ?></p>
			<?php rubezh_journal_pager( false ); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/empty', 'initiative' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
