<?php
/**
 * Plugin Name: GNSS.PRO Seed
 * Description: Создаёт рубрики, страницы и стартовые материалы журнала. Повторный запуск с той же версией ничего не перезаписывает.
 * Author: Редакция GNSS.PRO
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

const GNSS_SEED_VERSION = '1.0.2';

add_action('init', 'gnss_maybe_seed', 30);

function gnss_maybe_seed(): void
{
    static $running = false;
    if ($running || get_option('gnss_seed_version') === GNSS_SEED_VERSION) {
        return;
    }
    $running = true;

    if (!function_exists('wp_insert_post') || !get_option('siteurl')) {
        $running = false;
        return;
    }

    $users = get_users([
        'role' => 'administrator',
        'number' => 1,
        'orderby' => 'ID',
        'order' => 'ASC',
    ]);
    if (!$users) {
        $running = false;
        return;
    }

    $author = $users[0];
    wp_set_current_user((int) $author->ID);
    if (in_array($author->display_name, [$author->user_login, 'admin', 'editor'], true)) {
        wp_update_user([
            'ID' => $author->ID,
            'display_name' => 'Редакция GNSS.PRO',
            'nickname' => 'Редакция GNSS.PRO',
            'description' => 'Редакция журнала GNSS.PRO пишет о спутниковой навигации: сначала физика и числа, затем граница метода.',
        ]);
    }

    $theme = wp_get_theme('gnss-pro');
    if ($theme->exists() && get_stylesheet() !== 'gnss-pro') {
        switch_theme('gnss-pro');
    }

    gnss_seed_options();
    gnss_seed_remove_defaults();

    $categories = gnss_seed_terms();
    $tags = gnss_seed_tags();
    $sticky = gnss_seed_articles((int) $author->ID, $categories, $tags);
    $pages = gnss_seed_pages((int) $author->ID);

    if ($sticky === null || empty($pages['glavnaya']) || empty($pages['zhurnal']) || empty($categories['glonass'])) {
        $running = false;
        return;
    }

    update_option('sticky_posts', $sticky);
    update_option('default_category', (int) $categories['glonass']);
    update_option('show_on_front', 'page');
    update_option('page_on_front', (int) $pages['glavnaya']);
    update_option('page_for_posts', (int) $pages['zhurnal']);
    update_option('permalink_structure', '/%postname%/');

    gnss_seed_menu($pages);

    $leftovers = get_terms([
        'taxonomy' => 'category',
        'hide_empty' => false,
    ]);
    if (is_array($leftovers)) {
        foreach ($leftovers as $term) {
            if (!$term instanceof WP_Term || (int) $term->count !== 0) {
                continue;
            }
            if (in_array($term->name, ['Без рубрики', 'Uncategorized'], true) || in_array($term->slug, ['uncategorized', 'bez-rubriki'], true)) {
                wp_delete_term((int) $term->term_id, 'category');
            }
        }
    }

    flush_rewrite_rules(false);
    update_option('gnss_seed_version', GNSS_SEED_VERSION);
}

function gnss_seed_options(): void
{
    update_option('blogname', 'GNSS.PRO');
    update_option('blogdescription', 'Журнал о ГЛОНАСС и спутниковой навигации');
    update_option('timezone_string', 'Europe/Moscow');
    update_option('date_format', 'j F Y');
    update_option('time_format', 'H:i');
    update_option('start_of_week', '1');
    update_option('posts_per_page', 9);
    update_option('default_comment_status', 'closed');
    update_option('default_ping_status', 'closed');
    update_option('comment_registration', '1');
    update_option('blog_public', '1');
    update_option('WPLANG', 'ru_RU');
}

function gnss_seed_remove_defaults(): void
{
    $drop_titles = [
        'Hello world!',
        'Привет, мир!',
        'Здравствуйте, мир!',
        'Sample Page',
        'Пример страницы',
    ];
    $posts = get_posts([
        'post_type' => ['post', 'page'],
        'post_status' => 'any',
        'numberposts' => 50,
    ]);
    foreach ($posts as $post) {
        if (in_array($post->post_title, $drop_titles, true)) {
            wp_delete_post((int) $post->ID, true);
        }
    }
}

function gnss_seed_terms(): array
{
    $definitions = [
        'glonass' => ['ГЛОНАСС', 'Орбитальная группировка, время и развитие российской системы.'],
        'sistemy-gnss' => ['Системы GNSS', 'GPS, Galileo, BeiDou и региональные дополнения рядом с ГЛОНАСС.'],
        'signaly' => ['Сигналы и частоты', 'Литеры, коды и зачем приёмнику больше одной частоты.'],
        'vysokaya-tochnost' => ['Высокая точность', 'RTK, PPP, SBAS и границы, после которых метры не становятся сантиметрами.'],
        'otrasli' => ['Отрасли', 'Транспорт, точное земледелие и другие места, где координата стоит денег.'],
        'priemniki' => ['Приёмная аппаратура', 'Что смотреть в приёмнике и антенне до покупки.'],
    ];

    $ids = [];
    foreach ($definitions as $slug => [$name, $description]) {
        $existing = get_term_by('slug', $slug, 'category');
        if ($existing instanceof WP_Term) {
            wp_update_term((int) $existing->term_id, 'category', [
                'name' => $name,
                'description' => $description,
            ]);
            $ids[$slug] = (int) $existing->term_id;
            continue;
        }

        $created = wp_insert_term($name, 'category', [
            'slug' => $slug,
            'description' => $description,
        ]);
        if (is_wp_error($created)) {
            continue;
        }
        $ids[$slug] = (int) $created['term_id'];
    }

    return $ids;
}

function gnss_seed_tags(): array
{
    $definitions = [
        'orbita' => 'Орбита',
        'geodeziya' => 'Геодезия',
        'transport' => 'Транспорт',
        'vremya' => 'Время',
        'chastoty' => 'Частоты',
        'selskoe-khozyaystvo' => 'Сельское хозяйство',
        'priemnik' => 'Приёмник',
    ];

    $ids = [];
    foreach ($definitions as $slug => $name) {
        $existing = get_term_by('slug', $slug, 'post_tag');
        if ($existing instanceof WP_Term) {
            $ids[$slug] = (int) $existing->term_id;
            continue;
        }
        $created = wp_insert_term($name, 'post_tag', ['slug' => $slug]);
        if (is_wp_error($created)) {
            continue;
        }
        $ids[$slug] = (int) $created['term_id'];
    }

    return $ids;
}

function gnss_seed_articles(int $author_id, array $categories, array $tags): ?array
{
    $articles = require __DIR__ . '/content/articles.php';
    $sticky = [];

    foreach ($articles as $article) {
        if (empty($categories[$article['category']])) {
            return null;
        }
        $existing = get_page_by_path($article['slug'], OBJECT, 'post');
        $local = wp_date('Y-m-d H:i:s', time() - ((int) $article['days_ago'] * DAY_IN_SECONDS));
        $data = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_title' => $article['title'],
            'post_name' => $article['slug'],
            'post_excerpt' => $article['excerpt'],
            'post_content' => $article['content'],
            'post_author' => $author_id,
            'post_date' => $local,
            'post_date_gmt' => get_gmt_from_date($local),
            'comment_status' => 'closed',
            'ping_status' => 'closed',
        ];
        if ($existing instanceof WP_Post) {
            $data['ID'] = (int) $existing->ID;
        }

        $post_id = wp_insert_post($data, true);
        if (is_wp_error($post_id) || !$post_id) {
            return null;
        }

        $category_id = $categories[$article['category']] ?? 0;
        if ($category_id) {
            wp_set_post_terms((int) $post_id, [$category_id], 'category', false);
        }

        $tag_ids = [];
        foreach ($article['tags'] as $tag_slug) {
            if (!empty($tags[$tag_slug])) {
                $tag_ids[] = (int) $tags[$tag_slug];
            }
        }
        if ($tag_ids) {
            wp_set_post_terms((int) $post_id, $tag_ids, 'post_tag', false);
        }

        if (!empty($article['sticky'])) {
            $sticky[] = (int) $post_id;
        }
    }

    return $sticky;
}

function gnss_seed_pages(int $author_id): array
{
    $pages = require __DIR__ . '/content/pages.php';
    $ids = [];

    foreach ($pages as $page) {
        $existing = get_page_by_path($page['slug']);
        $data = [
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => $page['title'],
            'post_name' => $page['slug'],
            'post_content' => $page['content'],
            'post_author' => $author_id,
            'comment_status' => 'closed',
            'ping_status' => 'closed',
        ];
        if ($existing instanceof WP_Post) {
            $data['ID'] = (int) $existing->ID;
        }

        $page_id = wp_insert_post($data, true);
        if (is_wp_error($page_id) || !$page_id) {
            continue;
        }
        $ids[$page['slug']] = (int) $page_id;
    }

    return $ids;
}

function gnss_seed_menu(array $pages): void
{
    $order = [
        'glavnaya' => 'Главная',
        'zhurnal' => 'Журнал',
        'sistemy' => 'Системы',
        'glossariy' => 'Глоссарий',
        'o-zhurnale' => 'О журнале',
    ];
    $menu = wp_get_nav_menu_object('Главное');
    if ($menu instanceof WP_Term) {
        $menu_id = (int) $menu->term_id;
    } else {
        $created = wp_create_nav_menu('Главное');
        if (is_wp_error($created)) {
            return;
        }
        $menu_id = (int) $created;
    }
    if ($menu_id <= 0) {
        return;
    }

    $items = wp_get_nav_menu_items($menu_id, ['post_status' => 'any']);
    if (is_array($items)) {
        foreach ($items as $item) {
            wp_delete_post((int) $item->ID, true);
        }
    }

    $position = 1;
    foreach ($order as $slug => $label) {
        if (empty($pages[$slug])) {
            continue;
        }
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => $label,
            'menu-item-object' => 'page',
            'menu-item-object-id' => (int) $pages[$slug],
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
            'menu-item-position' => $position,
        ]);
        $position++;
    }

    $locations = get_theme_mod('nav_menu_locations');
    if (!is_array($locations)) {
        $locations = [];
    }
    $locations['primary'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}
