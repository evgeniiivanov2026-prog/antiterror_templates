<?php
/**
 * Three editorial principles.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$principles = array(
	array(
		'n' => '01',
		't' => __( 'Законность', 'atjournal' ),
		'd' => __( 'Только правовое поле. Никаких призывов разбираться самостоятельно и никаких сенсаций вместо факта.', 'atjournal' ),
	),
	array(
		'n' => '02',
		't' => __( 'Память', 'atjournal' ),
		'd' => __( 'Карточки героев ведёт редакция. В них попадает то, что можно подтвердить, и не попадает то, о чём просили молчать.', 'atjournal' ),
	),
	array(
		'n' => '03',
		't' => __( 'Участие', 'atjournal' ),
		'd' => __( 'Открытый фонд инициатив и равный голос читателя. Это мнение аудитории, а не официальное голосование.', 'atjournal' ),
	),
);
?>
<section class="atj-section atj-principles" id="about" aria-labelledby="atj-principles-title">
	<div class="atj-shell">
		<div class="atj-section__intro atj-reveal">
			<p class="atj-num"><?php esc_html_e( '01  /  Принципы', 'atjournal' ); ?></p>
			<h2 id="atj-principles-title"><?php esc_html_e( 'Три правила, из которых собран сайт', 'atjournal' ); ?></h2>
		</div>
		<div class="atj-principles__grid">
			<?php foreach ( $principles as $item ) : ?>
				<article class="atj-reveal">
					<span><?php echo esc_html( $item['n'] ); ?></span>
					<h3><?php echo esc_html( $item['t'] ); ?></h3>
					<p><?php echo esc_html( $item['d'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
