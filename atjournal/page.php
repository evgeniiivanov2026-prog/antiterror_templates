<?php
/**
 * Page.
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
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'atj-sheet' ); ?>>
			<header class="atj-pagehead">
				<div class="atj-shell">
					<?php atj_breadcrumbs(); ?>
					<p class="atj-kicker"><?php esc_html_e( 'Страница', 'atjournal' ); ?></p>
					<h1><?php the_title(); ?></h1>
				</div>
			</header>
			<div class="atj-shell atj-sheet__body entry-content">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before' => '<nav class="atj-pages">' . esc_html__( 'Страницы:', 'atjournal' ),
						'after'  => '</nav>',
					)
				);
				?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
