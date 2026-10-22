<?php
/**
 * Пустой фонд инициатив.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="rz-empty">
	<p><?php esc_html_e( 'Инициатив, открытых для поддержки, пока нет.', 'rubezh' ); ?></p>
	<?php if ( current_user_can( 'edit_posts' ) ) : ?>
		<a class="rz-textlink" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=initiative' ) ); ?>"><?php esc_html_e( 'Добавить инициативу', 'rubezh' ); ?></a>
	<?php endif; ?>
</div>
