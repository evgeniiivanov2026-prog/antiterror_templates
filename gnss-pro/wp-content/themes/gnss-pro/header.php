<?php
/**
 * Site header.
 */

if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?php echo esc_url(get_template_directory_uri() . '/assets/favicon.svg'); ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#content">К содержанию</a>
<?php if (is_singular('post')) : ?>
    <div class="progress" data-progress></div>
<?php endif; ?>
<header class="site-header">
    <div class="wrap utility">
        <p>Москва · журнал о навигации</p>
        <p class="utility__coord">55°45′ с.ш. · 37°37′ в.д.</p>
        <p><time datetime="<?php echo esc_attr(wp_date('Y-m-d')); ?>"><?php echo esc_html(gnss_format_date(time())); ?></time></p>
    </div>
    <div class="wrap masthead">
        <a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
            <?php gnss_logo_mark(); ?>
            <span class="brand__text">
                <span class="brand__name">GNSS<span class="brand__dot">.</span>PRO</span>
                <span class="brand__tag">ГЛОНАСС и глобальные навигационные системы</span>
            </span>
        </a>
        <div class="masthead__tools">
            <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="site-nav">Меню</button>
            <?php get_search_form(); ?>
        </div>
    </div>
    <div class="wrap rule" aria-hidden="true"></div>
    <nav class="wrap nav" id="site-nav" data-nav aria-label="Разделы">
        <?php
        wp_nav_menu([
            'theme_location' => 'primary',
            'container' => false,
            'menu_class' => 'menu',
            'fallback_cb' => 'gnss_fallback_menu',
            'depth' => 1,
        ]);
        ?>
    </nav>
</header>
