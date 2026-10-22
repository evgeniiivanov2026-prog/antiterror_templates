<?php
/**
 * Гражданский бюллетень инициативы.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$rank    = isset( $args['rank'] ) ? (int) $args['rank'] : 0;
$max     = isset( $args['max'] ) ? max( 1, (int) $args['max'] ) : rubezh_max_votes();
$votes   = (int) get_post_meta( get_the_ID(), '_rubezh_votes', true );
$status  = get_post_meta( get_the_ID(), '_rubezh_status', true ) ?: 'open';
$author  = get_post_meta( get_the_ID(), '_rubezh_author', true );
$topic   = rubezh_first_term( get_the_ID(), 'initiative_topic' );
$percent = (int) round( ( $votes / $max ) * 100 );
$voted   = function_exists( 'rubezh_has_voted' ) ? rubezh_has_voted( get_the_ID() ) : false;
$open    = 'open' === $status;
?>
<article <?php post_class( 'card-ballot reveal' ); ?> data-initiative="<?php the_ID(); ?>">
	<div class="card-ballot__rank" aria-hidden="true"><?php echo esc_html( $rank ? str_pad( (string) $rank, 2, '0', STR_PAD_LEFT ) : '' ); ?></div>
	<div class="card-ballot__body">
		<p class="card-meta">
			<?php if ( $topic ) : ?><span class="chip"><?php echo esc_html( $topic->name ); ?></span><?php endif; ?>
			<span class="chip chip--ghost"><?php echo esc_html( rubezh_status_label( $status ) ); ?></span>
			<?php if ( $author ) : ?><span><?php echo esc_html( $author ); ?></span><?php endif; ?>
		</p>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
		<div class="vote-bar" role="img" aria-label="<?php echo esc_attr( sprintf( __( '%d голосов', 'rubezh' ), $votes ) ); ?>">
			<span style="width: <?php echo esc_attr( $percent ); ?>%"></span>
		</div>
		<p class="vote-count"><strong data-vote-count><?php echo esc_html( number_format_i18n( $votes ) ); ?></strong> <?php esc_html_e( 'голосов', 'rubezh' ); ?></p>
	</div>
	<div class="card-ballot__action">
		<?php if ( $open ) : ?>
			<button class="btn btn--burgundy vote-btn" type="button" data-vote="<?php the_ID(); ?>" <?php disabled( $voted ); ?>>
				<?php echo $voted ? esc_html__( 'Ваш голос учтён', 'rubezh' ) : esc_html__( 'Поддержать', 'rubezh' ); ?>
			</button>
		<?php else : ?>
			<span class="btn btn--ghost" aria-disabled="true"><?php echo esc_html( rubezh_status_label( $status ) ); ?></span>
		<?php endif; ?>
		<a class="card-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Подробнее', 'rubezh' ); ?></a>
	</div>
</article>
