<?php
/**
 * Header search.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <label>
        <span class="screen-reader">Искать по журналу</span>
        <input type="search" class="search-field" placeholder="Поиск" value="<?php echo esc_attr(get_search_query(false)); ?>" name="s">
    </label>
    <button type="submit" class="search-submit">Найти</button>
</form>
