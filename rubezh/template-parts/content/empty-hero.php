<?php
/**
 * Пустой раздел памяти.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="rz-empty">
	<p><?php esc_html_e( 'В разделе памяти пока нет записей.', 'rubezh' ); ?></p>
	<p><?php esc_html_e( 'Добавляйте имя, годы, роль и короткую справку только после проверки.', 'rubezh' ); ?></p>
	<?php if ( current_user_can( 'edit_posts' ) ) : ?>
		<a class="rz-textlink" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=hero' ) ); ?>"><?php esc_html_e( 'Добавить карточку', 'rubezh' ); ?></a>
	<?php endif; ?>
</div>
