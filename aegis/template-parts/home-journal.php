<?php
/**
 * Magazine desk — featured + rail, JournalX composition.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$featured = new WP_Query( array(
	'post_type'           => 'post',
	'posts_per_page'      => 1,
	'post_status'         => 'publish',
	'ignore_sticky_posts' => false,
) );

$rail = new WP_Query( array(
	'post_type'           => 'post',
	'posts_per_page'      => 4,
	'post_status'         => 'publish',
	'post__not_in'        => wp_list_pluck( $featured->posts, 'ID' ),
	'ignore_sticky_posts' => true,
) );
?>
<section id="journal" class="band band--paper" aria-labelledby="journal-title">
	<div class="shell">
		<div class="band__head reveal">
			<div>
				<p class="section-kicker"><?php esc_html_e( '02 / Журнал', 'aegis' ); ?></p>
				<h2 id="journal-title"><?php esc_html_e( 'Редакционный стол', 'aegis' ); ?></h2>
			</div>
			<p><?php esc_html_e( 'Крупный вход и боковая колонна — журнальная сетка: расследования, профилактика, право и этика памяти.', 'aegis' ); ?></p>
			<a class="band__more" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: get_post_type_archive_link( 'post' ) ?: home_url( '/' ) ); ?>"><?php esc_html_e( 'Все материалы', 'aegis' ); ?> <span aria-hidden="true">→</span></a>
		</div>

		<?php if ( $featured->have_posts() ) : ?>
			<div class="desk">
				<div class="desk__featured reveal">
					<?php
					$featured->the_post();
					get_template_part( 'template-parts/card', 'post', array( 'variant' => 'featured' ) );
					wp_reset_postdata();
					?>
				</div>
				<div class="desk__rail">
					<?php
					if ( $rail->have_posts() ) :
						while ( $rail->have_posts() ) :
							$rail->the_post();
							get_template_part( 'template-parts/card', 'post', array( 'variant' => 'rail' ) );
						endwhile;
						wp_reset_postdata();
					endif;
					?>
				</div>
			</div>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'Опубликуйте первую статью журнала — здесь появится редакционный стол.', 'aegis' ); ?></p>
		<?php endif; ?>
	</div>
</section>
