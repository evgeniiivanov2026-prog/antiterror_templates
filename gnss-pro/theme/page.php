<?php
/**
 * Static page.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<div class="shell">
    <?php while (have_posts()) : the_post(); ?>
        <header class="page-head">
            <?php gnss_breadcrumbs(); ?>
            <h1><?php the_title(); ?></h1>
            <?php if (has_excerpt()) : ?>
                <p class="dek"><?php echo esc_html(gnss_dek()); ?></p>
            <?php endif; ?>
        </header>
        <div class="page-layout">
            <article <?php post_class('prose'); ?>>
                <?php the_content(); ?>
                <?php if (is_page('kontakty')) : ?>
                    <?php get_template_part('template-parts/contact-form'); ?>
                <?php endif; ?>
            </article>
            <aside class="article-aside">
                <div class="aside-card">
                    <h2>По журналу</h2>
                    <ul>
                        <li><a href="<?php echo esc_url(home_url('/o-proekte/')); ?>">О проекте</a></li>
                        <li><a href="<?php echo esc_url(home_url('/glossariy/')); ?>">Глоссарий</a></li>
                        <li><a href="<?php echo esc_url(home_url('/redaktsiya/')); ?>">Редакция</a></li>
                        <li><a href="<?php echo esc_url(home_url('/kontakty/')); ?>">Контакты</a></li>
                        <li><a href="<?php echo esc_url(home_url('/pravovaya-informatsiya/')); ?>">Правовая информация</a></li>
                    </ul>
                </div>
            </aside>
        </div>
    <?php endwhile; ?>
</div>
<?php
get_footer();
