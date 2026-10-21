<?php
/**
 * Comments.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<section class="comments" id="comments">
	<h2><?php esc_html_e( 'Обсуждение', 'vigilum' ); ?></h2>
	<?php if ( have_comments() ) : ?>
		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'short_ping' => true,
				'avatar_size'=> 48,
			) );
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>
	<?php
	comment_form( array(
		'title_reply'          => __( 'Оставить комментарий', 'vigilum' ),
		'label_submit'         => __( 'Отправить', 'vigilum' ),
		'comment_notes_before' => '<p class="comment-notes">' . esc_html__( 'Комментарии модерируются. Не публикуйте персональные данные и оперативную информацию.', 'vigilum' ) . '</p>',
	) );
	?>
</section>
