<?php
/**
 * Форма поиска.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$id = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'rz-search-' ) : 'rz-search-field';
?>
<form class="rz-search rz-search--inline" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="rz-sr" for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Поиск', 'rubezh' ); ?></label>
	<input id="<?php echo esc_attr( $id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Имя, материал, инициатива', 'rubezh' ); ?>">
	<button class="rz-button" type="submit"><?php esc_html_e( 'Найти', 'rubezh' ); ?></button>
</form>
