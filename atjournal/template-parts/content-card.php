<?php
/**
 * Journal card. Variants: feature, stack, tile.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variant = isset( $args['variant'] ) ? $args['variant'] : 'tile';
if ( ! in_array( $variant, array( 'feature', 'stack', 'tile' ), true ) ) {
	$variant = 'tile';
}
$heading = ( isset( $args['heading'] ) && 'h3' === $args['heading'] ) ? 'h3' : 'h2';
$eager   = 'feature' === $variant;
?>
<article <?php post_class( 'atj-card atj-card--' . $variant ); ?>>
	<?php if ( 'stack' !== $variant ) : ?>
		<a class="atj-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php
				the_post_thumbnail(
					'feature' === $variant ? 'atj-feature' : 'atj-card',
					array(
						'loading'       => $eager ? 'eager' : 'lazy',
						'fetchpriority' => $eager ? 'high' : 'auto',
					)
				);
				?>
			<?php else : ?>
				<span class="atj-cover">
					<b><?php echo esc_html( atj_monogram( get_the_title() ) ); ?></b>
					<small><?php echo esc_html( atj_kicker_text() ); ?></small>
				</span>
			<?php endif; ?>
		</a>
	<?php endif; ?>
	<div class="atj-card__body">
		<?php atj_sample_badge(); ?>
		<p class="atj-card__meta">
			<span><?php echo esc_html( atj_kicker_text() ); ?></span>
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( atj_human_date( get_the_ID() ) ); ?></time>
			<?php if ( 'atj_hero' !== get_post_type() && 'atj_initiative' !== get_post_type() ) : ?>
				<span><?php echo esc_html( sprintf( /* translators: %d minutes */ _n( '%d мин', '%d мин', atj_reading_minutes(), 'atjournal' ), atj_reading_minutes() ) ); ?></span>
			<?php endif; ?>
		</p>
		<<?php echo esc_attr( $heading ); ?> class="atj-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</<?php echo esc_attr( $heading ); ?>>
		<?php if ( 'stack' !== $variant || has_excerpt() ) : ?>
			<p class="atj-card__text"><?php echo esc_html( atj_card_text( 'feature' === $variant ? 32 : 18 ) ); ?></p>
		<?php endif; ?>
	</div>
</article>
