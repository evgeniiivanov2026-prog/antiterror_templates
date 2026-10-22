<?php
/**
 * Лента записей, если главная отдана статической странице.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
get_template_part( 'template-parts/archive/posts' );
get_footer();
