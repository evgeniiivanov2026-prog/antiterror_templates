<?php
/**
 * Боковая колонка.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<aside class="rz-side" aria-label="<?php esc_attr_e( 'Колонка', 'rubezh' ); ?>">
	<?php
	if ( is_active_sidebar( 'rubezh-column' ) ) {
		dynamic_sidebar( 'rubezh-column' );
	} else {
		get_template_part( 'template-parts/layout/sidebar', 'default' );
	}
	?>
</aside>
