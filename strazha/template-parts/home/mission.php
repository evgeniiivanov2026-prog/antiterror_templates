<?php
/**
 * Миссия.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = (string) strazha_mod( 'mission_title' );
$text  = (string) strazha_mod( 'mission_text' );
$html  = '';

if ( is_front_page() && ! is_home() ) {
	$page_id = (int) get_queried_object_id();
	$raw     = $page_id ? trim( (string) get_post_field( 'post_content', $page_id ) ) : '';
	if ( '' !== $raw ) {
		$html  = apply_filters( 'the_content', $raw );
		$title = get_the_title( $page_id );
		$text  = '';
	}
}
?>
<section class="section mission" id="mission">
	<div class="shell mission__grid">
		<header class="section__intro">
			<p class="section__num"><?php esc_html_e( '01 / Миссия', 'strazha' ); ?></p>
			<h2><?php echo esc_html( $title ); ?></h2>
		</header>
		<div class="mission__body">
			<?php if ( $html ) : ?>
				<div class="entry__content"><?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- фильтр the_content. ?></div>
			<?php else : ?>
				<p class="lede"><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>
			<?php $principles = strazha_principles(); ?>
			<?php if ( $principles ) : ?>
				<div class="principles">
					<?php foreach ( $principles as $index => $principle ) : ?>
						<article>
							<span><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
							<h3><?php echo esc_html( $principle['title'] ); ?></h3>
							<p><?php echo esc_html( $principle['text'] ); ?></p>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
