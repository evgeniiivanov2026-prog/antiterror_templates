<?php
/**
 * Rubrics, tags, dates.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="content" class="wrap section">
    <header class="page-head">
        <p class="kicker">Подборка</p>
        <h1><?php the_archive_title(); ?></h1>
        <?php if (get_the_archive_description()) : ?>
            <div class="lede"><?php echo wp_kses_post(get_the_archive_description()); ?></div>
        <?php endif; ?>
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
        <?php the_posts_pagination(['mid_size' => 1, 'prev_text' => 'Назад', 'next_text' => 'Дальше']); ?>
    <?php else : ?>
        <p class="lede">В этой подборке пока пусто.</p>
    <?php endif; ?>
</main>
<?php
get_footer();
