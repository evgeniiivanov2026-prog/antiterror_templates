<?php
/**
 * Принципы издания.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="rz-section rz-principles" id="principi" aria-labelledby="rz-principles-title">
	<div class="rz-shell">
		<div class="rz-split">
			<header class="rz-split__intro">
				<p class="rz-index"><span>04</span> <?php esc_html_e( 'Принципы', 'rubezh' ); ?></p>
				<h2 id="rz-principles-title"><?php echo esc_html( rubezh_mod( 'mission_title' ) ); ?></h2>
			</header>
			<div>
				<p class="rz-lede"><?php echo esc_html( rubezh_mod( 'mission_lede' ) ); ?></p>
				<p class="rz-deck"><?php echo esc_html( rubezh_mod( 'mission_text' ) ); ?></p>
			</div>
		</div>
		<div class="rz-principles__grid">
			<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<article>
					<p class="rz-principles__index"><?php echo esc_html( sprintf( '%02d', $i ) ); ?></p>
					<h3><?php echo esc_html( rubezh_mod( 'principle_' . $i . '_title' ) ); ?></h3>
					<p><?php echo esc_html( rubezh_mod( 'principle_' . $i . '_text' ) ); ?></p>
				</article>
			<?php endfor; ?>
		</div>
	</div>
</section>
