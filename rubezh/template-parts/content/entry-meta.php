<?php
/**
 * Мета публикации.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cats = get_the_category();
?>
<p class="card-meta article-meta">
	<?php if ( $cats ) : ?>
		<a class="chip" href="<?php echo esc_url( get_category_link( $cats[0] ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a>
	<?php endif; ?>
	<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></time>
	<span><?php echo esc_html( rubezh_reading_time() ); ?></span>
	<span><?php the_author(); ?></span>
</p>
