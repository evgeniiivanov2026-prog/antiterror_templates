<?php
/**
 * Строка инициативы.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$field    = strazha_term_name( get_the_ID(), 'initiative_field' );
$statuses = strazha_statuses();
$status   = strazha_initiative_status( get_the_ID() );
?>
<article <?php post_class( 'bill' ); ?>>
	<div class="bill__copy">
		<p class="bill__meta">
			<?php if ( $field ) : ?>
				<span><?php echo esc_html( $field ); ?></span>
			<?php endif; ?>
			<span class="status status--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $statuses[ $status ] ); ?></span>
		</p>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php if ( has_excerpt() || get_the_excerpt() ) : ?>
			<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32 ) ); ?></p>
		<?php endif; ?>
	</div>
	<?php strazha_vote_control( get_the_ID() ); ?>
</article>
