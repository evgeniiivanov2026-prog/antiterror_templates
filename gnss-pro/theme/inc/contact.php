<?php
/**
 * Editorial contact form. Letters are stored as private posts.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function (): void {
    register_post_type('gnss_message', [
        'labels' => [
            'name' => 'Письма',
            'singular_name' => 'Письмо',
            'add_new_item' => 'Новое письмо',
            'edit_item' => 'Письмо',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-email-alt',
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'supports' => ['title', 'editor'],
    ]);
});

function gnss_contact_redirect(string $code): void
{
    $page = get_page_by_path('kontakty');
    $url = $page instanceof WP_Post ? get_permalink($page) : home_url('/');
    wp_safe_redirect(add_query_arg('contact', $code, $url));
    exit;
}

function gnss_handle_contact(): void
{
    if (!isset($_POST['gnss_contact_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gnss_contact_nonce'])), 'gnss_contact')) {
        gnss_contact_redirect('invalid');
    }

    $honeypot = isset($_POST['gnss_company']) ? trim((string) wp_unslash($_POST['gnss_company'])) : '';
    if ($honeypot !== '') {
        gnss_contact_redirect('sent');
    }

    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown';
    $limit_key = 'gnss_contact_' . md5($ip);
    if (get_transient($limit_key)) {
        gnss_contact_redirect('limit');
    }

    $name = isset($_POST['gnss_name']) ? sanitize_text_field(wp_unslash($_POST['gnss_name'])) : '';
    $email = isset($_POST['gnss_email']) ? sanitize_email(wp_unslash($_POST['gnss_email'])) : '';
    $topic = isset($_POST['gnss_topic']) ? sanitize_key(wp_unslash($_POST['gnss_topic'])) : 'question';
    $message = isset($_POST['gnss_message']) ? sanitize_textarea_field(wp_unslash($_POST['gnss_message'])) : '';
    $consent = isset($_POST['gnss_consent']) ? '1' : '';

    $topics = [
        'question' => 'Вопрос',
        'correction' => 'Уточнение к материалу',
        'other' => 'Другое',
    ];
    if (!isset($topics[$topic])) {
        $topic = 'question';
    }

    if ($name === '' || $message === '' || $consent !== '1') {
        gnss_contact_redirect('missing');
    }
    if (!is_email($email)) {
        gnss_contact_redirect('email');
    }
    if (mb_strlen($message) < 10) {
        gnss_contact_redirect('missing');
    }

    $post_id = wp_insert_post([
        'post_type' => 'gnss_message',
        'post_status' => 'private',
        'post_title' => $topics[$topic] . ': ' . $name,
        'post_content' => $message,
    ], true);

    if (is_wp_error($post_id)) {
        gnss_contact_redirect('invalid');
    }

    update_post_meta($post_id, '_gnss_email', $email);
    update_post_meta($post_id, '_gnss_topic', $topics[$topic]);
    set_transient($limit_key, 1, 30);
    gnss_contact_redirect('sent');
}

add_action('admin_post_nopriv_gnss_contact', 'gnss_handle_contact');
add_action('admin_post_gnss_contact', 'gnss_handle_contact');
