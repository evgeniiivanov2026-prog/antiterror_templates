<?php
/**
 * Вестник антитеррора — самостоятельный журнальный шаблон.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ATJ_VERSION', '1.0.0' );
define( 'ATJ_DIR', get_template_directory() );
define( 'ATJ_URI', get_template_directory_uri() );

if ( ! isset( $content_width ) ) {
	$content_width = 760;
}

require ATJ_DIR . '/inc/setup.php';
require ATJ_DIR . '/inc/template-tags.php';
require ATJ_DIR . '/inc/cpt.php';
require ATJ_DIR . '/inc/meta.php';
require ATJ_DIR . '/inc/votes.php';
require ATJ_DIR . '/inc/customizer.php';
require ATJ_DIR . '/inc/shortcodes.php';
require ATJ_DIR . '/inc/patterns.php';
require ATJ_DIR . '/inc/starter.php';
