<?php
/**
 * Footer.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</div>
	<?php get_template_part( 'template-parts/site-footer' ); ?>
	<button class="to-top" type="button" data-to-top hidden><?php esc_html_e( 'Наверх', 'aegis' ); ?></button>
</div>
<?php wp_footer(); ?>
</body>
</html>
