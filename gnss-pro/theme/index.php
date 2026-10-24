<?php
/**
 * Fallback list.
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
        <h1><?php echo is_home() ? 'Журнал' : esc_html(get_the_archive_title()); ?></h1>
    </header>
    <?php if (have_posts()) : ?>
        <div class="issue-list">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/issue-row'); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(['prev_text' => 'Назад', 'next_text' => 'Дальше']); ?>
    <?php else : ?>
        <p class="empty">Ничего не нашлось.</p>
    <?php endif; ?>
</div>
<?php
get_footer();
