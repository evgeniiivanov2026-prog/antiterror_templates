<?php
/**
 * Magazine card.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'atj-card' ); ?>>
	<a class="atj-card__link" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="atj-card__media"><?php the_post_thumbnail( 'atj-thumb' ); ?></div>
		<?php endif; ?>
		<div class="atj-card__body">
			<?php atj_posted_on(); ?>
			<h3 class="atj-card__title"><?php the_title(); ?></h3>
			<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
		</div>
	</a>
</article>
