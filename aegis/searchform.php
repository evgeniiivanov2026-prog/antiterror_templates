<?php
/**
 * Search form.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e( 'Поиск', 'aegis' ); ?></span>
		<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Найти в журнале, памяти, инициативах', 'aegis' ); ?>">
	</label>
	<button type="submit" class="btn btn--primary"><?php esc_html_e( 'Искать', 'aegis' ); ?></button>
</form>
