<?php
/**
 * Initiative single + vote.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$data    = vigilum_initiative_data( get_the_ID() );
$percent = ( $data['goal'] > 0 ) ? min( 100, (int) round( ( $data['votes'] / $data['goal'] ) * 100 ) ) : 0;
?>
<main id="primary" class="site-main interior">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'initiative-article' ); ?> data-initiative="<?php the_ID(); ?>">
			<div class="shell">
				<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
				<header class="archive-head">
					<p class="kicker"><?php echo esc_html( vigilum_status_label( $data['status'] ) ); ?></p>
					<h1><?php the_title(); ?></h1>
					<?php if ( $data['author'] ) : ?>
						<p class="archive-desc"><?php echo esc_html( $data['author'] ); ?></p>
					<?php endif; ?>
				</header>
				<div class="initiative-layout">
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
					<aside class="initiative-aside">
						<?php if ( $data['goal'] > 0 ) : ?>
							<div class="vote-meter vote-meter--lg" role="img" aria-label="<?php echo esc_attr( sprintf( __( '%1$s из %2$s голосов', 'vigilum' ), $data['votes'], $data['goal'] ) ); ?>">
								<span style="width:<?php echo esc_attr( $percent ); ?>%"></span>
							</div>
						<?php endif; ?>
						<?php get_template_part( 'template-parts/vote', 'box' ); ?>
						<p class="aside-note"><?php esc_html_e( 'Один голос с устройства. Голос нельзя отозвать. Инициативы рассматриваются фондом, а не исполняются автоматически.', 'vigilum' ); ?></p>
					</aside>
				</div>
				<?php if ( comments_open() || get_comments_number() ) : ?>
					<div class="comments-wrap"><?php comments_template(); ?></div>
				<?php endif; ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
