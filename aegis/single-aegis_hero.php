<?php
/**
 * Memory portrait page.
 *
 * @package Aegis
 */

get_header();
$meta = aegis_hero_meta();
?>
<main id="primary" class="site-main interior interior--memory">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'portrait' ); ?>>
			<div class="shell portrait__grid">
				<figure class="portrait__media">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'aegis-portrait' ); ?>
					<?php else : ?>
						<span class="memory-card__silhouette" aria-hidden="true"></span>
					<?php endif; ?>
				</figure>
				<div class="portrait__body">
					<?php aegis_breadcrumbs(); ?>
					<?php if ( $meta['service'] ) : ?>
						<p class="section-kicker section-kicker--bronze"><?php echo esc_html( $meta['service'] ); ?></p>
					<?php endif; ?>
					<h1><?php the_title(); ?></h1>
					<?php if ( $meta['years'] ) : ?>
						<p class="portrait__years"><?php echo esc_html( $meta['years'] ); ?></p>
					<?php endif; ?>
					<dl class="portrait__facts">
						<?php if ( $meta['award'] ) : ?>
							<div><dt><?php esc_html_e( 'Награда', 'aegis' ); ?></dt><dd><?php echo esc_html( $meta['award'] ); ?></dd></div>
						<?php endif; ?>
						<?php if ( $meta['date'] ) : ?>
							<div><dt><?php esc_html_e( 'Дата', 'aegis' ); ?></dt><dd><?php echo esc_html( $meta['date'] ); ?></dd></div>
						<?php endif; ?>
					</dl>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</div>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
