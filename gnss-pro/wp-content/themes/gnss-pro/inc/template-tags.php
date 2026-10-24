<?php
/**
 * Template helpers for GNSS.PRO.
 */

if (!defined('ABSPATH')) {
    exit;
}

function gnss_rubric_order(): array
{
    return [
        'glonass',
        'sistemy-gnss',
        'signaly',
        'vysokaya-tochnost',
        'otrasli',
        'priemniki',
    ];
}

function gnss_rubric_palette(string $slug): array
{
    $map = [
        'glonass' => ['#1f4d4a', '#f4efe4', '#e2b15a'],
        'sistemy-gnss' => ['#17232c', '#f4efe4', '#c45c26'],
        'signaly' => ['#3c3224', '#f4efe4', '#e2b15a'],
        'vysokaya-tochnost' => ['#6e3414', '#f4efe4', '#f0c9a0'],
        'otrasli' => ['#24362e', '#f4efe4', '#9dcec2'],
        'priemniki' => ['#2a3144', '#f4efe4', '#d7c4a3'],
    ];

    return $map[$slug] ?? ['#17232c', '#f4efe4', '#c45c26'];
}

function gnss_post_rubric(?int $post_id = null): ?WP_Term
{
    $post_id = $post_id ?: (int) get_the_ID();
    $categories = get_the_category($post_id);
    if (!$categories) {
        return null;
    }

    $order = array_flip(gnss_rubric_order());
    usort($categories, static function (WP_Term $a, WP_Term $b) use ($order): int {
        $left = $order[$a->slug] ?? 99;
        $right = $order[$b->slug] ?? 99;
        return $left <=> $right;
    });

    return $categories[0];
}

function gnss_reading_minutes(?int $post_id = null): int
{
    $post_id = $post_id ?: (int) get_the_ID();
    $text = wp_strip_all_tags((string) get_post_field('post_content', $post_id));
    $words = preg_match_all('/[\p{L}\p{N}]+/u', $text);
    $words = $words ? (int) $words : 0;

    return max(1, (int) ceil($words / 180));
}

function gnss_format_date(?int $timestamp = null): string
{
    if ($timestamp === null) {
        $timestamp = (int) get_post_timestamp();
    }

    $months = [
        1 => 'января',
        2 => 'февраля',
        3 => 'марта',
        4 => 'апреля',
        5 => 'мая',
        6 => 'июня',
        7 => 'июля',
        8 => 'августа',
        9 => 'сентября',
        10 => 'октября',
        11 => 'ноября',
        12 => 'декабря',
    ];

    $day = (int) wp_date('j', $timestamp);
    $month = (int) wp_date('n', $timestamp);
    $year = wp_date('Y', $timestamp);

    return $day . ' ' . ($months[$month] ?? '') . ' ' . $year;
}

function gnss_posts_url(): string
{
    $page_id = (int) get_option('page_for_posts');
    if ($page_id) {
        $url = get_permalink($page_id);
        if (is_string($url) && $url !== '') {
            return $url;
        }
    }

    return home_url('/zhurnal/');
}

function gnss_meta_description(): string
{
    if (is_singular()) {
        $excerpt = get_post_field('post_excerpt', get_queried_object_id());
        if (is_string($excerpt) && $excerpt !== '') {
            return wp_strip_all_tags($excerpt);
        }
    }

    if (is_category() || is_tag() || is_tax()) {
        $description = term_description();
        if (is_string($description) && $description !== '') {
            return wp_strip_all_tags($description);
        }
    }

    return 'GNSS.PRO — журнал о ГЛОНАСС и глобальных навигационных спутниковых системах: орбиты, сигналы, высокая точность, транспорт и служба времени.';
}

function gnss_ordered_categories(): array
{
    $found = [];
    $categories = get_categories([
        'hide_empty' => false,
        'taxonomy' => 'category',
    ]);

    foreach ($categories as $category) {
        $found[$category->slug] = $category;
    }

    $ordered = [];
    foreach (gnss_rubric_order() as $slug) {
        if (isset($found[$slug])) {
            $ordered[] = $found[$slug];
            unset($found[$slug]);
        }
    }

    foreach ($found as $category) {
        if (in_array($category->slug, ['uncategorized', 'bez-rubriki'], true)) {
            continue;
        }
        if (in_array($category->name, ['Без рубрики', 'Uncategorized'], true)) {
            continue;
        }
        if ((int) $category->count === 0) {
            continue;
        }
        $ordered[] = $category;
    }

    return $ordered;
}

function gnss_related_query(int $post_id): WP_Query
{
    $categories = wp_get_post_categories($post_id);
    $query = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => 3,
        'post__not_in' => [$post_id],
        'category__in' => $categories ?: [0],
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    ]);

    if ((int) $query->post_count >= 1) {
        return $query;
    }

    return new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => 3,
        'post__not_in' => [$post_id],
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    ]);
}

function gnss_fallback_menu(): void
{
    $links = [
        home_url('/') => 'Главная',
        gnss_posts_url() => 'Журнал',
        home_url('/sistemy/') => 'Системы',
        home_url('/glossariy/') => 'Глоссарий',
        home_url('/o-zhurnale/') => 'О журнале',
    ];

    echo '<ul class="menu">';
    foreach ($links as $url => $label) {
        echo '<li><a href="' . esc_url($url) . '">' . esc_html($label) . '</a></li>';
    }
    echo '</ul>';
}
