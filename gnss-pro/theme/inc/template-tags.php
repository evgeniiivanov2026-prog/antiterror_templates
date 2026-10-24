<?php
/**
 * Presentation helpers.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

function gnss_plural(int $number, string $one, string $few, string $many): string
{
    $n = abs($number) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) {
        return $many;
    }
    if ($n1 > 1 && $n1 < 5) {
        return $few;
    }
    if ($n1 === 1) {
        return $one;
    }
    return $many;
}

function gnss_reading_minutes(?int $post_id = null): int
{
    $post_id = $post_id ?: get_the_ID();
    $text = wp_strip_all_tags((string) get_post_field('post_content', $post_id));
    $words = preg_match_all('/[\p{L}\p{N}]+/u', $text);
    return max(1, (int) ceil($words / 180));
}

function gnss_reading_label(?int $post_id = null): string
{
    $minutes = gnss_reading_minutes($post_id);
    return $minutes . ' ' . gnss_plural($minutes, 'минута', 'минуты', 'минут') . ' чтения';
}

function gnss_dek(?int $post_id = null): string
{
    $post_id = $post_id ?: get_the_ID();
    $excerpt = trim((string) get_post_field('post_excerpt', $post_id));
    if ($excerpt !== '') {
        return $excerpt;
    }
    return wp_trim_words(wp_strip_all_tags((string) get_post_field('post_content', $post_id)), 28, '…');
}

function gnss_kicker_html(?int $post_id = null): string
{
    $post_id = $post_id ?: get_the_ID();
    $cats = get_the_category($post_id);
    if (!$cats) {
        return '';
    }
    $cat = $cats[0];
    return '<a class="kicker" href="' . esc_url(get_category_link($cat)) . '">' . esc_html($cat->name) . '</a>';
}

function gnss_meta_line(?int $post_id = null): string
{
    $post_id = $post_id ?: get_the_ID();
    return get_the_date('j F Y', $post_id) . ' · ' . gnss_reading_label($post_id);
}

function gnss_journal_url(): string
{
    $id = (int) get_option('page_for_posts');
    if ($id) {
        $url = get_permalink($id);
        if (is_string($url)) {
            return $url;
        }
    }
    return home_url('/');
}

/**
 * @return WP_Term[]
 */
function gnss_sections(): array
{
    $terms = get_terms([
        'taxonomy' => 'category',
        'hide_empty' => false,
    ]);

    if (is_wp_error($terms) || !$terms) {
        return [];
    }

    $terms = array_values(array_filter(
        $terms,
        static fn (WP_Term $term): bool => get_term_meta($term->term_id, 'gnss_order', true) !== ''
    ));

    usort(
        $terms,
        static fn (WP_Term $a, WP_Term $b): int => ((int) get_term_meta($a->term_id, 'gnss_order', true)) <=> ((int) get_term_meta($b->term_id, 'gnss_order', true))
    );

    return $terms;
}

function gnss_lead_post(): ?WP_Post
{
    $pinned = get_page_by_path('tri-ploskosti-dvadtsat-chetyre-apparata', OBJECT, 'post');
    if ($pinned instanceof WP_Post && $pinned->post_status === 'publish') {
        return $pinned;
    }
    $latest = get_posts(['numberposts' => 1, 'post_status' => 'publish']);
    return $latest[0] ?? null;
}

function gnss_breadcrumbs(): void
{
    if (is_front_page()) {
        return;
    }

    echo '<nav class="crumbs" aria-label="Навигация по сайту">';
    echo '<a href="' . esc_url(home_url('/')) . '">Главная</a>';

    $sep = '<span aria-hidden="true">/</span>';

    if (is_home()) {
        echo $sep . '<span>' . esc_html(get_the_title((int) get_option('page_for_posts')) ?: 'Журнал') . '</span>';
    } elseif (is_category() || is_tag() || is_author() || is_date()) {
        echo $sep . '<span>' . esc_html(get_the_archive_title()) . '</span>';
    } elseif (is_singular('post')) {
        $cats = get_the_category();
        if ($cats) {
            echo $sep . '<a href="' . esc_url(get_category_link($cats[0])) . '">' . esc_html($cats[0]->name) . '</a>';
        }
        echo $sep . '<span>' . esc_html(get_the_title()) . '</span>';
    } elseif (is_page()) {
        echo $sep . '<span>' . esc_html(get_the_title()) . '</span>';
    } elseif (is_search()) {
        echo $sep . '<span>Поиск</span>';
    } elseif (is_404()) {
        echo $sep . '<span>Страница не найдена</span>';
    }

    echo '</nav>';
}

function gnss_found_label(int $number): string
{
    $word = gnss_plural($number, 'материал', 'материала', 'материалов');
    $verb = (abs($number) % 10 === 1 && abs($number) % 100 !== 11) ? 'Найден' : 'Найдено';
    return $verb . ' ' . $number . ' ' . $word;
}
