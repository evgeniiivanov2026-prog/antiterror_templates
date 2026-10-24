<?php
/**
 * Theme supports, assets, and head cleanup.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', function (): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets']);
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_editor_style(['assets/css/fonts.css', 'assets/css/editor.css']);

    register_nav_menus([
        'primary' => 'Основное меню',
        'footer' => 'Меню в подвале',
    ]);
});

add_action('wp_enqueue_scripts', function (): void {
    $dir = get_template_directory();
    $uri = get_template_directory_uri();

    wp_enqueue_style(
        'gnss-main',
        $uri . '/assets/css/main.css',
        [],
        (string) filemtime($dir . '/assets/css/main.css')
    );

    wp_enqueue_script(
        'gnss-theme',
        $uri . '/assets/js/theme.js',
        [],
        (string) filemtime($dir . '/assets/js/theme.js'),
        true
    );
});

add_filter('document_title_separator', function (): string {
    return '—';
});

add_filter('document_title_parts', function (array $parts): array {
    if (is_front_page()) {
        $parts['title'] = 'GNSS.PRO';
        $parts['tagline'] = 'Журнал о ГЛОНАСС и спутниковой навигации';
        unset($parts['site']);
        return $parts;
    }

    $parts['site'] = 'GNSS.PRO';
    return $parts;
});

add_filter('get_the_archive_title', function (string $title): string {
    if (is_category()) {
        return single_cat_title('', false);
    }
    if (is_tag()) {
        return single_tag_title('', false);
    }
    if (is_author()) {
        return get_the_author();
    }
    return $title;
});

add_action('init', function (): void {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
});

add_filter('the_content', function (string $content): string {
    if (is_admin()) {
        return $content;
    }
    $post_id = get_the_ID();
    if (!$post_id || get_post_meta($post_id, '_gnss_raw_html', true) !== '1') {
        return $content;
    }

    remove_filter('the_content', 'wpautop');
    remove_filter('the_content', 'shortcode_unautop');
    add_filter('the_content', 'gnss_restore_wpautop', 11);

    return $content;
}, 0);

function gnss_restore_wpautop(string $content): string
{
    remove_filter('the_content', 'gnss_restore_wpautop', 11);
    add_filter('the_content', 'wpautop');
    add_filter('the_content', 'shortcode_unautop');
    return $content;
}
