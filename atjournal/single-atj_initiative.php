<?php
/**
 * Initiative single with a sticky vote panel.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="content" class="atj-main">
	<?php
	while ( have_posts() ) :
		the_post();
		atj_prime_votes( array( get_the_ID() ) );
		$status = atj_initiative_status( get_the_ID() );
		?>
		<article <?php post_class( 'atj-proposal' ); ?>>
			<header class="atj-pagehead">
				<div class="atj-shell">
					<?php atj_breadcrumbs(); ?>
					<?php atj_sample_badge(); ?>
					<p class="atj-kicker"><?php echo esc_html( atj_kicker_text() ); ?></p>
					<h1><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="atj-pagehead__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</div>
			</header>
			<div class="atj-shell atj-layout">
				<div class="entry-content">
					<?php the_content(); ?>
					<?php comments_template(); ?>
				</div>
				<aside class="atj-vote-panel" aria-label="<?php esc_attr_e( 'Голосование', 'atjournal' ); ?>">
					<p class="atj-kicker"><?php echo 'open' === $status ? esc_html__( 'Открыто', 'atjournal' ) : esc_html__( 'Завершено', 'atjournal' ); ?></p>
					<h2><?php esc_html_e( 'Голос читателя', 'atjournal' ); ?></h2>
					<?php echo atj_vote_markup( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<p><?php esc_html_e( 'Это мнение аудитории журнала, а не официальное голосование. Повторное нажатие отзывает голос, пока набор открыт.', 'atjournal' ); ?></p>
					<?php
					$archive = get_post_type_archive_link( 'atj_initiative' );
					if ( $archive ) :
						?>
						<a class="atj-textlink" href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( 'Все инициативы', 'atjournal' ); ?> <span aria-hidden="true">→</span></a>
					<?php endif; ?>
				</aside>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
