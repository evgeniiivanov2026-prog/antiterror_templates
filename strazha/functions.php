<?php
/**
 * Стража — журнальный шаблон антитеррора.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STRAZHA_VERSION', '1.0.0' );
define( 'STRAZHA_DIR', get_template_directory() );
define( 'STRAZHA_URI', get_template_directory_uri() );

require STRAZHA_DIR . '/inc/template-tags.php';
require STRAZHA_DIR . '/inc/setup.php';
require STRAZHA_DIR . '/inc/cpt.php';
require STRAZHA_DIR . '/inc/meta.php';
require STRAZHA_DIR . '/inc/voting.php';
require STRAZHA_DIR . '/inc/customizer.php';
require STRAZHA_DIR . '/inc/ajax.php';
require STRAZHA_DIR . '/inc/home.php';
require STRAZHA_DIR . '/inc/patterns.php';
