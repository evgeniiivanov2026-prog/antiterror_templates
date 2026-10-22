<?php
/**
 * Search form.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$atj_id = wp_unique_id( 'atj-s-' );
?>
<form class="atj-searchform" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $atj_id ); ?>"><?php esc_html_e( 'Поиск', 'atjournal' ); ?></label>
	<input id="<?php echo esc_attr( $atj_id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Поиск', 'atjournal' ); ?>">
	<button type="submit"><?php esc_html_e( 'Найти', 'atjournal' ); ?></button>
</form>
