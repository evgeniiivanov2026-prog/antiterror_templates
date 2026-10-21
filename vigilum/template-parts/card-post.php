<?php
/**
 * Magazine article card.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variant = isset( $args['variant'] ) ? $args['variant'] : 'standard';
?>
<article <?php post_class( 'card card--post card--' . sanitize_html_class( $variant ) ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'featured' === $variant ? 'vigilum-hero' : 'vigilum-card' ); ?>
		</a>
	<?php endif; ?>
	<div class="card__body">
		<p class="card__meta">
			<?php
			$cats = get_the_category();
			if ( $cats ) {
				echo '<span class="card__kicker">' . esc_html( $cats[0]->name ) . '</span>';
			}
			?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></time>
			<span><?php echo esc_html( vigilum_reading_time() ); ?></span>
		</p>
		<h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( 'featured' === $variant || 'standard' === $variant ) : ?>
			<p class="card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 'featured' === $variant ? 36 : 22 ) ); ?></p>
		<?php endif; ?>
		<a class="card__more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Читать', 'vigilum' ); ?> <span aria-hidden="true">↗</span></a>
	</div>
</article>
