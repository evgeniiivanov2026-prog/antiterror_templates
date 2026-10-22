<?php
/**
 * Civic vote control.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id       = isset( $args['id'] ) ? absint( $args['id'] ) : get_the_ID();
$votes    = isset( $args['votes'] ) ? absint( $args['votes'] ) : 0;
$voted    = ! empty( $args['voted'] );
$can_vote = isset( $args['can_vote'] ) ? (bool) $args['can_vote'] : true;
?>
<div class="vote-control <?php echo $voted ? 'is-voted' : ''; ?>">
	<strong class="vote-control__count" data-vote-count><?php echo esc_html( number_format_i18n( $votes ) ); ?></strong>
	<span class="vote-control__label"><?php esc_html_e( 'голосов', 'aegis' ); ?></span>
	<?php if ( $can_vote ) : ?>
		<button
			class="vote-control__btn"
			type="button"
			data-vote="<?php echo esc_attr( $id ); ?>"
			<?php disabled( $voted ); ?>
		>
			<?php echo $voted ? esc_html__( 'Поддержано', 'aegis' ) : esc_html__( 'Поддержать', 'aegis' ); ?>
		</button>
	<?php else : ?>
		<span class="vote-control__closed"><?php esc_html_e( 'Голосование закрыто', 'aegis' ); ?></span>
	<?php endif; ?>
</div>
