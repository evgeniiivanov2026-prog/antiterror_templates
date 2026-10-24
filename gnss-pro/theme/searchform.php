<?php
/**
 * Search form.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<form class="search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
    <label>
        <span class="screen-reader-text">Поиск по журналу</span>
        <input type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="Поиск">
    </label>
    <button type="submit">Найти</button>
</form>
