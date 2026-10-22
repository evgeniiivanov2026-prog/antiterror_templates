<?php
/**
 * Комментарии.
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
<section class="comments-area" id="comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			printf(
				esc_html( _n( '%s комментарий', '%s комментариев', get_comments_number(), 'rubezh' ) ),
				esc_html( number_format_i18n( get_comments_number() ) )
			);
			?>
		</h2>
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

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="no-comments"><?php esc_html_e( 'Комментарии закрыты.', 'rubezh' ); ?></p>
	<?php endif; ?>

	<?php comment_form( array(
		'title_reply' => __( 'Отклик редакции и читателей', 'rubezh' ),
	) ); ?>
</section>
