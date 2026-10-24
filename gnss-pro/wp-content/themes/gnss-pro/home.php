<?php
/**
 * Journal index, the posts page.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
$page_id = (int) get_option('page_for_posts');
?>
<main id="content" class="wrap section">
    <header class="page-head">
        <p class="kicker">GNSS.PRO</p>
        <h1><?php echo esc_html($page_id ? get_the_title($page_id) : 'Журнал'); ?></h1>
        <?php if ($page_id) : ?>
            <div class="lede"><?php echo wp_kses_post((string) get_post_field('post_content', $page_id)); ?></div>
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
        <?php
        the_posts_pagination([
            'mid_size' => 1,
            'prev_text' => 'Назад',
            'next_text' => 'Дальше',
        ]);
        ?>
    <?php else : ?>
        <p class="lede">В этой подборке пока нет материалов.</p>
    <?php endif; ?>
</main>
<?php
get_footer();
