<?php
/**
 * Not found.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="content" class="wrap section">
    <header class="page-head">
        <p class="kicker">404</p>
        <h1>Такой страницы нет</h1>
        <p class="lede">Координата не сошлась: ссылка с опечаткой или материал больше не опубликован. Поиск ниже смотрит по заголовкам и текстам журнала.</p>
    </header>
    <?php get_search_form(); ?>
    <p class="lede"><a class="text-link" href="<?php echo esc_url(home_url('/')); ?>">На главную</a> · <a class="text-link" href="<?php echo esc_url(gnss_posts_url()); ?>">В журнал</a></p>
</main>
<?php
get_footer();
