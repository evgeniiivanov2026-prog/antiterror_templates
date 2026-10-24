<?php
/**
 * GNSS.PRO theme bootstrap.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('GNSS_VERSION', '1.0.0');

require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/icons.php';
require get_template_directory() . '/inc/reference.php';

function gnss_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
        'navigation-widgets',
    ]);
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');

    register_nav_menus([
        'primary' => 'Главное меню',
    ]);
}
add_action('after_setup_theme', 'gnss_setup');

function gnss_assets(): void
{
    wp_enqueue_style(
        'gnss-fonts',
        'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:ital,wght@0,400;0,500;1,400&family=Manrope:wght@400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,480;0,8..60,600;0,8..60,700;1,8..60,480&family=Unbounded:wght@500;600;700&display=swap',
        [],
        null
    );
    wp_enqueue_style('gnss-style', get_stylesheet_uri(), ['gnss-fonts'], GNSS_VERSION);
    wp_enqueue_script('gnss-theme', get_template_directory_uri() . '/assets/js/theme.js', [], GNSS_VERSION, true);
}
add_action('wp_enqueue_scripts', 'gnss_assets');

function gnss_tune_content(): void
{
    if (is_admin()) {
        return;
    }

    remove_filter('the_content', 'wpautop');
    remove_filter('the_excerpt', 'wpautop');
}
add_action('wp', 'gnss_tune_content');

function gnss_archive_title_prefix(): string
{
    return '';
}
add_filter('get_the_archive_title_prefix', 'gnss_archive_title_prefix');

function gnss_document_title_separator(): string
{
    return '·';
}
add_filter('document_title_separator', 'gnss_document_title_separator');

function gnss_document_title(array $parts): array
{
    if (is_front_page()) {
        $parts['title'] = 'GNSS.PRO';
        $parts['tagline'] = 'Журнал о ГЛОНАСС и спутниковой навигации';
        unset($parts['site']);
    }

    return $parts;
}
add_filter('document_title_parts', 'gnss_document_title');

function gnss_excerpt_more(): string
{
    return '…';
}
add_filter('excerpt_more', 'gnss_excerpt_more');

function gnss_head_meta(): void
{
    $description = gnss_meta_description();
    $url = is_singular() ? (string) get_permalink(get_queried_object_id()) : home_url(add_query_arg([]));
    if (is_front_page()) {
        $url = home_url('/');
    }

    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="theme-color" content="#17232c">' . "\n";
    echo '<meta property="og:site_name" content="GNSS.PRO">' . "\n";
    echo '<meta property="og:locale" content="ru_RU">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr(wp_get_document_title()) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
    echo '<meta name="twitter:card" content="summary">' . "\n";

    if (is_singular('post')) {
        $published = get_post_time('c', true, get_queried_object_id());
        if (is_string($published)) {
            echo '<meta property="article:published_time" content="' . esc_attr($published) . '">' . "\n";
        }
    }

    if (is_front_page()) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'GNSS.PRO',
            'url' => home_url('/'),
            'description' => $description,
            'inLanguage' => 'ru',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => home_url('/?s={search_term_string}'),
                'query-input' => 'required name=search_term_string',
            ],
        ];
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    if (is_singular('post')) {
        $post_id = (int) get_queried_object_id();
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title($post_id),
            'datePublished' => get_post_time('c', true, $post_id),
            'dateModified' => get_post_modified_time('c', true, $post_id),
            'description' => $description,
            'inLanguage' => 'ru',
            'mainEntityOfPage' => get_permalink($post_id),
            'author' => [
                '@type' => 'Organization',
                'name' => 'Редакция GNSS.PRO',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'GNSS.PRO',
                'url' => home_url('/'),
            ],
        ];
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
}
add_action('wp_head', 'gnss_head_meta', 1);
