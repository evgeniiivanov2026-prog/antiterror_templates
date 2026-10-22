<?php
/**
 * Initiative archive.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
atj_prime_votes( wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
?>
<main id="content" class="atj-main">
	<?php
	atj_page_header(
		__( 'Фонд инициатив', 'atjournal' ),
		is_tax( 'atj_area' ) ? single_term_title( '', false ) : __( 'Инициативы', 'atjournal' ),
		__( 'Голос читателя можно отдать и отозвать. Это мнение аудитории журнала, а не официальное голосование.', 'atjournal' )
	);
	?>
	<div class="atj-shell atj-loop">
		<?php if ( have_posts() ) : ?>
			<div class="atj-ledger">
				<?php
				$index = ( max( 1, (int) get_query_var( 'paged' ) ) - 1 ) * max( 1, (int) get_query_var( 'posts_per_page' ) );
				while ( have_posts() ) :
					the_post();
					++$index;
					get_template_part( 'template-parts/content', 'initiative', array( 'index' => $index ) );
				endwhile;
				?>
			</div>
			<p class="atj-fineprint">
				<?php
				if ( is_user_logged_in() ) {
					esc_html_e( 'Вы вошли в учётную запись: голос сохранится и на других устройствах, где вы вошли.', 'atjournal' );
				} else {
					$login = wp_login_url( get_post_type_archive_link( 'atj_initiative' ) ? get_post_type_archive_link( 'atj_initiative' ) : home_url( '/' ) );
					echo esc_html__( 'Без входа голос привязан к этому браузеру и не хранит ваше имя.', 'atjournal' ) . ' ';
					printf( '<a href="%s">%s</a>', esc_url( $login ), esc_html__( 'Войти', 'atjournal' ) );
				}
				?>
			</p>
			<?php atj_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
