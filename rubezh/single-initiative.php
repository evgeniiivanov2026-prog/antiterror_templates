<?php
/**
 * Страница инициативы.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="rz-main">
	<?php
	while ( have_posts() ) :
		the_post();
		$status   = rubezh_initiative_status( get_the_ID() );
		$region   = (string) get_post_meta( get_the_ID(), '_rubezh_region', true );
		$proposer = (string) get_post_meta( get_the_ID(), '_rubezh_proposer', true );
		?>
		<article <?php post_class( 'rz-shell rz-initiative' ); ?>>
			<header class="rz-page-head">
				<?php rubezh_breadcrumbs(); ?>
				<p class="rz-index"><span>03</span> <?php esc_html_e( 'Инициатива', 'rubezh' ); ?></p>
				<p class="rz-kicker">
					<?php echo esc_html( rubezh_status_label( $status ) ); ?>
					<?php if ( '' !== $region ) : ?>
						<span> · <?php echo esc_html( $region ); ?></span>
					<?php endif; ?>
				</p>
				<h1><?php the_title(); ?></h1>
				<?php if ( '' !== $proposer ) : ?>
					<p class="rz-deck"><?php echo esc_html( $proposer ); ?></p>
				<?php endif; ?>
			</header>
			<div class="rz-initiative__grid">
				<div class="rz-entry">
					<?php the_content(); ?>
				</div>
				<aside class="rz-initiative__vote" aria-label="<?php esc_attr_e( 'Поддержка', 'rubezh' ); ?>">
					<?php rubezh_vote_controls( get_the_ID() ); ?>
					<p class="rz-disclaimer"><?php esc_html_e( 'Голос учитывается как общественная поддержка. Это не референдум и не поручение государственным органам.', 'rubezh' ); ?></p>
				</aside>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
