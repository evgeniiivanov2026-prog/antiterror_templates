<?php
/**
 * Article.
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
        <header class="article-head">
            <?php gnss_breadcrumbs(); ?>
            <?php echo wp_kses_post(gnss_kicker_html()); ?>
            <h1><?php the_title(); ?></h1>
            <p class="dek"><?php echo esc_html(gnss_dek()); ?></p>
            <p class="meta"><?php echo esc_html(gnss_meta_line()); ?> · <?php the_author(); ?></p>
        </header>
        <div class="article-layout">
            <article <?php post_class('prose'); ?>>
                <?php the_content(); ?>
                <p class="note">Материал подготовила редакция GNSS.PRO по открытым техническим описаниям. Это не официальная публикация оператора системы.</p>
            </article>
            <aside class="article-aside">
                <div class="aside-card fact-box">
                    <h2>Штатная группировка</h2>
                    <p><strong>24</strong> аппарата, 3 плоскости, наклонение 64,8°. Фактический состав на орбите меняется и в эту карточку не входит.</p>
                </div>
                <?php get_template_part('template-parts/related'); ?>
                <div class="aside-card">
                    <h2>Ещё</h2>
                    <ul>
                        <li><a href="<?php echo esc_url(home_url('/glossariy/')); ?>">Глоссарий</a></li>
                        <li><a href="<?php echo esc_url(gnss_journal_url()); ?>">Все материалы</a></li>
                    </ul>
                </div>
            </aside>
        </div>
        <?php comments_template(); ?>
    <?php endwhile; ?>
</div>
<?php
get_footer();
