<?php
/**
 * Header.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link" href="#content"><?php esc_html_e( 'К содержанию', 'aegis' ); ?></a>
	<?php get_template_part( 'template-parts/site-header' ); ?>
	<div id="content" class="site-content">
