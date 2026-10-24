<?php
/**
 * Story card.
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_id = (int) get_the_ID();
$rubric = gnss_post_rubric($post_id);
$slug = $rubric ? $rubric->slug : 'glonass';
?>
<article <?php post_class('card'); ?>>
    <a class="card__cover" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
        <?php get_template_part('template-parts/cover', null, ['slug' => $slug, 'seed' => $post_id]); ?>
    </a>
    <?php if ($rubric) : ?>
        <p class="kicker"><a href="<?php echo esc_url(get_category_link($rubric)); ?>"><?php echo esc_html($rubric->name); ?></a></p>
    <?php endif; ?>
    <h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
    <p class="card__excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
    <p class="meta">
        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(gnss_format_date()); ?></time>
        <span><?php echo esc_html((string) gnss_reading_minutes($post_id)); ?> мин</span>
    </p>
</article>
