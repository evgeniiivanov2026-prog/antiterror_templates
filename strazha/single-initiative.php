<?php
/**
 * Страница инициативы.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="sheet">
	<?php
	while ( have_posts() ) :
		the_post();
		strazha_prime_ballots( array( get_the_ID() ) );
		$field    = strazha_term_name( get_the_ID(), 'initiative_field' );
		$statuses = strazha_statuses();
		$status   = strazha_initiative_status( get_the_ID() );
		?>
		<div class="layout shell layout--article">
			<article <?php post_class( 'article' ); ?>>
				<header class="article__head">
					<?php strazha_breadcrumbs(); ?>
					<p class="bill__meta">
						<?php if ( $field ) : ?>
							<span><?php echo esc_html( $field ); ?></span>
						<?php endif; ?>
						<span class="status status--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $statuses[ $status ] ); ?></span>
					</p>
					<h1><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</header>
				<div class="entry">
					<div class="entry__content">
						<?php the_content(); ?>
					</div>
				</div>
				<?php edit_post_link( __( 'Править', 'strazha' ), '<p class="edit">', '</p>' ); ?>
			</article>
			<aside class="side side--vote">
				<div class="vote-panel">
					<p class="widget__title"><?php esc_html_e( 'Голос', 'strazha' ); ?></p>
					<?php strazha_vote_control( get_the_ID() ); ?>
					<p class="vote-panel__note">
						<?php
						if ( 'collecting' === $status ) {
							esc_html_e( 'Один голос с этого браузера. Повторное нажатие отзывает его, пока инициатива открыта. Это не сбор средств.', 'strazha' );
						} else {
							esc_html_e( 'Голосование закрыто. Счётчик сохранён.', 'strazha' );
						}
						?>
					</p>
					<a class="textlink" href="<?php echo esc_url( get_post_type_archive_link( 'initiative' ) ); ?>"><?php esc_html_e( 'Все инициативы', 'strazha' ); ?></a>
				</div>
			</aside>
		</div>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
