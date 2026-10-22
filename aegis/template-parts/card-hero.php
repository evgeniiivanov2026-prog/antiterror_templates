<?php
/**
 * Memory portrait card.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$meta = aegis_hero_meta();
?>
<article <?php post_class( 'memory-card' ); ?>>
	<a class="memory-card__link" href="<?php the_permalink(); ?>">
		<div class="memory-card__portrait">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'aegis-portrait' ); ?>
			<?php else : ?>
				<span class="memory-card__silhouette" aria-hidden="true"></span>
			<?php endif; ?>
		</div>
		<div class="memory-card__body">
			<?php if ( $meta['service'] ) : ?>
				<p class="memory-card__service"><?php echo esc_html( $meta['service'] ); ?></p>
			<?php endif; ?>
			<h3><?php the_title(); ?></h3>
			<?php if ( $meta['years'] ) : ?>
				<p class="memory-card__years"><?php echo esc_html( $meta['years'] ); ?></p>
			<?php endif; ?>
			<?php if ( $meta['award'] ) : ?>
				<p class="memory-card__award"><?php echo esc_html( $meta['award'] ); ?></p>
			<?php endif; ?>
		</div>
	</a>
</article>
