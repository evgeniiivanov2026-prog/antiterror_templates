<?php
/**
 * Memorial card.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = vigilum_hero_data( get_the_ID() );
?>
<article <?php post_class( 'card card--hero' ); ?>>
	<a class="card__media card__media--portrait" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'vigilum-portrait' ); ?>
		<?php else : ?>
			<span class="card__placeholder" aria-hidden="true"></span>
		<?php endif; ?>
	</a>
	<div class="card__body">
		<?php if ( $data['years'] ) : ?>
			<p class="card__years"><?php echo esc_html( $data['years'] ); ?></p>
		<?php endif; ?>
		<h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( $data['rank'] ) : ?>
			<p class="card__rank"><?php echo esc_html( $data['rank'] ); ?></p>
		<?php endif; ?>
		<?php if ( $data['place'] ) : ?>
			<p class="card__place"><?php echo esc_html( $data['place'] ); ?></p>
		<?php endif; ?>
	</div>
</article>
