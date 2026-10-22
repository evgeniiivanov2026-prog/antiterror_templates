<?php
/**
 * Рубеж — точка входа темы.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RUBEZH_VERSION', '1.0.0' );

require get_template_directory() . '/inc/icons.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/setup.php';
require get_template_directory() . '/inc/cpts.php';
require get_template_directory() . '/inc/meta.php';
require get_template_directory() . '/inc/votes.php';
require get_template_directory() . '/inc/ajax.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/samples.php';
require get_template_directory() . '/inc/patterns.php';
require get_template_directory() . '/inc/shortcodes.php';
