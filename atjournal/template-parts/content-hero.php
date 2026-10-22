<?php
/**
 * Memorial card.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$years = (string) get_post_meta( get_the_ID(), '_atj_years', true );
$role  = (string) get_post_meta( get_the_ID(), '_atj_role', true );
$place = (string) get_post_meta( get_the_ID(), '_atj_place', true );
?>
<article <?php post_class( 'atj-hero-card' ); ?>>
	<a href="<?php the_permalink(); ?>">
		<div class="atj-hero-card__media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'atj-portrait' ); ?>
			<?php else : ?>
				<span class="atj-monogram" aria-hidden="true"><?php echo esc_html( atj_monogram( get_the_title() ) ); ?></span>
			<?php endif; ?>
		</div>
		<div class="atj-hero-card__body">
			<?php atj_sample_badge(); ?>
			<?php if ( $years ) : ?>
				<p class="atj-hero-card__years"><?php echo esc_html( $years ); ?></p>
			<?php endif; ?>
			<h3><?php the_title(); ?></h3>
			<?php if ( $role ) : ?>
				<p class="atj-hero-card__role"><?php echo esc_html( $role ); ?></p>
			<?php endif; ?>
			<?php if ( $place ) : ?>
				<p class="atj-hero-card__place"><?php echo esc_html( $place ); ?></p>
			<?php endif; ?>
		</div>
	</a>
</article>
