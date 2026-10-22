<?php
/**
 * Комментарии к материалу.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<section class="rz-shell rz-comments" id="comments">
	<h2><?php esc_html_e( 'Комментарии', 'rubezh' ); ?></h2>
	<?php if ( have_comments() ) : ?>
		<ol class="rz-comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 40,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>
	<?php
	comment_form(
		array(
			'title_reply'   => __( 'Комментарий к материалу', 'rubezh' ),
			'label_submit'  => __( 'Отправить', 'rubezh' ),
			'class_submit'  => 'rz-button',
			'class_form'    => 'rz-comments__form',
		)
	);
	?>
</section>
