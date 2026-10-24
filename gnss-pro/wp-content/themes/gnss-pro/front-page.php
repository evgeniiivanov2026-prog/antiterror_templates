<?php
/**
 * Magazine front page.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$sticky = array_values(array_filter(array_map('intval', (array) get_option('sticky_posts'))));
$feature_args = [
    'post_type' => 'post',
    'posts_per_page' => 1,
    'ignore_sticky_posts' => true,
    'no_found_rows' => true,
];
if ($sticky) {
    $feature_args['post__in'] = $sticky;
    $feature_args['orderby'] = 'post__in';
}
$feature = new WP_Query($feature_args);
$feature_id = 0;
if ($feature->have_posts()) {
    $feature->the_post();
    $feature_id = (int) get_the_ID();
}

$side = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 3,
    'post__not_in' => $feature_id ? [$feature_id] : [0],
    'ignore_sticky_posts' => true,
    'no_found_rows' => true,
]);
$used = $feature_id ? [$feature_id] : [];
if ($side->have_posts()) {
    foreach ($side->posts as $side_post) {
        $used[] = (int) $side_post->ID;
    }
}
$grid = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 6,
    'post__not_in' => $used ?: [0],
    'ignore_sticky_posts' => true,
    'no_found_rows' => true,
]);
?>
<main id="content">
    <section class="hero wrap">
        <?php if ($feature_id) : ?>
            <?php
            $feature->rewind_posts();
            $feature->the_post();
            $rubric = gnss_post_rubric();
            ?>
            <div class="hero__main">
                <p class="kicker">01<?php echo $rubric ? ' · ' . esc_html($rubric->name) : ''; ?></p>
                <h1 class="hero__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                <p class="hero__lead"><?php echo esc_html(get_the_excerpt()); ?></p>
                <p class="meta">
                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(gnss_format_date()); ?></time>
                    <span><?php echo esc_html((string) gnss_reading_minutes()); ?> мин чтения</span>
                    <a class="text-link" href="<?php the_permalink(); ?>">Читать материал</a>
                </p>
                <a class="hero__figure" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                    <?php get_template_part('template-parts/cover', null, ['slug' => $rubric ? $rubric->slug : 'glonass', 'seed' => $feature_id]); ?>
                </a>
            </div>
            <?php wp_reset_postdata(); ?>
        <?php endif; ?>
        <div class="hero__side">
            <?php
            $index = 2;
            if ($side->have_posts()) :
                while ($side->have_posts()) :
                    $side->the_post();
                    $rubric = gnss_post_rubric();
                    ?>
                    <article class="side-story">
                        <p class="kicker"><?php echo esc_html(sprintf('%02d', $index)); ?><?php echo $rubric ? ' · ' . esc_html($rubric->name) : ''; ?></p>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <p class="meta"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(gnss_format_date()); ?></time></p>
                    </article>
                    <?php
                    $index++;
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </section>

    <section class="statbar" aria-label="Номинал группировки ГЛОНАСС">
        <div class="wrap statbar__grid">
            <p><strong>24</strong><span>спутника в номинале</span></p>
            <p><strong>19 100 км</strong><span>высота орбиты</span></p>
            <p><strong>64,8°</strong><span>наклонение плоскости</span></p>
            <p><strong>ПЗ-90.11</strong><span>система координат</span></p>
        </div>
    </section>

    <section class="band wrap">
        <div class="band__head">
            <h2>Рубрики</h2>
            <a class="text-link" href="<?php echo esc_url(gnss_posts_url()); ?>">Весь журнал</a>
        </div>
        <ul class="rubrics">
            <?php foreach (gnss_ordered_categories() as $category) : ?>
                <li>
                    <a href="<?php echo esc_url(get_category_link($category)); ?>">
                        <span><?php echo esc_html($category->name); ?></span>
                        <small><?php echo esc_html((string) $category->count); ?></small>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section class="band wrap">
        <div class="band__head">
            <h2>Свежие материалы</h2>
        </div>
        <div class="story-grid">
            <?php
            if ($grid->have_posts()) :
                while ($grid->have_posts()) :
                    $grid->the_post();
                    get_template_part('template-parts/card');
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </section>

    <section class="band wrap systems-band">
        <div class="band__head">
            <h2>Четыре глобальные системы</h2>
            <a class="text-link" href="<?php echo esc_url(home_url('/sistemy/')); ?>">Сравнить параметры</a>
        </div>
        <div class="system-grid">
            <?php foreach (gnss_systems() as $system) : ?>
                <?php if ($system['scope'] !== 'global') { continue; } ?>
                <a class="system-card" href="<?php echo esc_url(home_url('/sistemy/#' . $system['id'])); ?>">
                    <p class="kicker"><?php echo esc_html($system['operator']); ?></p>
                    <h3><?php echo esc_html($system['name']); ?></h3>
                    <p><?php echo esc_html($system['summary']); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="manifesto wrap">
        <p class="kicker">О журнале</p>
        <blockquote>
            <p>Координата — это измерение с погрешностью, а не точка, которую кто-то положил на карту. Пишем так, чтобы это было понятно и геодезисту, и редактору, который проверяет чужой текст.</p>
        </blockquote>
        <a class="button" href="<?php echo esc_url(home_url('/o-zhurnale/')); ?>">Как устроена редакция</a>
    </section>
</main>
<?php
get_footer();
