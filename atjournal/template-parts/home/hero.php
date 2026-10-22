<?php
/**
 * Homepage statement and lead story.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$query = ( isset( $args['query'] ) && $args['query'] instanceof WP_Query ) ? $args['query'] : null;
?>
<section class="atj-hero" aria-labelledby="atj-hero-title">
	<div class="atj-shell atj-hero__grid">
		<div class="atj-hero__copy">
			<p class="atj-kicker atj-kicker--light"><?php echo esc_html( atj_mod( 'kicker' ) ); ?></p>
			<h1 id="atj-hero-title"><?php echo atj_headline_html( atj_mod( 'headline' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per line. ?></h1>
			<p class="atj-hero__lead"><?php echo esc_html( atj_mod( 'lead' ) ); ?></p>
			<div class="atj-actions">
				<a class="atj-button" href="<?php echo esc_url( atj_journal_url() ); ?>"><?php esc_html_e( 'Читать журнал', 'atjournal' ); ?> <span aria-hidden="true">→</span></a>
				<a class="atj-button atj-button--ghost" href="#iniciativy"><?php esc_html_e( 'Фонд инициатив', 'atjournal' ); ?> <span aria-hidden="true">↘</span></a>
			</div>
		</div>
		<div class="atj-hero__story">
			<?php
			if ( $query && $query->have_posts() ) :
				$query->the_post();
				get_template_part( 'template-parts/content', 'card', array( 'variant' => 'feature', 'heading' => 'h2' ) );
				wp_reset_postdata();
			else :
				?>
				<div class="atj-empty atj-empty--inverse">
					<p><?php esc_html_e( 'Здесь появится материал выпуска — первая опубликованная запись.', 'atjournal' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
