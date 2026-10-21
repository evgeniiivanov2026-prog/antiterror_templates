<?php
/**
 * Memorial single — solemn, factual.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$data = vigilum_hero_data( get_the_ID() );
?>
<main id="primary" class="site-main interior memorial">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'memorial-article' ); ?>>
			<div class="shell memorial-grid">
				<figure class="memorial-portrait">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'vigilum-portrait' ); ?>
					<?php else : ?>
						<span class="card__placeholder" aria-hidden="true"></span>
					<?php endif; ?>
				</figure>
				<div>
					<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
					<p class="kicker"><?php esc_html_e( 'Память', 'vigilum' ); ?></p>
					<h1><?php the_title(); ?></h1>
					<ul class="memorial-facts">
						<?php if ( $data['years'] ) : ?><li><span><?php esc_html_e( 'Годы', 'vigilum' ); ?></span><?php echo esc_html( $data['years'] ); ?></li><?php endif; ?>
						<?php if ( $data['rank'] ) : ?><li><span><?php esc_html_e( 'Звание', 'vigilum' ); ?></span><?php echo esc_html( $data['rank'] ); ?></li><?php endif; ?>
						<?php if ( $data['place'] ) : ?><li><span><?php esc_html_e( 'Место', 'vigilum' ); ?></span><?php echo esc_html( $data['place'] ); ?></li><?php endif; ?>
						<?php if ( $data['award'] ) : ?><li><span><?php esc_html_e( 'Награда', 'vigilum' ); ?></span><?php echo esc_html( $data['award'] ); ?></li><?php endif; ?>
					</ul>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</div>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
