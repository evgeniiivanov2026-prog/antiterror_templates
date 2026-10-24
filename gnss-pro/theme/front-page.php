<?php
/**
 * Magazine front page.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$lead = gnss_lead_post();
$exclude = $lead ? [$lead->ID] : [];
$rest = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 9,
    'post__not_in' => $exclude,
    'ignore_sticky_posts' => true,
]);
$posts = $rest->posts;
$side = array_slice($posts, 0, 3);
$list = array_slice($posts, 3);
?>
<div class="shell">
    <section class="hero">
        <div>
            <p class="kicker">Журнал gnss.pro</p>
            <h1>Спутниковая навигация, разобранная по орбитам, сигналам и времени</h1>
            <p class="dek">Независимое издание о ГЛОНАСС и других GNSS. Пишем по открытым интерфейсным документам: как устроена группировка, откуда берётся координата и чем метр отличается от сантиметра.</p>
            <div class="hero-actions">
                <?php if ($lead) : ?>
                    <a class="button" href="<?php echo esc_url(get_permalink($lead)); ?>">Читать основной материал</a>
                <?php endif; ?>
                <a class="button secondary" href="#ukazatel">Указатель разделов</a>
            </div>
        </div>
        <?php get_template_part('template-parts/constellation'); ?>
    </section>

    <ul class="facts">
        <li><strong>24</strong><span>аппарата в штатной группировке</span></li>
        <li><strong>3</strong><span>орбитальные плоскости по 8 спутников</span></li>
        <li><strong>64,8°</strong><span>наклонение, высота около 19&nbsp;100&nbsp;км</span></li>
        <li><strong>11&nbsp;ч&nbsp;15&nbsp;мин</strong><span>период обращения</span></li>
    </ul>

    <?php if ($lead) : ?>
        <div class="section-head">
            <h2>Сейчас в номере</h2>
            <a href="<?php echo esc_url(gnss_journal_url()); ?>">Все материалы</a>
        </div>
        <div class="lead-grid">
            <article class="lead-story">
                <?php echo wp_kses_post(gnss_kicker_html($lead->ID)); ?>
                <h2><a href="<?php echo esc_url(get_permalink($lead)); ?>"><?php echo esc_html(get_the_title($lead)); ?></a></h2>
                <p class="dek"><?php echo esc_html(gnss_dek($lead->ID)); ?></p>
                <p class="meta"><?php echo esc_html(gnss_meta_line($lead->ID)); ?></p>
            </article>
            <div class="story-side">
                <?php foreach ($side as $post) : ?>
                    <?php setup_postdata($post); ?>
                    <article <?php post_class('side-story'); ?>>
                        <?php echo wp_kses_post(gnss_kicker_html()); ?>
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p class="side-dek"><?php echo esc_html(gnss_dek()); ?></p>
                    </article>
                <?php endforeach; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        </div>
    <?php endif; ?>

    <section class="rubric-index" id="ukazatel">
        <h2>Указатель</h2>
        <ol class="rubric-list">
            <?php foreach (gnss_sections() as $section) : ?>
                <li>
                    <a href="<?php echo esc_url(get_category_link($section)); ?>">
                        <span class="rubric-count"><?php echo esc_html(sprintf('%02d', (int) $section->count)); ?></span>
                        <span>
                            <strong><?php echo esc_html($section->name); ?></strong>
                            <em><?php echo esc_html($section->description); ?></em>
                        </span>
                        <span class="rubric-go" aria-hidden="true">→</span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <?php if ($list) : ?>
        <div class="section-head">
            <h2>Дальше по полке</h2>
        </div>
        <div class="issue-list">
            <?php foreach ($list as $post) : ?>
                <?php setup_postdata($post); ?>
                <?php get_template_part('template-parts/issue-row'); ?>
            <?php endforeach; ?>
            <?php wp_reset_postdata(); ?>
        </div>
    <?php endif; ?>

    <section class="glossary-teaser">
        <div class="section-head">
            <h2>Короткий глоссарий</h2>
            <a href="<?php echo esc_url(home_url('/glossariy/')); ?>">Все термины</a>
        </div>
        <div class="term-grid">
            <a href="<?php echo esc_url(home_url('/glossariy/#psevdodalnost')); ?>">
                <h3>Псевдодальность</h3>
                <p>Расстояние до спутника, в котором ещё сидит ошибка часов приёмника.</p>
            </a>
            <a href="<?php echo esc_url(home_url('/glossariy/#fdma')); ?>">
                <h3>FDMA</h3>
                <p>Частотное разделение: у аппаратов ГЛОНАСС разные несущие на L1 и L2.</p>
            </a>
            <a href="<?php echo esc_url(home_url('/glossariy/#pz-90')); ?>">
                <h3>ПЗ-90.11</h3>
                <p>Система координат, в которой ГЛОНАСС отдаёт положение потребителю.</p>
            </a>
            <a href="<?php echo esc_url(home_url('/glossariy/#sdkm')); ?>">
                <h3>СДКМ</h3>
                <p>Российское широкозонное дополнение: поправки и контроль целостности.</p>
            </a>
        </div>
    </section>
</div>
<?php
get_footer();
