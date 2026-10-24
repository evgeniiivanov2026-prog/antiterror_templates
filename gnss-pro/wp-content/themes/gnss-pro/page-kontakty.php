<?php
/**
 * Contacts page.
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
                <p class="kicker">Редакция</p>
                <h1><?php the_title(); ?></h1>
            </header>
            <div class="contact-card">
                <p class="footer-brand">GNSS<span>.</span>PRO</p>
                <p>Письма читаем по адресу <a href="mailto:hello@gnss.pro">hello@gnss.pro</a>.</p>
                <p>Редакция работает удалённо, отдельного адреса приёмной нет. Для сверки состава группировки ГЛОНАСС используйте сводку <a href="https://glonass-iac.ru/" rel="noopener noreferrer">ИАЦ КВНО</a>, а не письмо в журнал.</p>
                <a class="button" href="mailto:hello@gnss.pro">Написать в редакцию</a>
            </div>
            <div class="prose">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
