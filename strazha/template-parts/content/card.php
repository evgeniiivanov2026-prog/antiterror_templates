<?php
/**
 * Карточка материала.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variant = isset( $args['variant'] ) ? sanitize_key( $args['variant'] ) : 'default';
if ( ! in_array( $variant, array( 'lead', 'compact', 'default' ), true ) ) {
	$variant = 'default';
}
$words = 'compact' === $variant ? 16 : 28;
$term  = strazha_term_name( get_the_ID(), 'category' );
$size  = 'lead' === $variant ? 'strazha-feature' : 'strazha-card';
?>
<article <?php post_class( 'card card--' . $variant ); ?>>
	<a class="card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( $size );
		} else {
			echo '<span class="card__placeholder"></span>';
		}
		?>
	</a>
	<div class="card__body">
		<p class="card__meta">
			<?php if ( $term ) : ?>
				<span><?php echo esc_html( $term ); ?></span>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time>
		</p>
		<h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( 'compact' !== $variant ) : ?>
			<p class="card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), $words ) ); ?></p>
		<?php endif; ?>
	</div>
</article>
