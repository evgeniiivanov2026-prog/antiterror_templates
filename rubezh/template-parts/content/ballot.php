<?php
/**
 * Карточка инициативы в списке.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id  = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
$max      = isset( $args['max'] ) ? max( 1, (int) $args['max'] ) : 1;
$voted    = ! empty( $args['voted'] );
$count    = rubezh_vote_count( $post_id );
$share    = $count > 0 ? round( ( $count / $max ) * 100, 1 ) : 0;
$status   = rubezh_initiative_status( $post_id );
$region   = (string) get_post_meta( $post_id, '_rubezh_region', true );
$proposer = (string) get_post_meta( $post_id, '_rubezh_proposer', true );
$sample   = (bool) get_post_meta( $post_id, '_rubezh_sample', true );
?>
<article class="rz-ballot<?php echo $sample ? ' is-sample' : ''; ?>">
	<div>
		<p class="rz-kicker">
			<?php echo esc_html( rubezh_status_label( $status ) ); ?>
			<?php if ( '' !== $region ) : ?>
				<span> · <?php echo esc_html( $region ); ?></span>
			<?php endif; ?>
		</p>
		<?php if ( $sample ) : ?>
			<p class="rz-badge"><?php esc_html_e( 'Образец', 'rubezh' ); ?></p>
		<?php endif; ?>
		<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
		<p><?php echo esc_html( rubezh_excerpt( $post_id, 28 ) ); ?></p>
		<?php if ( '' !== $proposer ) : ?>
			<p class="rz-ballot__by"><?php echo esc_html( $proposer ); ?></p>
		<?php endif; ?>
	</div>
	<?php rubezh_vote_controls( $post_id, $voted, $share ); ?>
</article>
