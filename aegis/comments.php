<?php
/**
 * Comments.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments">
	<?php if ( have_comments() ) : ?>
		<h2><?php esc_html_e( 'Обсуждение', 'aegis' ); ?></h2>
		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'short_ping' => true,
			) );
			?>
		</ol>
		<?php the_comments_pagination(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="empty-note"><?php esc_html_e( 'Комментарии закрыты.', 'aegis' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</section>
