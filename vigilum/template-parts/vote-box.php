<?php
/**
 * Vote control.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id      = get_the_ID();
$data    = vigilum_initiative_data( $id );
$voted   = vigilum_has_voted( $id );
$closed  = 'closed' === $data['status'];
$disabled = $voted || $closed;
?>
<div class="vote-box" data-vote-box data-id="<?php echo esc_attr( $id ); ?>">
	<button class="vote-button" type="button" data-vote="<?php echo esc_attr( $id ); ?>" <?php disabled( $disabled ); ?>>
		<span class="vote-button__icon" aria-hidden="true">▲</span>
		<span class="vote-button__label"><?php echo $voted ? esc_html__( 'Голос принят', 'vigilum' ) : esc_html__( 'Поддержать', 'vigilum' ); ?></span>
	</button>
	<p class="vote-count" data-vote-count>
		<strong><?php echo esc_html( number_format_i18n( $data['votes'] ) ); ?></strong>
		<span><?php echo esc_html( vigilum_votes_noun( $data['votes'] ) ); ?></span>
	</p>
</div>
