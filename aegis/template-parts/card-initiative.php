<?php
/**
 * Civic initiative ballot card.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$meta     = aegis_initiative_meta();
$voted    = function_exists( 'aegis_has_voted' ) ? aegis_has_voted( get_the_ID() ) : false;
$can_vote = 'closed' !== $meta['status'];
$topics   = get_the_terms( get_the_ID(), 'aegis_topic' );
?>
<article <?php post_class( 'ballot-card' ); ?> data-initiative="<?php the_ID(); ?>">
	<div class="ballot-card__rank" aria-hidden="true">
		<span><?php echo esc_html( str_pad( (string) ( isset( $args['index'] ) ? $args['index'] : 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
	</div>
	<div class="ballot-card__body">
		<div class="ballot-card__meta">
			<span class="status-pill status-pill--<?php echo esc_attr( $meta['status'] ); ?>"><?php echo esc_html( aegis_status_label( $meta['status'] ) ); ?></span>
			<?php if ( $topics && ! is_wp_error( $topics ) ) : ?>
				<span><?php echo esc_html( $topics[0]->name ); ?></span>
			<?php endif; ?>
			<?php if ( $meta['author'] ) : ?>
				<span><?php echo esc_html( $meta['author'] ); ?></span>
			<?php endif; ?>
		</div>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt() ?: wp_strip_all_tags( get_the_content() ), 22 ) ); ?></p>
	</div>
	<div class="ballot-card__vote">
		<?php
		get_template_part(
			'template-parts/vote-control',
			null,
			array(
				'votes'    => $meta['votes'],
				'voted'    => $voted,
				'can_vote' => $can_vote,
				'id'       => get_the_ID(),
			)
		);
		?>
	</div>
</article>
