<?php
/**
 * Site header.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="starfield" aria-hidden="true">
    <span class="stars stars-far"></span>
    <span class="stars stars-near"></span>
    <span class="aurora"></span>
    <span class="meteor"></span>
</div>
<a class="skip-link" href="#content">К содержанию</a>
<div class="topline">
    <p>Журнал о спутниковой навигации</p>
    <p>ГЛОНАСС · GPS · Galileo · BeiDou</p>
</div>
<header class="site-header">
    <div class="shell site-header-inner">
        <a class="logo" href="<?php echo esc_url(home_url('/')); ?>">
            <svg class="logo-mark" viewBox="0 0 36 36" aria-hidden="true">
                <circle cx="18" cy="18" r="5.5" fill="none" stroke="currentColor" stroke-width="1.4"></circle>
                <ellipse cx="18" cy="18" rx="14" ry="5.6" fill="none" stroke="currentColor" stroke-width="1.4" transform="rotate(-32 18 18)"></ellipse>
                <ellipse cx="18" cy="18" rx="14" ry="5.6" fill="none" stroke="#e3b17a" stroke-width="1.4" transform="rotate(28 18 18)"></ellipse>
                <circle cx="29" cy="11.5" r="1.8" fill="#e3b17a"></circle>
            </svg>
            <span class="logo-type">GNSS<span>.PRO</span></span>
            <span class="screen-reader-text">На главную</span>
        </a>
        <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="site-nav">
            <span class="nav-toggle-lines" aria-hidden="true"></span>
            <span class="screen-reader-text">Меню</span>
        </button>
        <div class="header-tools" id="site-nav">
            <nav aria-label="Основное меню">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => 'nav-list',
                    'fallback_cb' => false,
                    'depth' => 1,
                ]);
                ?>
            </nav>
            <div class="header-search">
                <?php get_search_form(); ?>
            </div>
        </div>
    </div>
</header>
<main id="content">
