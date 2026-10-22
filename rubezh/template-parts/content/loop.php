<?php
/**
 * Лента карточек с колонкой.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="rz-shell rz-layout">
	<div>
		<?php if ( have_posts() ) : ?>
			<div class="rz-stack" data-post-list>
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part(
						'template-parts/content/card',
						null,
						array( 'variant' => 'row' )
					);
				endwhile;
				?>
			</div>
			<?php rubezh_journal_pager( is_home() || is_category() ); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/none' ); ?>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
</div>
