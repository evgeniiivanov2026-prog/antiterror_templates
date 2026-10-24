<?php
/**
 * Search results.
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
        <p class="kicker">Поиск</p>
        <h1><?php echo esc_html(get_search_query()); ?></h1>
        <p class="dek"><?php echo esc_html(gnss_found_label((int) $wp_query->found_posts)); ?></p>
    </header>
    <?php if (have_posts()) : ?>
        <div class="issue-list">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/issue-row'); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(['prev_text' => 'Назад', 'next_text' => 'Дальше']); ?>
    <?php else : ?>
        <div class="empty">
            <p>По этому запросу материалов нет. Попробуйте «ГЛОНАСС», «FDMA» или «ПЗ-90».</p>
            <?php get_search_form(); ?>
        </div>
    <?php endif; ?>
</div>
<?php
get_footer();
