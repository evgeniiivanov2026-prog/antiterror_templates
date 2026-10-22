<?php
/**
 * Featured magazine story.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'atj-featured' ); ?>>
	<a class="atj-featured__link" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="atj-featured__media"><?php the_post_thumbnail( 'atj-featured' ); ?></div>
		<?php endif; ?>
		<div class="atj-featured__body">
			<p class="atj-kicker"><?php esc_html_e( 'Главный материал', 'antiterror-journal' ); ?></p>
			<?php atj_posted_on(); ?>
			<h3 class="atj-featured__title"><?php the_title(); ?></h3>
			<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32 ) ); ?></p>
			<span class="atj-readmore"><?php esc_html_e( 'Читать', 'antiterror-journal' ); ?> <span aria-hidden="true">↗</span></span>
		</div>
	</a>
</article>
