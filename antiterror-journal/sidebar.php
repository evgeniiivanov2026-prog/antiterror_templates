<?php
/**
 * Sidebar fallback.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>
<aside class="atj-sidebar" aria-label="<?php esc_attr_e( 'Боковая колонка', 'antiterror-journal' ); ?>">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
