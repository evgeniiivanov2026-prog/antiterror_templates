<?php
/**
 * Comments.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="atj-comments">
	<?php if ( have_comments() ) : ?>
		<h2><?php esc_html_e( 'Обсуждение', 'antiterror-journal' ); ?></h2>
		<ol class="atj-comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply' => __( 'Добавить комментарий', 'antiterror-journal' ),
		)
	);
	?>
</div>
