<?php
/**
 * Главная: обложка и разделы.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main">
	<?php get_template_part( 'template-parts/home/opening' ); ?>
	<nav class="spine" aria-label="<?php esc_attr_e( 'Разделы выпуска', 'strazha' ); ?>">
		<?php if ( strazha_flag( 'show_mission' ) ) : ?><a href="#mission">01</a><?php endif; ?>
		<?php if ( strazha_flag( 'show_journal' ) ) : ?><a href="#journal">02</a><?php endif; ?>
		<?php if ( strazha_flag( 'show_memory' ) ) : ?><a href="#memory">03</a><?php endif; ?>
		<?php if ( strazha_flag( 'show_fund' ) ) : ?><a href="#fund">04</a><?php endif; ?>
	</nav>
	<?php
	/**
	 * Разделы главной. Приоритеты: миссия 10, журнал 20, память 30, фонд 40, 112 50.
	 */
	do_action( 'strazha_home_sections' );
	?>
</main>
<?php
get_footer();
