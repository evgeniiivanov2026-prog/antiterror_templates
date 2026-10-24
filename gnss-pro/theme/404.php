<?php
/**
 * Not found.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<div class="shell">
    <header class="page-head">
        <?php gnss_breadcrumbs(); ?>
        <p class="kicker">404</p>
        <h1>Такой страницы нет</h1>
        <p class="dek">Адрес мог устареть. Журнал и указатель разделов на месте.</p>
    </header>
    <div class="hero-actions">
        <a class="button" href="<?php echo esc_url(home_url('/')); ?>">На главную</a>
        <a class="button secondary" href="<?php echo esc_url(gnss_journal_url()); ?>">В журнал</a>
    </div>
    <div class="empty">
        <?php get_search_form(); ?>
    </div>
</div>
<?php
get_footer();
