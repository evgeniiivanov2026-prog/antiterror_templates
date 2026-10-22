<?php
/**
 * Рубеж — журнальный шаблон антитеррора.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RUBEZH_VERSION', '1.0.0' );

require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/setup.php';
require get_template_directory() . '/inc/post-types.php';
require get_template_directory() . '/inc/meta.php';
require get_template_directory() . '/inc/voting.php';
require get_template_directory() . '/inc/settings.php';
require get_template_directory() . '/inc/render-chrome.php';
require get_template_directory() . '/inc/render-front.php';
require get_template_directory() . '/inc/render-content.php';
require get_template_directory() . '/inc/blocks.php';
require get_template_directory() . '/inc/starter.php';
