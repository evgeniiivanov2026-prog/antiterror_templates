<?php
/**
 * Static page.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="content" class="wrap section">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('article'); ?>>
            <header class="article-head">
                <p class="kicker">GNSS.PRO</p>
                <h1><?php the_title(); ?></h1>
            </header>
            <div class="prose">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
