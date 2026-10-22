<?php
/**
 * 404.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$latest = new WP_Query(
	array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post_status'    => 'publish',
	)
);
?>
<main id="main" class="sheet">
	<header class="pagehead shell miss">
		<?php strazha_breadcrumbs(); ?>
		<p class="kicker">404</p>
		<h1><?php esc_html_e( 'Такой страницы нет.', 'strazha' ); ?></h1>
		<p class="lede"><?php esc_html_e( 'Адрес устарел или был набран с ошибкой. Вернитесь к выпуску или поищите по журналу.', 'strazha' ); ?></p>
		<?php get_search_form(); ?>
		<p><a class="textlink" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'На главную', 'strazha' ); ?></a></p>
	</header>
	<?php if ( $latest->have_posts() ) : ?>
		<div class="shell">
			<h2><?php esc_html_e( 'Свежие материалы', 'strazha' ); ?></h2>
			<div class="cards">
				<?php
				while ( $latest->have_posts() ) :
					$latest->the_post();
					get_template_part( 'template-parts/content/card' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
