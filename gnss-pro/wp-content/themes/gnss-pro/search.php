<?php
/**
 * Search results.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="content" class="wrap section">
    <header class="page-head">
        <p class="kicker">Поиск</p>
        <h1><?php echo esc_html(get_search_query(false)); ?></h1>
        <p class="lede">
            <?php
            global $wp_query;
            echo esc_html(sprintf(
                'Найдено материалов: %d',
                (int) $wp_query->found_posts
            ));
            ?>
        </p>
    </header>
    <?php if (have_posts()) : ?>
        <div class="story-grid">
            <?php
            while (have_posts()) :
                the_post();
                get_template_part('template-parts/card');
            endwhile;
            ?>
        </div>
        <?php the_posts_pagination(['prev_text' => 'Назад', 'next_text' => 'Дальше']); ?>
    <?php else : ?>
        <p class="lede">По этому запросу в журнале пусто. Попробуйте «ГЛОНАСС», «RTK» или «ПЗ-90».</p>
        <?php get_search_form(); ?>
    <?php endif; ?>
</main>
<?php
get_footer();
