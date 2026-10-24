<?php
/**
 * Idempotent content seed. Run: wp eval-file bin/seed.php
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    fwrite(STDERR, "Запускайте через wp eval-file bin/seed.php\n");
    exit(1);
}

function gnss_seed_dir(): string
{
    foreach ([dirname(__DIR__) . '/content', '/seed/content'] as $dir) {
        if (is_dir($dir . '/articles') && is_file($dir . '/manifest.php')) {
            return $dir;
        }
    }
    WP_CLI::error('Каталог content не найден.');
}

function gnss_seed_html(string $path): string
{
    $html = file_get_contents($path);
    if ($html === false) {
        WP_CLI::error('Не прочитан файл ' . $path);
    }
    return str_replace('{{home}}', trailingslashit(home_url()), $html);
}

function gnss_seed_upsert(array $data): int
{
    $existing = get_page_by_path($data['post_name'], OBJECT, $data['post_type']);
    if ($existing instanceof WP_Post) {
        $data['ID'] = $existing->ID;
        $id = wp_update_post($data, true);
    } else {
        $id = wp_insert_post($data, true);
    }
    if (is_wp_error($id)) {
        WP_CLI::error($id->get_error_message());
    }
    update_post_meta((int) $id, '_gnss_raw_html', '1');
    return (int) $id;
}

function gnss_seed_menu(string $name): int
{
    $menu = wp_get_nav_menu_object($name);
    if (!$menu) {
        $created = wp_create_nav_menu($name);
        if (is_wp_error($created)) {
            WP_CLI::error($created->get_error_message());
        }
        return (int) $created;
    }

    $items = wp_get_nav_menu_items((int) $menu->term_id, ['post_status' => 'any']);
    if (is_array($items)) {
        foreach ($items as $item) {
            wp_delete_post((int) $item->ID, true);
        }
    }
    return (int) $menu->term_id;
}

function gnss_seed_menu_item(int $menu_id, array $args): void
{
    $args['menu-item-status'] = 'publish';
    $result = wp_update_nav_menu_item($menu_id, 0, $args);
    if (is_wp_error($result)) {
        WP_CLI::error($result->get_error_message());
    }
}

if (get_stylesheet() !== 'gnss-pro') {
    switch_theme('gnss-pro');
}

$dir = gnss_seed_dir();
/** @var array $manifest */
$manifest = require $dir . '/manifest.php';

update_option('blogname', 'GNSS.PRO');
update_option('blogdescription', 'Журнал о ГЛОНАСС и спутниковой навигации');
update_option('timezone_string', 'Europe/Moscow');
update_option('date_format', 'j F Y');
update_option('time_format', 'H:i');
update_option('start_of_week', '1');
update_option('permalink_structure', '/%postname%/');
update_option('category_base', 'razdel');
update_option('tag_base', 'metka');
update_option('posts_per_page', 12);
update_option('default_comment_status', 'closed');
update_option('default_ping_status', 'closed');
update_option('comment_registration', '1');
update_option('users_can_register', '0');
update_option('blog_public', '1');
update_option('show_on_front', 'page');

wp_update_user([
    'ID' => 1,
    'display_name' => 'Редакция GNSS.PRO',
    'nickname' => 'Редакция',
    'description' => 'Редакционная группа журнала GNSS.PRO.',
]);

$category_ids = [];
foreach ($manifest['categories'] as $category) {
    $existing = get_term_by('slug', $category['slug'], 'category');
    if ($existing instanceof WP_Term) {
        $updated = wp_update_term((int) $existing->term_id, 'category', [
            'name' => $category['name'],
            'description' => $category['description'],
        ]);
        if (is_wp_error($updated)) {
            WP_CLI::error($updated->get_error_message());
        }
        $term_id = (int) $existing->term_id;
    } else {
        $created = wp_insert_term($category['name'], 'category', [
            'slug' => $category['slug'],
            'description' => $category['description'],
        ]);
        if (is_wp_error($created)) {
            WP_CLI::error($created->get_error_message());
        }
        $term_id = (int) $created['term_id'];
    }
    update_term_meta($term_id, 'gnss_order', (string) $category['order']);
    $category_ids[$category['slug']] = $term_id;
}

if (isset($category_ids['glonass'])) {
    update_option('default_category', $category_ids['glonass']);
}

$page_ids = [];
foreach ($manifest['pages'] as $page) {
    $page_ids[$page['slug']] = gnss_seed_upsert([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_name' => $page['slug'],
        'post_title' => $page['title'],
        'post_excerpt' => $page['excerpt'],
        'post_content' => gnss_seed_html($dir . '/pages/' . $page['file']),
        'post_author' => 1,
        'comment_status' => 'closed',
        'ping_status' => 'closed',
    ]);
}

$page_ids['glavnaya'] = gnss_seed_upsert([
    'post_type' => 'page',
    'post_status' => 'publish',
    'post_name' => 'glavnaya',
    'post_title' => 'Главная',
    'post_excerpt' => 'Журнал GNSS.PRO о ГЛОНАСС и спутниковой навигации.',
    'post_content' => '<p>Обложка журнала собирается темой. Если вы видите этот абзац, активна другая тема.</p>',
    'post_author' => 1,
    'comment_status' => 'closed',
    'ping_status' => 'closed',
]);

$page_ids['zhurnal'] = gnss_seed_upsert([
    'post_type' => 'page',
    'post_status' => 'publish',
    'post_name' => 'zhurnal',
    'post_title' => 'Журнал',
    'post_excerpt' => 'Все материалы GNSS.PRO.',
    'post_content' => '<p>Хронологический список материалов.</p>',
    'post_author' => 1,
    'comment_status' => 'closed',
    'ping_status' => 'closed',
]);

update_option('page_on_front', $page_ids['glavnaya']);
update_option('page_for_posts', $page_ids['zhurnal']);

foreach ($manifest['articles'] as $article) {
    $post_id = gnss_seed_upsert([
        'post_type' => 'post',
        'post_status' => 'publish',
        'post_name' => $article['slug'],
        'post_title' => $article['title'],
        'post_excerpt' => $article['excerpt'],
        'post_content' => gnss_seed_html($dir . '/articles/' . $article['file']),
        'post_date' => $article['date'],
        'post_author' => 1,
        'comment_status' => 'closed',
        'ping_status' => 'closed',
    ]);
    wp_set_post_categories($post_id, [$category_ids[$article['category']]], false);
    wp_set_post_tags($post_id, $article['tags'], false);
    WP_CLI::log('Материал: ' . $article['slug']);
}

$owned_ids = array_map('intval', $page_ids);
$sample_titles = [
    'Привет, мир!',
    'Hello world!',
    'Образец страницы',
    'Sample Page',
    'Политика конфиденциальности',
    'Privacy Policy',
];
$samples = get_posts([
    'post_type' => ['post', 'page'],
    'post_status' => 'any',
    'posts_per_page' => -1,
]);
foreach ($samples as $sample) {
    if (in_array((int) $sample->ID, $owned_ids, true)) {
        continue;
    }
    if (in_array($sample->post_title, $sample_titles, true)) {
        wp_delete_post((int) $sample->ID, true);
        WP_CLI::log('Удалена стартовая запись: ' . $sample->post_title);
    }
}

foreach (['bez-rubriki', 'uncategorized'] as $slug) {
    $term = get_term_by('slug', $slug, 'category');
    if ($term instanceof WP_Term && (int) $term->count === 0 && (int) get_option('default_category') !== (int) $term->term_id) {
        wp_delete_term((int) $term->term_id, 'category');
    }
}

$primary = gnss_seed_menu('Основное');
gnss_seed_menu_item($primary, [
    'menu-item-title' => 'Журнал',
    'menu-item-type' => 'post_type',
    'menu-item-object' => 'page',
    'menu-item-object-id' => $page_ids['zhurnal'],
]);
foreach (['glonass', 'sozvezdiya', 'tochnost', 'primeneniya'] as $slug) {
    gnss_seed_menu_item($primary, [
        'menu-item-title' => get_term($category_ids[$slug], 'category')->name,
        'menu-item-type' => 'taxonomy',
        'menu-item-object' => 'category',
        'menu-item-object-id' => $category_ids[$slug],
    ]);
}
gnss_seed_menu_item($primary, [
    'menu-item-title' => 'Глоссарий',
    'menu-item-type' => 'post_type',
    'menu-item-object' => 'page',
    'menu-item-object-id' => $page_ids['glossariy'],
]);
gnss_seed_menu_item($primary, [
    'menu-item-title' => 'О проекте',
    'menu-item-type' => 'post_type',
    'menu-item-object' => 'page',
    'menu-item-object-id' => $page_ids['o-proekte'],
]);

$footer = gnss_seed_menu('Подвал');
foreach (['priemniki', 'standarty'] as $slug) {
    gnss_seed_menu_item($footer, [
        'menu-item-title' => get_term($category_ids[$slug], 'category')->name,
        'menu-item-type' => 'taxonomy',
        'menu-item-object' => 'category',
        'menu-item-object-id' => $category_ids[$slug],
    ]);
}
foreach (['redaktsiya' => 'Редакция', 'kontakty' => 'Контакты', 'pravovaya-informatsiya' => 'Правовая информация'] as $slug => $title) {
    gnss_seed_menu_item($footer, [
        'menu-item-title' => $title,
        'menu-item-type' => 'post_type',
        'menu-item-object' => 'page',
        'menu-item-object-id' => $page_ids[$slug],
    ]);
}

$locations = get_theme_mod('nav_menu_locations', []);
if (!is_array($locations)) {
    $locations = [];
}
$locations['primary'] = $primary;
$locations['footer'] = $footer;
set_theme_mod('nav_menu_locations', $locations);

global $wp_rewrite;
$wp_rewrite->set_permalink_structure('/%postname%/');
$wp_rewrite->set_category_base('razdel');
$wp_rewrite->set_tag_base('metka');
flush_rewrite_rules(false);

WP_CLI::success('Журнал GNSS.PRO собран: ' . count($manifest['articles']) . ' материалов.');
