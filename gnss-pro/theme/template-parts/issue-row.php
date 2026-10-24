<?php
/**
 * One row in the journal list.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<article <?php post_class('issue-row'); ?>>
    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('j F Y')); ?></time>
    <div>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <p><?php echo esc_html(gnss_dek()); ?></p>
    </div>
    <?php echo wp_kses_post(gnss_kicker_html()); ?>
</article>
