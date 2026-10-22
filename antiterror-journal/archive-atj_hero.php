<?php
/**
 * Memorial archive.
 *
 * @package AntiterrorJournal
 */

get_header();
?>
<main id="primary" class="atj-page atj-memorial-page">
	<div class="atj-shell">
		<header class="atj-page__head atj-page__head--light">
			<?php atj_breadcrumbs(); ?>
			<p class="atj-section-number atj-section-number--gold">02 / <?php esc_html_e( 'Память', 'antiterror-journal' ); ?></p>
			<h1><?php esc_html_e( 'Стена памяти героев антитеррора', 'antiterror-journal' ); ?></h1>
			<p><?php echo esc_html( atj_mod( 'atj_memorial_intro', __( 'Карточки памяти публикуются только по проверяемым сведениям.', 'antiterror-journal' ) ) ); ?></p>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="atj-memorial__wall">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/hero', 'card' );
				endwhile;
				?>
			</div>
			<?php atj_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/none' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
