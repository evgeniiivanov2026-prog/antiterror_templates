<?php
/**
 * Descriptions, Open Graph, and structured data.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

function gnss_meta_description(): string
{
    if (is_front_page()) {
        return 'Независимый журнал о ГЛОНАСС, GPS, Galileo и BeiDou: орбиты, сигналы, шкалы времени и точность позиционирования.';
    }
    if (is_singular()) {
        return gnss_dek();
    }
    if (is_category() || is_tag()) {
        $description = wp_strip_all_tags(term_description());
        if ($description !== '') {
            return $description;
        }
    }
    if (is_search()) {
        return 'Поиск по журналу GNSS.PRO: ' . get_search_query();
    }
    return 'Независимый журнал о ГЛОНАСС, GPS, Galileo и BeiDou: орбиты, сигналы, шкалы времени и точность позиционирования.';
}

add_action('wp_head', function (): void {
    $description = gnss_meta_description();
    $title = wp_get_document_title();
    $url = is_singular() ? get_permalink() : home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));
    if (!is_string($url) || $url === '') {
        $url = home_url('/');
    }
    $image = get_template_directory_uri() . '/assets/img/og.png';

    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="theme-color" content="#072421">' . "\n";
    echo '<link rel="icon" href="' . esc_url(get_template_directory_uri() . '/assets/img/favicon.svg') . '" type="image/svg+xml">' . "\n";
    echo '<meta property="og:locale" content="ru_RU">' . "\n";
    echo '<meta property="og:site_name" content="GNSS.PRO">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

    $graph = [];

    if (is_front_page()) {
        $graph[] = [
            '@type' => 'WebSite',
            'name' => 'GNSS.PRO',
            'url' => home_url('/'),
            'description' => gnss_meta_description(),
            'inLanguage' => 'ru-RU',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => home_url('/?s={search_term_string}'),
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    if (is_singular('post')) {
        $graph[] = [
            '@type' => 'Article',
            'headline' => get_the_title(),
            'description' => gnss_dek(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'inLanguage' => 'ru-RU',
            'mainEntityOfPage' => get_permalink(),
            'author' => [
                '@type' => 'Organization',
                'name' => 'Редакция GNSS.PRO',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'GNSS.PRO',
            ],
            'image' => $image,
        ];
    }

    if ($graph) {
        echo '<script type="application/ld+json">' . wp_json_encode([
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
}, 1);
