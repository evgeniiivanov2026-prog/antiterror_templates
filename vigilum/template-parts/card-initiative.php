<?php
/**
 * Initiative card with vote.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data    = vigilum_initiative_data( get_the_ID() );
$percent = ( $data['goal'] > 0 ) ? min( 100, (int) round( ( $data['votes'] / $data['goal'] ) * 100 ) ) : 0;
?>
<article <?php post_class( 'card card--initiative' ); ?> data-initiative="<?php the_ID(); ?>">
	<div class="card__body">
		<p class="card__meta">
			<span class="status status--<?php echo esc_attr( $data['status'] ); ?>"><?php echo esc_html( vigilum_status_label( $data['status'] ) ); ?></span>
			<?php
			$topics = get_the_terms( get_the_ID(), 'initiative_topic' );
			if ( $topics && ! is_wp_error( $topics ) ) {
				echo '<span>' . esc_html( $topics[0]->name ) . '</span>';
			}
			?>
		</p>
		<h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt() ?: wp_strip_all_tags( get_the_content() ), 24 ) ); ?></p>
		<?php if ( $data['author'] ) : ?>
			<p class="card__author"><?php echo esc_html( $data['author'] ); ?></p>
		<?php endif; ?>
		<?php if ( $data['goal'] > 0 ) : ?>
			<div class="vote-meter" role="img" aria-label="<?php echo esc_attr( sprintf( __( '%1$s из %2$s голосов', 'vigilum' ), $data['votes'], $data['goal'] ) ); ?>">
				<span style="width:<?php echo esc_attr( $percent ); ?>%"></span>
			</div>
		<?php endif; ?>
		<?php get_template_part( 'template-parts/vote', 'box' ); ?>
	</div>
</article>
