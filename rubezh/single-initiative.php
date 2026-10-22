<?php
/**
 * Страница инициативы с голосованием.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
while ( have_posts() ) :
	the_post();
	$max    = rubezh_max_votes();
	$votes  = (int) get_post_meta( get_the_ID(), '_rubezh_votes', true );
	$status = get_post_meta( get_the_ID(), '_rubezh_status', true ) ?: 'open';
	$author = get_post_meta( get_the_ID(), '_rubezh_author', true );
	$topic  = rubezh_first_term( get_the_ID(), 'initiative_topic' );
	$voted  = rubezh_has_voted( get_the_ID() );
	$open   = 'open' === $status;
	?>
<main id="primary" class="site-main shell interior initiative-page">
	<?php get_template_part( 'template-parts/content/breadcrumbs' ); ?>
	<article <?php post_class(); ?> data-initiative="<?php the_ID(); ?>">
		<p class="section-number"><?php esc_html_e( 'Фонд инициатив', 'rubezh' ); ?></p>
		<h1><?php the_title(); ?></h1>
		<p class="card-meta">
			<?php if ( $topic ) : ?><span class="chip"><?php echo esc_html( $topic->name ); ?></span><?php endif; ?>
			<span class="chip chip--ghost"><?php echo esc_html( rubezh_status_label( $status ) ); ?></span>
			<?php if ( $author ) : ?><span><?php echo esc_html( $author ); ?></span><?php endif; ?>
		</p>
		<div class="initiative-page__vote">
			<div class="vote-bar vote-bar--lg" role="img" aria-label="<?php echo esc_attr( sprintf( __( '%d голосов', 'rubezh' ), $votes ) ); ?>">
				<span style="width: <?php echo esc_attr( (int) round( ( $votes / $max ) * 100 ) ); ?>%"></span>
			</div>
			<p class="vote-count"><strong data-vote-count><?php echo esc_html( number_format_i18n( $votes ) ); ?></strong> <?php esc_html_e( 'голосов общественной поддержки', 'rubezh' ); ?></p>
			<?php if ( $open ) : ?>
				<button class="btn btn--burgundy vote-btn" type="button" data-vote="<?php the_ID(); ?>" <?php disabled( $voted ); ?>>
					<?php echo $voted ? esc_html__( 'Ваш голос учтён', 'rubezh' ) : esc_html__( 'Поддержать инициативу', 'rubezh' ); ?>
				</button>
			<?php endif; ?>
		</div>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</article>
</main>
	<?php
endwhile;
get_footer();
