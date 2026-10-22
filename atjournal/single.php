<?php
/**
 * Journal article.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="content" class="atj-main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'atj-article' ); ?>>
			<header class="atj-pagehead">
				<div class="atj-shell atj-article__head">
					<?php atj_breadcrumbs(); ?>
					<?php atj_sample_badge(); ?>
					<p class="atj-kicker"><?php echo esc_html( atj_kicker_text() ); ?></p>
					<h1><?php the_title(); ?></h1>
					<p class="atj-byline">
						<span><?php the_author(); ?></span>
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( atj_human_date( get_the_ID() ) ); ?></time>
						<span>
							<?php
							echo esc_html(
								sprintf(
									/* translators: %d: minutes */
									__( '%d мин чтения', 'atjournal' ),
									atj_reading_minutes()
								)
							);
							?>
						</span>
					</p>
				</div>
			</header>
			<div class="atj-shell atj-layout">
				<div class="atj-article__main">
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="atj-article__figure">
							<?php the_post_thumbnail( 'atj-feature' ); ?>
							<?php
							$caption = wp_get_attachment_caption( get_post_thumbnail_id() );
							if ( $caption ) :
								?>
								<figcaption><?php echo esc_html( $caption ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endif; ?>
					<div class="entry-content">
						<?php
						the_content();
						wp_link_pages(
							array(
								'before' => '<nav class="atj-pages">' . esc_html__( 'Страницы:', 'atjournal' ),
								'after'  => '</nav>',
							)
						);
						?>
					</div>
					<?php if ( has_tag() ) : ?>
						<p class="atj-tags"><?php the_tags( '', '' ); ?></p>
					<?php endif; ?>
					<?php
					$bio = get_the_author_meta( 'description' );
					if ( $bio ) :
						?>
						<section class="atj-author">
							<?php echo get_avatar( get_the_author_meta( 'ID' ), 72, '', get_the_author() ); ?>
							<div>
								<p class="atj-kicker"><?php esc_html_e( 'Автор', 'atjournal' ); ?></p>
								<h2><?php the_author(); ?></h2>
								<p><?php echo esc_html( $bio ); ?></p>
							</div>
						</section>
					<?php endif; ?>
					<?php
					$related = atj_related_query( get_the_ID() );
					if ( $related->have_posts() ) :
						?>
						<section class="atj-related">
							<h2><?php esc_html_e( 'Ещё в журнале', 'atjournal' ); ?></h2>
							<div class="atj-stack">
								<?php
								while ( $related->have_posts() ) :
									$related->the_post();
									get_template_part( 'template-parts/content', 'card', array( 'variant' => 'stack', 'heading' => 'h3' ) );
								endwhile;
								wp_reset_postdata();
								?>
							</div>
						</section>
					<?php endif; ?>
					<?php comments_template(); ?>
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
