<?php
/**
 * Single post.
 *
 * @package AntiterrorJournal
 */

get_header();
?>
<main id="primary" class="atj-page atj-shell">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content/content', 'single' );
		the_post_navigation(
			array(
				'prev_text' => '<span>' . esc_html__( 'Предыдущий', 'antiterror-journal' ) . '</span> %title',
				'next_text' => '<span>' . esc_html__( 'Следующий', 'antiterror-journal' ) . '</span> %title',
			)
		);
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	endwhile;
	?>
</main>
<?php
get_footer();
