<?php
/**
 * Sidebar.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>
<aside class="widgets" role="complementary">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
