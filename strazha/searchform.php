<?php
/**
 * Форма поиска.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id = wp_unique_id( 'strazha-search-' );
?>
<form class="search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="sr-only" for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Поиск', 'strazha' ); ?></label>
	<input id="<?php echo esc_attr( $id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Поиск по журналу', 'strazha' ); ?>">
	<button type="submit"><?php esc_html_e( 'Найти', 'strazha' ); ?></button>
</form>
