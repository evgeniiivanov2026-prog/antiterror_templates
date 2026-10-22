<?php
/**
 * Архив стены памяти.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_tax( 'hero_unit' ) ) {
	$title = single_term_title( '', false );
	$desc  = term_description();
} else {
	$title = __( 'Память героев', 'strazha' );
	$desc  = __( 'Имена, годы и биографии тех, кто противостоял террору.', 'strazha' );
}
?>
<main id="main" class="sheet sheet--memory">
	<header class="pagehead shell">
		<?php strazha_breadcrumbs(); ?>
		<p class="kicker"><?php esc_html_e( 'Мемориал', 'strazha' ); ?></p>
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( $desc ) : ?>
			<div class="pagehead__lead"><?php echo wp_kses_post( is_tax() ? wpautop( $desc ) : wpautop( esc_html( $desc ) ) ); ?></div>
		<?php endif; ?>
	</header>
	<div class="shell">
		<?php if ( have_posts() ) : ?>
			<div class="wall">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/hero' );
				endwhile;
				?>
			</div>
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 1,
					'prev_text' => __( 'Назад', 'strazha' ),
					'next_text' => __( 'Дальше', 'strazha' ),
				)
			);
			?>
		<?php else : ?>
			<div class="empty">
				<p><?php esc_html_e( 'Имён пока нет. Добавьте запись типа «Герой»: имя в заголовке, биография в тексте, годы и служба — в боковой карточке.', 'strazha' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</main>
