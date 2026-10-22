<?php
/**
 * Magazine homepage.
 *
 * @package AntiterrorJournal
 */

get_header();
?>

<nav class="atj-rail" aria-label="<?php esc_attr_e( 'Главы выпуска', 'antiterror-journal' ); ?>">
	<a href="#cover" data-rail="cover"><span>00</span><?php esc_html_e( 'Обложка', 'antiterror-journal' ); ?></a>
	<a href="#journal" data-rail="journal"><span>01</span><?php esc_html_e( 'Журнал', 'antiterror-journal' ); ?></a>
	<a href="#memory" data-rail="memory"><span>02</span><?php esc_html_e( 'Память', 'antiterror-journal' ); ?></a>
	<a href="#fund" data-rail="fund"><span>03</span><?php esc_html_e( 'Фонд', 'antiterror-journal' ); ?></a>
</nav>

<main id="primary" class="atj-home">
	<?php
	get_template_part( 'template-parts/home/hero' );
	get_template_part( 'template-parts/home/magazine' );
	get_template_part( 'template-parts/home/memorial' );
	get_template_part( 'template-parts/home/initiatives' );
	get_template_part( 'template-parts/home/cta' );
	?>
</main>

<?php
get_footer();
