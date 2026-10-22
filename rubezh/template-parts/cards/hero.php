<?php
/**
 * Карточка героя памяти.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$years = get_post_meta( get_the_ID(), '_rubezh_years', true );
$rank  = get_post_meta( get_the_ID(), '_rubezh_rank', true );
$place = get_post_meta( get_the_ID(), '_rubezh_place', true );
$line  = get_post_meta( get_the_ID(), '_rubezh_line', true );
$term  = rubezh_first_term( get_the_ID(), 'hero_service' );
?>
<article <?php post_class( 'card-hero reveal' ); ?>>
	<a class="card-hero__portrait" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'rubezh-portrait' ); ?>
		<?php else : ?>
			<span class="card-hero__silhouette" aria-hidden="true">
				<svg viewBox="0 0 160 200"><circle cx="80" cy="68" r="28" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M32 176c8-42 28-62 48-62s40 20 48 62" fill="none" stroke="currentColor" stroke-width="1.4"/></svg>
			</span>
		<?php endif; ?>
	</a>
	<div class="card-hero__body">
		<?php if ( $term ) : ?><p class="chip chip--bronze"><?php echo esc_html( $term->name ); ?></p><?php endif; ?>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="card-hero__meta">
			<?php if ( $years ) : ?><span><?php echo esc_html( $years ); ?></span><?php endif; ?>
			<?php if ( $rank ) : ?><span><?php echo esc_html( $rank ); ?></span><?php endif; ?>
		</p>
		<?php if ( $line ) : ?><p class="card-hero__line"><?php echo esc_html( $line ); ?></p><?php endif; ?>
		<?php if ( $place ) : ?><p class="card-hero__place"><?php echo esc_html( $place ); ?></p><?php endif; ?>
	</div>
</article>
