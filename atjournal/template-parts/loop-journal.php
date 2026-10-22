<?php
/**
 * Main journal loop.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="atj-shell atj-loop">
	<?php if ( have_posts() ) : ?>
		<div class="atj-cards<?php echo is_paged() ? '' : ' atj-cards--lead'; ?>">
			<?php
			$index = 0;
			while ( have_posts() ) :
				the_post();
				$variant = ( 0 === $index && ! is_paged() ) ? 'feature' : 'tile';
				get_template_part(
					'template-parts/content',
					'card',
					array(
						'variant' => $variant,
						'heading' => 'h2',
					)
				);
				++$index;
			endwhile;
			?>
		</div>
		<?php atj_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content', 'none' ); ?>
	<?php endif; ?>
</div>
