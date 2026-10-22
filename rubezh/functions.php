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
define( 'RUBEZH_DIR', get_template_directory() );
define( 'RUBEZH_URI', get_template_directory_uri() );

require_once RUBEZH_DIR . '/inc/setup.php';
require_once RUBEZH_DIR . '/inc/enqueue.php';
require_once RUBEZH_DIR . '/inc/cpt.php';
require_once RUBEZH_DIR . '/inc/meta.php';
require_once RUBEZH_DIR . '/inc/voting.php';
require_once RUBEZH_DIR . '/inc/customizer.php';
require_once RUBEZH_DIR . '/inc/template-tags.php';
require_once RUBEZH_DIR . '/inc/demo.php';
