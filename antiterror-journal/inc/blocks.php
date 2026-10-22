<?php
/**
 * Dynamic blocks. The front page is a canvas, so sections can be reordered
 * in the site editor without touching PHP.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'rubezh_register_blocks' );

/**
 * Register editor script, blocks and patterns.
 */
function rubezh_register_blocks() {
	wp_register_script(
		'rubezh-blocks',
		get_template_directory_uri() . '/assets/js/blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-server-side-render', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		RUBEZH_VERSION,
		true
	);

	$count = array(
		'type'    => 'number',
		'default' => 4,
	);
	$archive = array(
		'type'    => 'boolean',
		'default' => false,
	);

	$blocks = array(
		'rubezh/site-header'    => array( 'render_callback' => 'rubezh_render_header' ),
		'rubezh/site-footer'    => array( 'render_callback' => 'rubezh_render_footer' ),
		'rubezh/masthead'       => array( 'render_callback' => 'rubezh_render_masthead' ),
		'rubezh/journal-grid'   => array(
			'render_callback' => 'rubezh_render_journal_grid',
			'attributes'      => array( 'count' => array_merge( $count, array( 'default' => 5 ) ) ),
		),
		'rubezh/memorial'       => array(
			'render_callback' => 'rubezh_render_memorial',
			'attributes'      => array(
				'count'   => $count,
				'archive' => $archive,
			),
		),
		'rubezh/ethics'         => array( 'render_callback' => 'rubezh_render_ethics' ),
		'rubezh/initiatives'    => array(
			'render_callback' => 'rubezh_render_initiatives',
			'attributes'      => array(
				'count'   => $count,
				'archive' => $archive,
			),
		),
		'rubezh/signal'         => array( 'render_callback' => 'rubezh_render_signal' ),
		'rubezh/article'        => array( 'render_callback' => 'rubezh_render_article' ),
		'rubezh/hero-story'     => array( 'render_callback' => 'rubezh_render_hero_story' ),
		'rubezh/initiative-story' => array( 'render_callback' => 'rubezh_render_initiative_story' ),
		'rubezh/page-shell'     => array( 'render_callback' => 'rubezh_render_page' ),
		'rubezh/journal-archive'=> array( 'render_callback' => 'rubezh_render_journal_archive' ),
		'rubezh/search-results' => array( 'render_callback' => 'rubezh_render_search' ),
		'rubezh/not-found'      => array( 'render_callback' => 'rubezh_render_not_found' ),
		'rubezh/route'          => array( 'render_callback' => 'rubezh_render_route' ),
		'rubezh/canvas'         => array(
			'render_callback' => 'rubezh_render_canvas',
		),
	);

	foreach ( $blocks as $name => $args ) {
		register_block_type(
			$name,
			array_merge(
				array(
					'api_version'   => 3,
					'editor_script' => 'rubezh-blocks',
				),
				$args
			)
		);
	}

	register_block_pattern_category(
		'rubezh',
		array( 'label' => __( 'Рубеж', 'rubezh' ) )
	);

	register_block_pattern(
		'rubezh/front-page',
		array(
			'title'      => __( 'Главная журнала', 'rubezh' ),
			'categories' => array( 'rubezh' ),
			'content'    => implode(
				"\n",
				array(
					'<!-- wp:rubezh/masthead /-->',
					'<!-- wp:rubezh/journal-grid {"count":5} /-->',
					'<!-- wp:rubezh/memorial {"count":4} /-->',
					'<!-- wp:rubezh/ethics /-->',
					'<!-- wp:rubezh/initiatives {"count":4} /-->',
					'<!-- wp:rubezh/signal /-->',
				)
			),
		)
	);
}

/**
 * Page canvas. Inner blocks stay editable; the landmark is stable.
 *
 * @param array<string, mixed> $attributes Attributes.
 * @param string               $content    Rendered inner blocks.
 * @return string
 */
function rubezh_render_canvas( $attributes, $content ) {
	if ( false !== strpos( $content, '<main' ) ) {
		return $content;
	}
	return '<main id="content" class="rz-main">' . $content . '</main>';
}
