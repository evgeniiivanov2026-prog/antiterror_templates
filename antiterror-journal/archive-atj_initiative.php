<?php
/**
 * Initiative archive.
 *
 * @package AntiterrorJournal
 */

get_header();
?>
<main id="primary" class="atj-page atj-shell">
	<header class="atj-page__head">
		<?php atj_breadcrumbs(); ?>
		<p class="atj-section-number">03 / <?php esc_html_e( 'Фонд', 'antiterror-journal' ); ?></p>
		<h1><?php esc_html_e( 'Фонд инициатив', 'antiterror-journal' ); ?></h1>
		<p><?php echo esc_html( atj_mod( 'atj_fund_intro', __( 'Голосуйте за инициативы в сфере антитеррора.', 'antiterror-journal' ) ) ); ?></p>
	</header>
	<?php if ( have_posts() ) : ?>
		<div class="atj-ballot">
			<?php
			$index = 1;
			while ( have_posts() ) :
				the_post();
				set_query_var( 'atj_rank', $index );
				get_template_part( 'template-parts/content/initiative', 'card' );
				$index++;
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
