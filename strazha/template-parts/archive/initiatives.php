<?php
/**
 * Архив фонда.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_tax( 'initiative_field' ) ) {
	$title = single_term_title( '', false );
	$desc  = term_description();
} else {
	$title = __( 'Фонд инициатив', 'strazha' );
	$desc  = __( 'Гражданские предложения в сфере антитеррора. Голос — не пожертвование и не поручение действовать самостоятельно.', 'strazha' );
}

if ( have_posts() ) {
	strazha_prime_ballots( wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
}

$fields = get_terms(
	array(
		'taxonomy'   => 'initiative_field',
		'hide_empty' => true,
	)
);
?>
<main id="main" class="sheet">
	<header class="pagehead shell">
		<?php strazha_breadcrumbs(); ?>
		<p class="kicker"><?php esc_html_e( 'Голосование', 'strazha' ); ?></p>
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( $desc ) : ?>
			<div class="pagehead__lead"><?php echo wp_kses_post( is_tax() ? wpautop( $desc ) : wpautop( esc_html( $desc ) ) ); ?></div>
		<?php endif; ?>
		<?php strazha_status_filters(); ?>
		<?php if ( ! is_wp_error( $fields ) && $fields && ! is_tax( 'initiative_field' ) ) : ?>
			<nav class="filters filters--quiet" aria-label="<?php esc_attr_e( 'Направления', 'strazha' ); ?>">
				<?php foreach ( $fields as $field ) : ?>
					<a href="<?php echo esc_url( get_term_link( $field ) ); ?>"><?php echo esc_html( $field->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</header>
	<div class="shell bills-wrap">
		<?php if ( have_posts() ) : ?>
			<div class="bills">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/initiative' );
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
				<p><?php esc_html_e( 'В этой выборке инициатив нет.', 'strazha' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</main>
