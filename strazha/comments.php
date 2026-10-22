<?php
/**
 * Комментарии.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}

if ( ! function_exists( 'strazha_comment' ) ) {
	/**
	 * Один комментарий.
	 *
	 * @param WP_Comment $comment Комментарий.
	 * @param array      $args    Аргументы.
	 * @param int        $depth   Глубина.
	 */
	function strazha_comment( $comment, $args, $depth ) {
		?>
		<li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'thread' ); ?>>
			<article>
				<header class="thread__head">
					<?php echo get_avatar( $comment, 44 ); ?>
					<div>
						<span class="thread__author"><?php comment_author(); ?></span>
						<time datetime="<?php echo esc_attr( get_comment_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_comment_date() ); ?></time>
					</div>
				</header>
				<div class="thread__body">
					<?php if ( '0' === $comment->comment_approved ) : ?>
						<p class="thread__hold"><?php esc_html_e( 'Комментарий ждёт проверки.', 'strazha' ); ?></p>
					<?php endif; ?>
					<?php comment_text(); ?>
				</div>
				<?php
				comment_reply_link(
					array_merge(
						$args,
						array(
							'depth'     => $depth,
							'max_depth' => $args['max_depth'],
							'before'    => '<div class="thread__reply">',
							'after'     => '</div>',
						)
					)
				);
				?>
			</article>
		<?php
	}
}
?>
<section class="comments" id="comments">
	<?php if ( have_comments() ) : ?>
		<h2>
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: comment count. */
					__( 'Комментарии · %s', 'strazha' ),
					number_format_i18n( get_comments_number() )
				)
			);
			?>
		</h2>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 44,
					'callback'    => 'strazha_comment',
				)
			);
			?>
		</ol>
		<?php the_comments_pagination(); ?>
	<?php endif; ?>
	<?php
	comment_form(
		array(
			'class_submit' => 'button',
		)
	);
	?>
</section>
