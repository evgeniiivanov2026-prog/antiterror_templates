<?php
/**
 * Single article.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="content" class="wrap section">
    <?php while (have_posts()) : the_post(); ?>
        <?php $rubric = gnss_post_rubric(); ?>
        <article <?php post_class('article'); ?>>
            <nav class="crumbs" aria-label="Хлебные крошки">
                <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
                <a href="<?php echo esc_url(gnss_posts_url()); ?>">Журнал</a>
                <?php if ($rubric) : ?>
                    <a href="<?php echo esc_url(get_category_link($rubric)); ?>"><?php echo esc_html($rubric->name); ?></a>
                <?php endif; ?>
            </nav>
            <header class="article-head">
                <?php if ($rubric) : ?>
                    <p class="kicker"><a href="<?php echo esc_url(get_category_link($rubric)); ?>"><?php echo esc_html($rubric->name); ?></a></p>
                <?php endif; ?>
                <h1><?php the_title(); ?></h1>
                <?php if (has_excerpt()) : ?>
                    <p class="lead"><?php echo esc_html(get_the_excerpt()); ?></p>
                <?php endif; ?>
                <p class="meta">
                    <span>Редакция GNSS.PRO</span>
                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(gnss_format_date()); ?></time>
                    <span><?php echo esc_html((string) gnss_reading_minutes()); ?> мин чтения</span>
                </p>
            </header>
            <div class="prose">
                <?php the_content(); ?>
            </div>
            <?php $tags = get_the_tags(); ?>
            <?php if ($tags) : ?>
                <ul class="tags" aria-label="Метки">
                    <?php foreach ($tags as $tag) : ?>
                        <li><a href="<?php echo esc_url(get_tag_link($tag)); ?>"><?php echo esc_html($tag->name); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <aside class="colophon-note">
                <p>Номинальные параметры группировок не заменяют оперативный альманах и официальные извещения. Перед полевыми и производственными работами сверяйте статус системы и методику, по которой принят приёмник.</p>
            </aside>
            <?php
            the_post_navigation([
                'prev_text' => '<span class="nav-dir">Ранее</span><span class="nav-title">%title</span>',
                'next_text' => '<span class="nav-dir">Позже</span><span class="nav-title">%title</span>',
            ]);
            ?>
        </article>
        <?php
        $related = gnss_related_query((int) get_the_ID());
        if ($related->have_posts()) :
            ?>
            <section class="related">
                <h2>Ещё в журнале</h2>
                <div class="story-grid">
                    <?php
                    while ($related->have_posts()) :
                        $related->the_post();
                        get_template_part('template-parts/card');
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endwhile; ?>
</main>
<?php
get_footer();
