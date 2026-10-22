<?php
/**
 * Antiterror Journal — bootstrap.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ATJ_VERSION', '1.0.0' );
define( 'ATJ_SLUG', 'antiterror-journal' );

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/cpt.php';
require_once get_template_directory() . '/inc/meta.php';
require_once get_template_directory() . '/inc/voting.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/template-tags.php';
require_once get_template_directory() . '/inc/widgets.php';
require_once get_template_directory() . '/inc/patterns.php';
