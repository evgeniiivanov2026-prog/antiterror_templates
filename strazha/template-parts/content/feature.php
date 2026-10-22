<?php
/**
 * Обложечный материал.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term = strazha_term_name( get_the_ID(), 'category' );
?>
<article <?php post_class( 'feature' ); ?>>
	<a class="feature__link" href="<?php the_permalink(); ?>">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'strazha-feature', array( 'class' => 'feature__img' ) );
		}
		?>
		<span class="feature__body">
			<?php if ( $term ) : ?>
				<span class="kicker"><?php echo esc_html( $term ); ?></span>
			<?php endif; ?>
			<h2><?php the_title(); ?></h2>
			<span class="feature__dek"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></span>
		</span>
	</a>
</article>
