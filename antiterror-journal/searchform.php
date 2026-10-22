<?php
/**
 * Search form.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form role="search" method="get" class="atj-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="atj-search-field"><?php esc_html_e( 'Поиск', 'antiterror-journal' ); ?></label>
	<input id="atj-search-field" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Материал, герой, инициатива…', 'antiterror-journal' ); ?>">
	<button type="submit"><?php esc_html_e( 'Найти', 'antiterror-journal' ); ?></button>
</form>
