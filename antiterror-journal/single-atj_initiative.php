<?php
/**
 * Single initiative.
 *
 * @package AntiterrorJournal
 */

get_header();

$region   = (string) get_post_meta( get_the_ID(), '_atj_region', true );
$author   = (string) get_post_meta( get_the_ID(), '_atj_author_label', true );
$deadline = (string) get_post_meta( get_the_ID(), '_atj_deadline', true );
$status   = get_post_meta( get_the_ID(), '_atj_status', true ) ?: 'open';
?>
<main id="primary" class="atj-page atj-shell">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'atj-initiative-single' ); ?>>
			<?php atj_breadcrumbs(); ?>
			<p class="atj-kicker"><?php echo 'closed' === $status ? esc_html__( 'Голосование закрыто', 'antiterror-journal' ) : esc_html__( 'Открытая инициатива', 'antiterror-journal' ); ?></p>
			<h1><?php the_title(); ?></h1>
			<ul class="atj-facts">
				<?php if ( $author ) : ?><li><span><?php esc_html_e( 'Автор', 'antiterror-journal' ); ?></span><?php echo esc_html( $author ); ?></li><?php endif; ?>
				<?php if ( $region ) : ?><li><span><?php esc_html_e( 'Регион', 'antiterror-journal' ); ?></span><?php echo esc_html( $region ); ?></li><?php endif; ?>
				<?php if ( $deadline ) : ?><li><span><?php esc_html_e( 'Срок', 'antiterror-journal' ); ?></span><?php echo esc_html( $deadline ); ?></li><?php endif; ?>
			</ul>
			<?php atj_vote_button( get_the_ID(), 'page' ); ?>
			<div class="entry-content atj-article__content">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	endwhile;
	?>
</main>
<?php
get_footer();
