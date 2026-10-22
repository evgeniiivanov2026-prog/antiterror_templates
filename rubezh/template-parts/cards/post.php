<?php
/**
 * Карточка материала журнала.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$size     = isset( $args['size'] ) ? $args['size'] : 'card';
$category = get_the_category();
$term     = $category ? $category[0] : null;
?>
<article <?php post_class( 'card-post card-post--' . $size . ' reveal' ); ?>>
	<a class="card-post__media" href="<?php the_permalink(); ?>" aria-hidden="<?php echo has_post_thumbnail() ? 'false' : 'true'; ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'feature' === $size ? 'rubezh-feature' : 'rubezh-card' ); ?>
		<?php else : ?>
			<span class="card-post__fallback" aria-hidden="true"></span>
		<?php endif; ?>
	</a>
	<div class="card-post__body">
		<p class="card-meta">
			<?php if ( $term ) : ?>
				<a class="chip" href="<?php echo esc_url( get_category_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></time>
			<span><?php echo esc_html( rubezh_reading_time() ); ?></span>
		</p>
		<h3 class="card-post__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( 'stack' !== $size ) : ?>
			<p class="card-post__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 'feature' === $size ? 32 : 18 ) ); ?></p>
		<?php endif; ?>
		<a class="card-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Читать', 'rubezh' ); ?> <span aria-hidden="true">↗</span></a>
	</div>
</article>
