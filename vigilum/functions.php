<?php
/**
 * Vigilum — hybrid magazine theme for WordPress 6.4+.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VIGILUM_VERSION', '1.0.0' );
define( 'VIGILUM_DIR', get_template_directory() );
define( 'VIGILUM_URI', get_template_directory_uri() );

require_once VIGILUM_DIR . '/inc/setup.php';
require_once VIGILUM_DIR . '/inc/cpt.php';
require_once VIGILUM_DIR . '/inc/meta.php';
require_once VIGILUM_DIR . '/inc/voting.php';
require_once VIGILUM_DIR . '/inc/customizer.php';
require_once VIGILUM_DIR . '/inc/template-tags.php';
require_once VIGILUM_DIR . '/inc/patterns.php';
