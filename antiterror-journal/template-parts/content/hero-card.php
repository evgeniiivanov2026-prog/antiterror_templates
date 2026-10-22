<?php
/**
 * Memorial portrait card.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rank    = (string) get_post_meta( get_the_ID(), '_atj_rank', true );
$years   = (string) get_post_meta( get_the_ID(), '_atj_years', true );
$service = (string) get_post_meta( get_the_ID(), '_atj_service_label', true );
?>
<article <?php post_class( 'atj-portrait' ); ?>>
	<a href="<?php the_permalink(); ?>">
		<div class="atj-portrait__media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'atj-portrait' ); ?>
			<?php else : ?>
				<span class="atj-portrait__placeholder" aria-hidden="true"></span>
			<?php endif; ?>
		</div>
		<div class="atj-portrait__body">
			<?php if ( $service || $rank ) : ?>
				<p class="atj-kicker atj-kicker--gold"><?php echo esc_html( trim( $service . ( $rank ? ' · ' . $rank : '' ) ) ); ?></p>
			<?php endif; ?>
			<h3><?php the_title(); ?></h3>
			<?php if ( $years ) : ?>
				<p class="atj-portrait__years"><?php echo esc_html( $years ); ?></p>
			<?php endif; ?>
		</div>
	</a>
</article>
