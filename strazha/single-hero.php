<?php
/**
 * Страница героя.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="sheet sheet--memory">
	<?php
	while ( have_posts() ) :
		the_post();
		$years = (string) get_post_meta( get_the_ID(), '_strazha_years', true );
		$role  = (string) get_post_meta( get_the_ID(), '_strazha_role', true );
		$place = (string) get_post_meta( get_the_ID(), '_strazha_place', true );
		$unit  = strazha_term_name( get_the_ID(), 'hero_unit' );
		?>
		<article <?php post_class( 'memorial-sheet shell' ); ?>>
			<?php strazha_breadcrumbs(); ?>
			<header class="memorial-sheet__head">
				<figure>
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'strazha-portrait' ); ?>
					<?php else : ?>
						<span class="portrait__initials" aria-hidden="true"><?php echo esc_html( strazha_initials( get_the_title() ) ); ?></span>
					<?php endif; ?>
				</figure>
				<div>
					<?php if ( $unit || $place ) : ?>
						<p class="kicker"><?php echo esc_html( trim( $unit . ( $unit && $place ? ' · ' : '' ) . $place ) ); ?></p>
					<?php endif; ?>
					<h1><?php the_title(); ?></h1>
					<?php if ( $years ) : ?>
						<p class="portrait__years"><?php echo esc_html( $years ); ?></p>
					<?php endif; ?>
					<?php if ( $role ) : ?>
						<p class="memorial-sheet__role"><?php echo esc_html( $role ); ?></p>
					<?php endif; ?>
				</div>
			</header>
			<div class="entry">
				<div class="entry__content">
					<?php the_content(); ?>
				</div>
			</div>
			<p class="memorial-sheet__back">
				<a class="textlink" href="<?php echo esc_url( get_post_type_archive_link( 'hero' ) ); ?>"><?php esc_html_e( 'Вся стена памяти', 'strazha' ); ?><span aria-hidden="true"> →</span></a>
			</p>
			<?php edit_post_link( __( 'Править', 'strazha' ), '<p class="edit">', '</p>' ); ?>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
