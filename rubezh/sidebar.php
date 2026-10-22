<?php
/**
 * Боковая колонка.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>
<aside id="secondary" class="widget-area" aria-label="<?php esc_attr_e( 'Боковая колонка', 'rubezh' ); ?>">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
