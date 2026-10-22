<?php
/**
 * Карточка памяти.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="rz-main">
	<?php
	while ( have_posts() ) :
		the_post();
		$years = (string) get_post_meta( get_the_ID(), '_rubezh_years', true );
		$role  = (string) get_post_meta( get_the_ID(), '_rubezh_role', true );
		$place = (string) get_post_meta( get_the_ID(), '_rubezh_place', true );
		$meta  = array_filter( array( $role, $place ) );
		?>
		<article <?php post_class( 'rz-shell rz-hero' ); ?>>
			<header class="rz-page-head">
				<?php rubezh_breadcrumbs(); ?>
				<p class="rz-index"><span>02</span> <?php esc_html_e( 'Память', 'rubezh' ); ?></p>
				<?php if ( get_post_meta( get_the_ID(), '_rubezh_sample', true ) ) : ?>
					<p class="rz-badge"><?php esc_html_e( 'Образец', 'rubezh' ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $years ) : ?>
					<p class="rz-hero__years"><?php echo esc_html( $years ); ?></p>
				<?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<?php if ( $meta ) : ?>
					<p class="rz-deck"><?php echo esc_html( implode( ' · ', $meta ) ); ?></p>
				<?php endif; ?>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="rz-hero__figure">
					<?php the_post_thumbnail( 'rubezh-portrait' ); ?>
				</figure>
			<?php endif; ?>
			<div class="rz-entry">
				<?php the_content(); ?>
			</div>
			<button class="rz-textlink" type="button" data-copy-link data-copied="<?php esc_attr_e( 'Ссылка скопирована', 'rubezh' ); ?>"><?php esc_html_e( 'Скопировать ссылку', 'rubezh' ); ?></button>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
