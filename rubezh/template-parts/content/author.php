<?php
/**
 * Блок автора.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<aside class="author-box">
	<?php echo get_avatar( get_the_author_meta( 'ID' ), 72 ); ?>
	<div>
		<p class="rail-label"><?php esc_html_e( 'Автор материала', 'rubezh' ); ?></p>
		<h2><?php the_author(); ?></h2>
		<p><?php echo esc_html( get_the_author_meta( 'description' ) ?: __( 'Редакция журнала «Рубеж».', 'rubezh' ) ); ?></p>
	</div>
</aside>
