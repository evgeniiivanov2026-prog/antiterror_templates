<?php
/**
 * Fallback index.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="content" class="wrap section">
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
        <header class="page-head">
            <h1>Ничего не нашлось</h1>
            <p class="lede">По этому запросу материалов нет.</p>
        </header>
    <?php endif; ?>
</main>
<?php
get_footer();
