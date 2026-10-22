<?php
/**
 * Хлебные крошки.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Навигация по разделу', 'rubezh' ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Рубеж', 'rubezh' ); ?></a>
	<?php if ( is_singular( 'hero' ) ) : ?>
		<span>/</span><a href="<?php echo esc_url( rubezh_archive_link( 'hero' ) ); ?>"><?php esc_html_e( 'Память', 'rubezh' ); ?></a>
	<?php elseif ( is_singular( 'initiative' ) ) : ?>
		<span>/</span><a href="<?php echo esc_url( rubezh_archive_link( 'initiative' ) ); ?>"><?php esc_html_e( 'Фонд', 'rubezh' ); ?></a>
	<?php elseif ( is_single() ) : ?>
		<span>/</span><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' ) ); ?>"><?php esc_html_e( 'Журнал', 'rubezh' ); ?></a>
		<?php
		$cats = get_the_category();
		if ( $cats ) :
			?>
			<span>/</span><a href="<?php echo esc_url( get_category_link( $cats[0] ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a>
		<?php endif; ?>
	<?php endif; ?>
	<?php if ( is_singular() ) : ?>
		<span>/</span><span aria-current="page"><?php the_title(); ?></span>
	<?php endif; ?>
</nav>
