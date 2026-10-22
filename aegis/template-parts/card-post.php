<?php
/**
 * Magazine story card.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variant = isset( $args['variant'] ) ? $args['variant'] : 'standard';
?>
<article <?php post_class( 'story-card story-card--' . $variant ); ?>>
	<a class="story-card__media" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'featured' === $variant ? 'aegis-featured' : 'aegis-card' ); ?>
		<?php else : ?>
			<span class="story-card__fallback" aria-hidden="true"></span>
		<?php endif; ?>
	</a>
	<div class="story-card__body">
		<?php
		$cats = get_the_category();
		if ( $cats ) :
			?>
			<p class="story-card__kicker"><?php echo esc_html( $cats[0]->name ); ?></p>
		<?php endif; ?>
		<h3 class="story-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( 'compact' !== $variant ) : ?>
			<p class="story-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 'featured' === $variant ? 28 : 18 ) ); ?></p>
		<?php endif; ?>
		<p class="story-card__meta">
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<span aria-hidden="true">·</span>
			<span><?php echo esc_html( get_the_author() ); ?></span>
		</p>
	</div>
</article>
