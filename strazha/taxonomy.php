<?php
/**
 * Таксономии служб и направлений.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$taxonomy = get_queried_object();
$taxonomy = ( $taxonomy && isset( $taxonomy->taxonomy ) ) ? $taxonomy->taxonomy : '';

if ( 'hero_unit' === $taxonomy ) {
	get_template_part( 'template-parts/archive/heroes' );
} elseif ( 'initiative_field' === $taxonomy ) {
	get_template_part( 'template-parts/archive/initiatives' );
} else {
	get_template_part( 'template-parts/archive/posts' );
}

get_footer();
