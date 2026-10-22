<?php
/**
 * Default page.
 *
 * @package AntiterrorJournal
 */

get_header();
?>
<main id="primary" class="atj-page atj-shell">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'atj-article' ); ?>>
			<header class="atj-article__header">
				<?php atj_breadcrumbs(); ?>
				<h1 class="atj-article__title"><?php the_title(); ?></h1>
			</header>
			<div class="entry-content atj-article__content">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
