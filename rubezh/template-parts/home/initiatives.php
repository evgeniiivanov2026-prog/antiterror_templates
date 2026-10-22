<?php
/**
 * Фонд инициатив на главной.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$query = new WP_Query(
	array(
		'post_type'      => 'initiative',
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'no_found_rows'  => true,
	)
);
$archive = get_post_type_archive_link( 'initiative' );
?>
<section class="rz-section rz-fund" id="iniciativy" aria-labelledby="rz-fund-title">
	<div class="rz-shell rz-split">
		<header class="rz-split__intro">
			<p class="rz-index"><span>03</span> <?php esc_html_e( 'Фонд инициатив', 'rubezh' ); ?></p>
			<h2 id="rz-fund-title"><?php esc_html_e( 'Поддержать предложение', 'rubezh' ); ?></h2>
			<p class="rz-deck"><?php esc_html_e( 'Один голос с браузера или аккаунта. Его можно снять. Это открытая поддержка, а не официальное решение.', 'rubezh' ); ?></p>
			<?php if ( $archive ) : ?>
				<a class="rz-textlink" href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( 'Все инициативы', 'rubezh' ); ?></a>
			<?php endif; ?>
		</header>
		<div>
			<?php if ( $query->have_posts() ) : ?>
				<?php rubezh_render_ballots( $query ); ?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content/empty', 'initiative' ); ?>
			<?php endif; ?>
		</div>
	</div>
</section>
