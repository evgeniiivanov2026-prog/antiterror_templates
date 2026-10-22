<?php
/**
 * Карточка памяти.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$years = (string) get_post_meta( get_the_ID(), '_strazha_years', true );
$role  = (string) get_post_meta( get_the_ID(), '_strazha_role', true );
$place = (string) get_post_meta( get_the_ID(), '_strazha_place', true );
$unit  = strazha_term_name( get_the_ID(), 'hero_unit' );
$line  = $role ? $role : $unit;
?>
<article <?php post_class( 'portrait' ); ?>>
	<a href="<?php the_permalink(); ?>">
		<figure>
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'strazha-portrait' ); ?>
			<?php else : ?>
				<span class="portrait__initials" aria-hidden="true"><?php echo esc_html( strazha_initials( get_the_title() ) ); ?></span>
			<?php endif; ?>
		</figure>
		<h3><?php the_title(); ?></h3>
		<?php if ( $years ) : ?>
			<p class="portrait__years"><?php echo esc_html( $years ); ?></p>
		<?php endif; ?>
		<?php if ( $line ) : ?>
			<p class="portrait__role"><?php echo esc_html( $line ); ?></p>
		<?php endif; ?>
		<?php if ( $place ) : ?>
			<p class="portrait__place"><?php echo esc_html( $place ); ?></p>
		<?php endif; ?>
	</a>
</article>
