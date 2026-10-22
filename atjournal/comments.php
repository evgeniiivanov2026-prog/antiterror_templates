<?php
/**
 * Comments.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="atj-comments">
	<?php if ( have_comments() ) : ?>
		<h2><?php echo esc_html( atj_comments_label( (int) get_comments_number() ) ); ?></h2>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>
		<?php
		the_comments_pagination(
			array(
				'prev_text' => __( '← Ранее', 'atjournal' ),
				'next_text' => __( 'Дальше →', 'atjournal' ),
			)
		);
		?>
	<?php endif; ?>

	<?php if ( comments_open() ) : ?>
		<?php
		comment_form(
			array(
				'title_reply'          => __( 'Комментарий', 'atjournal' ),
				'title_reply_to'       => __( 'Ответ для %s', 'atjournal' ),
				'label_submit'         => __( 'Отправить', 'atjournal' ),
				'comment_notes_before' => '<p class="comment-notes">' . esc_html__( 'Без оскорблений, без данных третьих лиц и без оперативных подробностей.', 'atjournal' ) . '</p>',
			)
		);
		?>
	<?php elseif ( get_comments_number() ) : ?>
		<p class="atj-fineprint"><?php esc_html_e( 'Обсуждение закрыто.', 'atjournal' ); ?></p>
	<?php endif; ?>
</section>
