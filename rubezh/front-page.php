<?php
/**
 * Главная: порядок разделов задаёт настройщик.
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
	if ( is_front_page() && ! is_home() ) {
		while ( have_posts() ) {
			the_post();
			$content = trim( (string) get_the_content() );
			if ( '' !== $content ) {
				echo '<article class="rz-shell rz-front-note rz-entry">';
				the_content();
				echo '</article>';
			}
		}
		rewind_posts();
	}

	foreach ( rubezh_active_sections() as $section ) {
		get_template_part( 'template-parts/home/' . $section );
	}
	?>
</main>
<?php
get_footer();
