<?php
/**
 * Search form.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e( 'Поиск', 'vigilum' ); ?></span>
		<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Материал, герой, инициатива…', 'vigilum' ); ?>">
	</label>
	<button type="submit"><?php esc_html_e( 'Искать', 'vigilum' ); ?></button>
</form>
