<?php
/**
 * Template Name: Фонд инициатив
 *
 * @package AntiterrorJournal
 */

get_header();

$initiatives = new WP_Query(
	array(
		'post_type'      => 'atj_initiative',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
		'meta_key'       => '_atj_votes',
		'orderby'        => array(
			'meta_value_num' => 'DESC',
			'date'           => 'DESC',
		),
	)
);
?>
<main id="primary" class="atj-page atj-shell">
	<header class="atj-page__head">
		<?php atj_breadcrumbs(); ?>
		<p class="atj-section-number">03 / <?php esc_html_e( 'Фонд', 'antiterror-journal' ); ?></p>
		<h1><?php the_title(); ?></h1>
		<?php if ( has_excerpt() ) : ?>
			<p><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
	</header>
	<?php
	if ( $initiatives->have_posts() ) :
		echo '<div class="atj-ballot">';
		$index = 1;
		while ( $initiatives->have_posts() ) :
			$initiatives->the_post();
			set_query_var( 'atj_rank', $index );
			get_template_part( 'template-parts/content/initiative', 'card' );
			$index++;
		endwhile;
		echo '</div>';
		wp_reset_postdata();
	else :
		get_template_part( 'template-parts/content/none' );
	endif;
	?>
</main>
<?php
get_footer();
