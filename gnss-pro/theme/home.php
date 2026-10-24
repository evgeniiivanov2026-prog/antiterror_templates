<?php
/**
 * Journal index, the posts page.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
$journal_id = (int) get_option('page_for_posts');
$title = $journal_id ? get_the_title($journal_id) : 'Журнал';
?>
<div class="shell">
    <header class="page-head">
        <?php gnss_breadcrumbs(); ?>
        <p class="kicker">Хроника</p>
        <h1><?php echo esc_html($title); ?></h1>
        <p class="dek">Все материалы gnss.pro. Новый текст появляется сверху.</p>
    </header>
    <?php if (have_posts()) : ?>
        <div class="issue-list">
            <?php while (have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/issue-row'); ?>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination([
            'mid_size' => 1,
            'prev_text' => 'Назад',
            'next_text' => 'Дальше',
        ]); ?>
    <?php else : ?>
        <p class="empty">В журнале пока нет материалов.</p>
    <?php endif; ?>
</div>
<?php
get_footer();
