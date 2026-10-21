<?php
/**
 * Site header.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?> data-theme="light">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#content"><?php esc_html_e( 'К содержанию', 'vigilum' ); ?></a>
<div class="site-grain" aria-hidden="true"></div>
<div class="reading-progress" data-reading-progress hidden></div>
<?php get_template_part( 'template-parts/site', 'header' ); ?>
<div id="content" class="site-content">
