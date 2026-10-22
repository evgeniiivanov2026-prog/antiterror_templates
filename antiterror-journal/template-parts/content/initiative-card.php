<?php
/**
 * Initiative ballot card.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rank    = (int) get_query_var( 'atj_rank', 0 );
$region  = (string) get_post_meta( get_the_ID(), '_atj_region', true );
$author  = (string) get_post_meta( get_the_ID(), '_atj_author_label', true );
$topics  = get_the_terms( get_the_ID(), 'initiative_topic' );
?>
<article <?php post_class( 'atj-ballot__item' ); ?>>
	<?php if ( $rank ) : ?>
		<p class="atj-ballot__rank"><?php echo esc_html( str_pad( (string) $rank, 2, '0', STR_PAD_LEFT ) ); ?></p>
	<?php endif; ?>
	<div class="atj-ballot__copy">
		<?php if ( $topics && ! is_wp_error( $topics ) ) : ?>
			<p class="atj-kicker"><?php echo esc_html( $topics[0]->name ); ?></p>
		<?php endif; ?>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
		<p class="atj-ballot__meta">
			<?php
			if ( $author ) {
				echo esc_html( $author );
			}
			if ( $region ) {
				echo $author ? ' · ' : '';
				echo esc_html( $region );
			}
			?>
		</p>
	</div>
	<?php atj_vote_button( get_the_ID() ); ?>
</article>
