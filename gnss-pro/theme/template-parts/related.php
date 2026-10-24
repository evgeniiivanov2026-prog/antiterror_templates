<?php
/**
 * Related articles for the sidebar.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

$cats = get_the_category();
$ids = $cats ? wp_list_pluck($cats, 'term_id') : [];
$related = new WP_Query([
    'category__in' => $ids,
    'post__not_in' => [get_the_ID()],
    'posts_per_page' => 3,
    'ignore_sticky_posts' => true,
]);

if (!$related->have_posts()) {
    return;
}
?>
<div class="aside-card">
    <h2>В этом разделе</h2>
    <ol>
        <?php while ($related->have_posts()) : $related->the_post(); ?>
            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
        <?php endwhile; ?>
    </ol>
</div>
<?php
wp_reset_postdata();
