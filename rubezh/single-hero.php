<?php
/**
 * Карточка героя — мемориальная страница.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
while ( have_posts() ) :
	the_post();
	$years = get_post_meta( get_the_ID(), '_rubezh_years', true );
	$rank  = get_post_meta( get_the_ID(), '_rubezh_rank', true );
	$place = get_post_meta( get_the_ID(), '_rubezh_place', true );
	$line  = get_post_meta( get_the_ID(), '_rubezh_line', true );
	$term  = rubezh_first_term( get_the_ID(), 'hero_service' );
	?>
<main id="primary" class="site-main section--chapel chapel-page">
	<article <?php post_class( 'shell hero-page' ); ?>>
		<?php get_template_part( 'template-parts/content/breadcrumbs' ); ?>
		<div class="hero-page__grid">
			<div class="hero-page__portrait">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'rubezh-portrait' ); ?>
				<?php else : ?>
					<span class="card-hero__silhouette card-hero__silhouette--lg" aria-hidden="true">
						<svg viewBox="0 0 160 200"><circle cx="80" cy="68" r="28" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M32 176c8-42 28-62 48-62s40 20 48 62" fill="none" stroke="currentColor" stroke-width="1.4"/></svg>
					</span>
				<?php endif; ?>
			</div>
			<div class="hero-page__copy">
				<?php if ( $term ) : ?><p class="chip chip--bronze"><?php echo esc_html( $term->name ); ?></p><?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<p class="hero-page__meta">
					<?php if ( $years ) : ?><span><?php echo esc_html( $years ); ?></span><?php endif; ?>
					<?php if ( $rank ) : ?><span><?php echo esc_html( $rank ); ?></span><?php endif; ?>
					<?php if ( $place ) : ?><span><?php echo esc_html( $place ); ?></span><?php endif; ?>
				</p>
				<?php if ( $line ) : ?><p class="hero-page__line"><?php echo esc_html( $line ); ?></p><?php endif; ?>
				<div class="entry-content entry-content--memorial">
					<?php the_content(); ?>
				</div>
			</div>
		</div>
	</article>
</main>
	<?php
endwhile;
get_footer();
