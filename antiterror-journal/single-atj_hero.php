<?php
/**
 * Single memorial card.
 *
 * @package AntiterrorJournal
 */

get_header();

$rank    = (string) get_post_meta( get_the_ID(), '_atj_rank', true );
$years   = (string) get_post_meta( get_the_ID(), '_atj_years', true );
$service = (string) get_post_meta( get_the_ID(), '_atj_service_label', true );
$date    = (string) get_post_meta( get_the_ID(), '_atj_memorial_date', true );
$awards  = (string) get_post_meta( get_the_ID(), '_atj_awards', true );
?>
<main id="primary" class="atj-page atj-memorial-page">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'atj-shell atj-hero-single' ); ?>>
			<?php atj_breadcrumbs(); ?>
			<div class="atj-hero-single__grid">
				<div class="atj-hero-single__media">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'atj-portrait' ); ?>
					<?php else : ?>
						<span class="atj-portrait__placeholder" aria-hidden="true"></span>
					<?php endif; ?>
				</div>
				<div>
					<p class="atj-kicker atj-kicker--gold"><?php esc_html_e( 'Память героев', 'antiterror-journal' ); ?></p>
					<h1><?php the_title(); ?></h1>
					<ul class="atj-facts">
						<?php if ( $rank ) : ?><li><span><?php esc_html_e( 'Звание', 'antiterror-journal' ); ?></span><?php echo esc_html( $rank ); ?></li><?php endif; ?>
						<?php if ( $service ) : ?><li><span><?php esc_html_e( 'Подразделение', 'antiterror-journal' ); ?></span><?php echo esc_html( $service ); ?></li><?php endif; ?>
						<?php if ( $years ) : ?><li><span><?php esc_html_e( 'Годы', 'antiterror-journal' ); ?></span><?php echo esc_html( $years ); ?></li><?php endif; ?>
						<?php if ( $date ) : ?><li><span><?php esc_html_e( 'Дата памяти', 'antiterror-journal' ); ?></span><?php echo esc_html( $date ); ?></li><?php endif; ?>
						<?php if ( $awards ) : ?><li><span><?php esc_html_e( 'Награды', 'antiterror-journal' ); ?></span><?php echo esc_html( $awards ); ?></li><?php endif; ?>
					</ul>
				</div>
			</div>
			<div class="entry-content atj-article__content">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
