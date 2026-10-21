<?php
/**
 * Breadcrumbs.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_front_page() ) {
	return;
}
?>
<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Хлебные крошки', 'vigilum' ); ?>">
	<ol>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'vigilum' ); ?></a></li>
		<?php if ( is_singular( 'post' ) ) : ?>
			<li><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' ) ); ?>"><?php esc_html_e( 'Журнал', 'vigilum' ); ?></a></li>
			<li aria-current="page"><?php the_title(); ?></li>
		<?php elseif ( is_singular( 'vigilum_hero' ) ) : ?>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'vigilum_hero' ) ); ?>"><?php esc_html_e( 'Память', 'vigilum' ); ?></a></li>
			<li aria-current="page"><?php the_title(); ?></li>
		<?php elseif ( is_singular( 'vigilum_initiative' ) ) : ?>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'vigilum_initiative' ) ); ?>"><?php esc_html_e( 'Инициативы', 'vigilum' ); ?></a></li>
			<li aria-current="page"><?php the_title(); ?></li>
		<?php elseif ( is_home() ) : ?>
			<li aria-current="page"><?php esc_html_e( 'Журнал', 'vigilum' ); ?></li>
		<?php elseif ( is_search() ) : ?>
			<li aria-current="page"><?php esc_html_e( 'Поиск', 'vigilum' ); ?></li>
		<?php else : ?>
			<li aria-current="page"><?php echo wp_kses_post( get_the_archive_title() ); ?></li>
		<?php endif; ?>
	</ol>
</nav>
