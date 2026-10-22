<?php
/**
 * Блок автора.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$author_id = (int) get_the_author_meta( 'ID' );
$bio       = get_the_author_meta( 'description', $author_id );
?>
<aside class="rz-author">
	<p class="rz-kicker"><?php esc_html_e( 'Автор', 'rubezh' ); ?></p>
	<h2><a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></a></h2>
	<?php if ( $bio ) : ?>
		<p><?php echo esc_html( $bio ); ?></p>
	<?php endif; ?>
</aside>
