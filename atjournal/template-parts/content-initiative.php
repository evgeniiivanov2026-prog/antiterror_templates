<?php
/**
 * One row of the ledger.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$index  = isset( $args['index'] ) ? (int) $args['index'] : 0;
$status = atj_initiative_status( get_the_ID() );
?>
<article <?php post_class( 'atj-row' ); ?>>
	<?php if ( $index ) : ?>
		<p class="atj-row__index"><?php echo esc_html( sprintf( '%02d', $index ) ); ?></p>
	<?php endif; ?>
	<div class="atj-row__body">
		<p class="atj-row__meta">
			<span><?php echo esc_html( atj_kicker_text() ); ?></span>
			<span><?php echo 'open' === $status ? esc_html__( 'Голосование открыто', 'atjournal' ) : esc_html__( 'Голосование завершено', 'atjournal' ); ?></span>
		</p>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( atj_card_text( 26 ) ); ?></p>
		<?php atj_sample_badge(); ?>
	</div>
	<?php echo atj_vote_markup( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>
</article>
