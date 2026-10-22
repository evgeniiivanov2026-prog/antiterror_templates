<?php
/**
 * Карточка в ленте памяти.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$years   = (string) get_post_meta( get_the_ID(), '_rubezh_years', true );
$role    = (string) get_post_meta( get_the_ID(), '_rubezh_role', true );
$place   = (string) get_post_meta( get_the_ID(), '_rubezh_place', true );
$meta    = array_filter( array( $role, $place ) );
$sample  = (bool) get_post_meta( get_the_ID(), '_rubezh_sample', true );
?>
<article <?php post_class( $sample ? 'rz-portrait is-sample' : 'rz-portrait' ); ?>>
	<a href="<?php the_permalink(); ?>">
		<div class="rz-portrait__frame">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'rubezh-portrait', array( 'loading' => 'lazy' ) ); ?>
			<?php else : ?>
				<span class="rz-portrait__mono"><?php echo esc_html( rubezh_monogram( get_the_ID() ) ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( '' !== $years ) : ?>
			<p class="rz-portrait__years"><?php echo esc_html( $years ); ?></p>
		<?php endif; ?>
		<h3><?php the_title(); ?></h3>
		<?php if ( $meta ) : ?>
			<p><?php echo esc_html( implode( ' · ', $meta ) ); ?></p>
		<?php endif; ?>
	</a>
</article>
