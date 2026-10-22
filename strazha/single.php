<?php
/**
 * Запись журнала.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="sheet">
	<?php
	while ( have_posts() ) :
		the_post();
		$term = strazha_term_name( get_the_ID(), 'category' );
		?>
		<article <?php post_class( 'article' ); ?>>
			<header class="pagehead shell article__head">
				<?php strazha_breadcrumbs(); ?>
				<?php if ( $term ) : ?>
					<p class="kicker"><?php echo esc_html( $term ); ?></p>
				<?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<p class="article__meta">
					<span><?php echo esc_html( get_the_author() ); ?></span>
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time>
				</p>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="article__figure shell">
					<?php the_post_thumbnail( 'strazha-feature' ); ?>
					<?php if ( get_the_post_thumbnail_caption() ) : ?>
						<figcaption><?php echo wp_kses_post( get_the_post_thumbnail_caption() ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>
			<div class="layout shell layout--article">
				<div class="entry entry--dropcap">
					<div class="entry__content">
						<?php the_content(); ?>
					</div>
					<?php
					wp_link_pages(
						array(
							'before' => '<nav class="pagesplit" aria-label="' . esc_attr__( 'Страницы материала', 'strazha' ) . '">',
							'after'  => '</nav>',
						)
					);
					?>
					<?php if ( get_the_author_meta( 'description' ) ) : ?>
						<aside class="authorbox">
							<?php echo get_avatar( get_the_author_meta( 'ID' ), 64 ); ?>
							<div>
								<p><?php echo esc_html( get_the_author() ); ?></p>
								<p><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></p>
							</div>
						</aside>
					<?php endif; ?>
					<?php get_template_part( 'template-parts/content/related' ); ?>
					<?php
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>
				<?php get_sidebar(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
