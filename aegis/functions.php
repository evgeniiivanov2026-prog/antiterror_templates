<?php
/**
 * Aegis — журнальный шаблон антитеррора.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AEGIS_VERSION', '1.0.0' );
define( 'AEGIS_DIR', get_template_directory() );
define( 'AEGIS_URI', get_template_directory_uri() );

require_once AEGIS_DIR . '/inc/setup.php';
require_once AEGIS_DIR . '/inc/template-tags.php';
require_once AEGIS_DIR . '/inc/cpt.php';
require_once AEGIS_DIR . '/inc/voting.php';
require_once AEGIS_DIR . '/inc/customizer.php';
require_once AEGIS_DIR . '/inc/demo.php';
