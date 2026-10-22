<?php
/**
 * Карточка материала.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variant = isset( $args['variant'] ) ? $args['variant'] : 'row';
$index   = isset( $args['index'] ) ? (string) $args['index'] : '';
?>
<?php if ( 'grid' === $variant ) : ?>
	<article <?php post_class( 'rz-card' ); ?>>
		<?php if ( '' !== $index ) : ?>
			<p class="rz-card__index"><?php echo esc_html( $index ); ?></p>
		<?php endif; ?>
		<p class="rz-kicker"><?php rubezh_category_label(); ?></p>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( rubezh_excerpt( get_the_ID(), 22 ) ); ?></p>
	</article>
<?php else : ?>
	<article <?php post_class( 'rz-row' ); ?>>
		<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></time>
		<p class="rz-kicker"><?php rubezh_category_label(); ?></p>
		<div>
			<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<p><?php echo esc_html( rubezh_excerpt( get_the_ID(), 18 ) ); ?></p>
		</div>
		<span><?php echo esc_html( rubezh_reading_time() ); ?></span>
	</article>
<?php endif; ?>
