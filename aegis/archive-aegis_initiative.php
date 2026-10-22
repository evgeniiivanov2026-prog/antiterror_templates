<?php
/**
 * Initiatives archive — ranked ballot.
 *
 * @package Aegis
 */

get_header();
?>
<main id="primary" class="site-main interior">
	<div class="shell">
		<header class="archive-head">
			<?php aegis_breadcrumbs(); ?>
			<p class="section-kicker"><?php esc_html_e( 'Фонд инициатив', 'aegis' ); ?></p>
			<h1><?php esc_html_e( 'Гражданский бюллетень', 'aegis' ); ?></h1>
			<p class="archive-head__lead"><?php esc_html_e( 'Один голос на инициативу. Приоритет фонда, не рейтинг популярности.', 'aegis' ); ?></p>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="ballot-list">
				<?php
				$i = 1;
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card', 'initiative', array( 'index' => $i ) );
					$i++;
				endwhile;
				?>
			</div>
			<div class="pagination-box"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'Инициатив пока нет.', 'aegis' ); ?></p>
		<?php endif; ?>
		<p class="band__more" style="margin-top:2rem"><a href="<?php echo esc_url( home_url( '/#propose' ) ); ?>"><?php esc_html_e( 'Предложить инициативу', 'aegis' ); ?> →</a></p>
	</div>
</main>
<?php
get_footer();
