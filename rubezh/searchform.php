<?php
/**
 * Форма поиска.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="rubezh-search"><?php esc_html_e( 'Поиск по журналу', 'rubezh' ); ?></label>
	<input id="rubezh-search" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Имя, тема, инициатива…', 'rubezh' ); ?>">
	<button class="btn btn--burgundy" type="submit"><?php esc_html_e( 'Найти', 'rubezh' ); ?></button>
</form>
