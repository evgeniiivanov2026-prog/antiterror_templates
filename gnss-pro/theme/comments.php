<?php
/**
 * Comments, closed on seeded articles.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

if (post_password_required() || (!comments_open() && !get_comments_number())) {
    return;
}
?>
<section class="prose">
    <?php if (have_comments()) : ?>
        <h2>Комментарии</h2>
        <ol class="comment-list">
            <?php wp_list_comments(['style' => 'ol', 'short_ping' => true]); ?>
        </ol>
    <?php endif; ?>
    <?php comment_form(['title_reply' => 'Комментарий']); ?>
</section>
