<?php
/**
 * Glossary page.
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
                <p class="kicker">Словарь</p>
                <h1><?php the_title(); ?></h1>
            </header>
            <div class="prose">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
    <dl class="glossary">
        <?php foreach (gnss_glossary() as $entry) : ?>
            <div>
                <dt><?php echo esc_html($entry[0]); ?></dt>
                <dd><?php echo esc_html($entry[1]); ?></dd>
            </div>
        <?php endforeach; ?>
    </dl>
</main>
<?php
get_footer();
