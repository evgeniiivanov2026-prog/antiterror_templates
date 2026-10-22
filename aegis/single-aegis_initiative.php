<?php
/**
 * Initiative page.
 *
 * @package Aegis
 */

get_header();
$meta  = aegis_initiative_meta();
$voted = aegis_has_voted( get_the_ID() );
?>
<main id="primary" class="site-main interior">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'article article--initiative' ); ?> data-initiative="<?php the_ID(); ?>">
			<header class="article__header shell">
				<?php aegis_breadcrumbs(); ?>
				<p class="entry-meta">
					<span class="status-pill status-pill--<?php echo esc_attr( $meta['status'] ); ?>"><?php echo esc_html( aegis_status_label( $meta['status'] ) ); ?></span>
					<?php if ( $meta['author'] ) : ?>
						<span><?php echo esc_html( $meta['author'] ); ?></span>
					<?php endif; ?>
				</p>
				<h1><?php the_title(); ?></h1>
			</header>
			<div class="article__layout shell">
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
				<aside class="article__aside">
					<?php
					get_template_part(
						'template-parts/vote-control',
						null,
						array(
							'votes'    => $meta['votes'],
							'voted'    => $voted,
							'can_vote' => 'closed' !== $meta['status'],
							'id'       => get_the_ID(),
						)
					);
					?>
				</aside>
			</div>
		</article>
		<div class="shell">
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
