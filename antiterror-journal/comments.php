<?php
/**
 * Comments, kept quiet and on the essay measure.
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
<section id="comments" class="rz-comments">
	<?php if ( have_comments() ) : ?>
		<h2>
			<?php
			printf(
				esc_html(
					sprintf(
						/* translators: comment count */
						_n( '%d отклик', '%d откликов', get_comments_number(), 'rubezh' ),
						get_comments_number()
					)
				)
			);
			?>
		</h2>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size'=> 0,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_form'           => 'rz-form',
			'class_submit'         => 'rz-button',
			'title_reply'          => __( 'Отклик', 'rubezh' ),
			'title_reply_before'   => '<h2 id="reply-title" class="rz-comments__title">',
			'title_reply_after'    => '</h2>',
			'comment_notes_before' => '<p class="rz-form__note">' . esc_html__( 'Без оскорблений и без подробностей, которые могут навредить.', 'rubezh' ) . '</p>',
			'label_submit'         => __( 'Отправить', 'rubezh' ),
		)
	);
	?>
</section>
