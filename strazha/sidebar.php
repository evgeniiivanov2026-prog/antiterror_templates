<?php
/**
 * Колонка журнала.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<aside class="side" aria-label="<?php esc_attr_e( 'Дополнительно', 'strazha' ); ?>">
	<?php
	if ( is_active_sidebar( 'journal' ) ) {
		dynamic_sidebar( 'journal' );
	} else {
		get_template_part( 'template-parts/sidebar/default' );
	}
	?>
</aside>
