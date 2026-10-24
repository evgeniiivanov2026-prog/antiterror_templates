<?php
/**
 * Structural check for seeded articles. Does not boot WordPress.
 */

$root = dirname(__DIR__);
$articles = require $root . '/wp-content/mu-plugins/content/articles.php';
$pages = require $root . '/wp-content/mu-plugins/content/pages.php';

$errors = [];
if (!is_array($articles) || count($articles) < 8) {
    $errors[] = 'expected at least 8 articles';
}

$required = ['slug', 'title', 'excerpt', 'category', 'tags', 'days_ago', 'content'];
$slugs = [];
foreach ($articles as $index => $article) {
    foreach ($required as $key) {
        if (!isset($article[$key]) || $article[$key] === '' || $article[$key] === []) {
            $errors[] = "article {$index} missing {$key}";
        }
    }
    if (isset($slugs[$article['slug']])) {
        $errors[] = 'duplicate slug ' . $article['slug'];
    }
    $slugs[$article['slug']] = true;
    $words = preg_match_all('/[\p{L}\p{N}]+/u', strip_tags((string) $article['content']));
    if ($words < 450) {
        $errors[] = $article['slug'] . " is too short ({$words} words)";
    }
}

$page_slugs = [];
foreach ($pages as $page) {
    foreach (['slug', 'title', 'content'] as $key) {
        if (!isset($page[$key])) {
            $errors[] = 'page missing ' . $key;
        }
    }
    $page_slugs[$page['slug']] = true;
}
foreach (['glavnaya', 'zhurnal', 'sistemy', 'glossariy', 'o-zhurnale', 'kontakty', 'privacy-policy'] as $slug) {
    if (empty($page_slugs[$slug])) {
        $errors[] = 'missing page ' . $slug;
    }
}

if ($errors) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo count($articles) . " articles, " . count($pages) . " pages ok\n";
