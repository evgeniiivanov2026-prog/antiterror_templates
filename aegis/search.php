<?php
/**
 * Search results.
 *
 * @package Aegis
 */

get_header();
?>
<main id="primary" class="site-main interior">
	<div class="shell">
		<header class="archive-head">
			<p class="section-kicker"><?php esc_html_e( 'Поиск', 'aegis' ); ?></p>
			<h1>
				<?php
				printf(
					/* translators: %s: search query */
					esc_html__( 'Результаты: %s', 'aegis' ),
					'<em>' . esc_html( get_search_query() ) . '</em>'
				);
				?>
			</h1>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="story-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					$part = 'aegis_hero' === get_post_type() ? 'hero' : ( 'aegis_initiative' === get_post_type() ? 'initiative' : 'post' );
					get_template_part( 'template-parts/card', $part );
				endwhile;
				?>
			</div>
			<div class="pagination-box"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'Ничего не найдено. Измените запрос.', 'aegis' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
