<?php
/**
 * Memorial single.
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
		$years = (string) get_post_meta( get_the_ID(), '_atj_years', true );
		$role  = (string) get_post_meta( get_the_ID(), '_atj_role', true );
		$place = (string) get_post_meta( get_the_ID(), '_atj_place', true );
		$meta  = array_filter( array( $role, $place, $years ) );
		?>
		<article <?php post_class( 'atj-person' ); ?>>
			<header class="atj-personhead">
				<div class="atj-shell">
					<?php atj_breadcrumbs(); ?>
					<?php atj_sample_badge(); ?>
					<p class="atj-kicker atj-kicker--light"><?php esc_html_e( 'Память', 'atjournal' ); ?></p>
					<h1><?php the_title(); ?></h1>
					<?php if ( $meta ) : ?>
						<p class="atj-personhead__meta"><?php echo esc_html( implode( '  ·  ', $meta ) ); ?></p>
					<?php endif; ?>
				</div>
			</header>
			<div class="atj-shell atj-person__grid">
				<figure class="atj-person__portrait">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'atj-portrait' ); ?>
					<?php else : ?>
						<span class="atj-monogram atj-monogram--large" aria-hidden="true"><?php echo esc_html( atj_monogram( get_the_title() ) ); ?></span>
					<?php endif; ?>
				</figure>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</div>
			<?php
			$previous = get_adjacent_post( false, '', true );
			$next     = get_adjacent_post( false, '', false );
			if ( $previous || $next ) :
				?>
				<nav class="atj-shell atj-adjacent" aria-label="<?php esc_attr_e( 'Другие карточки', 'atjournal' ); ?>">
					<?php if ( $previous instanceof WP_Post ) : ?>
						<a href="<?php echo esc_url( get_permalink( $previous ) ); ?>"><span><?php esc_html_e( 'Ранее', 'atjournal' ); ?></span><?php echo esc_html( get_the_title( $previous ) ); ?></a>
					<?php endif; ?>
					<?php if ( $next instanceof WP_Post ) : ?>
						<a href="<?php echo esc_url( get_permalink( $next ) ); ?>"><span><?php esc_html_e( 'Далее', 'atjournal' ); ?></span><?php echo esc_html( get_the_title( $next ) ); ?></a>
					<?php endif; ?>
				</nav>
			<?php endif; ?>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
