<?php
/**
 * Главный материал выпуска.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$class = has_post_thumbnail() ? 'rz-lead' : 'rz-lead rz-lead--text';
?>
<article <?php post_class( $class ); ?>>
	<div class="rz-lead__copy">
		<p class="rz-kicker"><?php rubezh_category_label(); ?> · <?php echo esc_html( get_the_date( 'j F Y' ) ); ?></p>
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="rz-deck"><?php echo esc_html( rubezh_excerpt( get_the_ID(), 36 ) ); ?></p>
		<a class="rz-textlink" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Читать материал', 'rubezh' ); ?></a>
	</div>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="rz-lead__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'rubezh-lead', array( 'loading' => 'eager' ) ); ?>
		</a>
	<?php endif; ?>
</article>
