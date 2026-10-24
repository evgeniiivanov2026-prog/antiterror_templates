<?php
/**
 * Category, tag, and date archives.
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
        <p class="kicker"><?php echo is_category() ? 'Раздел' : 'Архив'; ?></p>
        <h1><?php the_archive_title(); ?></h1>
        <?php if (get_the_archive_description()) : ?>
            <div class="dek"><?php the_archive_description(); ?></div>
        <?php endif; ?>
    </header>
    <?php if (have_posts()) : ?>
        <div class="issue-list">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/issue-row'); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(['prev_text' => 'Назад', 'next_text' => 'Дальше']); ?>
    <?php else : ?>
        <p class="empty">В этом разделе пока пусто.</p>
    <?php endif; ?>
</div>
<?php
get_footer();
