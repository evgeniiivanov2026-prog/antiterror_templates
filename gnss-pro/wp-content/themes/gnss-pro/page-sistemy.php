<?php
/**
 * Systems reference page.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
$globals = array_values(array_filter(gnss_systems(), static function (array $system): bool {
    return $system['scope'] === 'global';
}));
$regional = array_values(array_filter(gnss_systems(), static function (array $system): bool {
    return $system['scope'] === 'regional';
}));
?>
<main id="content" class="wrap section">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('article'); ?>>
            <header class="article-head">
                <p class="kicker">Справочник</p>
                <h1><?php the_title(); ?></h1>
            </header>
            <div class="prose">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>

    <p class="scroll-hint">На узком экране таблицу можно листать вбок.</p>
    <div class="table-wrap">
        <table>
            <caption>Номинальные параметры глобальных GNSS</caption>
            <thead>
                <tr>
                    <th>Система</th>
                    <th>Оператор</th>
                    <th>Номинал</th>
                    <th>Плоскости</th>
                    <th>Высота</th>
                    <th>Наклонение</th>
                    <th>Координаты</th>
                    <th>Время</th>
                    <th>Открытые сигналы</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($globals as $system) : ?>
                    <tr id="<?php echo esc_attr($system['id']); ?>">
                        <th><?php echo esc_html($system['name']); ?></th>
                        <td><?php echo esc_html($system['operator']); ?></td>
                        <td><?php echo esc_html($system['sats']); ?></td>
                        <td><?php echo esc_html($system['planes']); ?></td>
                        <td><?php echo esc_html($system['altitude']); ?></td>
                        <td><?php echo esc_html($system['inclination']); ?></td>
                        <td><?php echo esc_html($system['frame']); ?></td>
                        <td><?php echo esc_html($system['time']); ?></td>
                        <td><?php echo esc_html($system['signals']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="system-grid section-tight">
        <?php foreach ($globals as $system) : ?>
            <article class="system-card" id="<?php echo esc_attr($system['id'] . '-note'); ?>">
                <p class="kicker"><?php echo esc_html($system['operator']); ?></p>
                <h2><?php echo esc_html($system['name']); ?></h2>
                <p><?php echo esc_html($system['summary']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>

    <header class="page-head">
        <p class="kicker">Не глобальные, но важные</p>
        <h2>Региональные дополнения</h2>
    </header>
    <div class="system-grid">
        <?php foreach ($regional as $system) : ?>
            <article class="system-card" id="<?php echo esc_attr($system['id']); ?>">
                <p class="kicker"><?php echo esc_html($system['operator']); ?></p>
                <h2><?php echo esc_html($system['name']); ?></h2>
                <p><?php echo esc_html($system['summary']); ?></p>
                <p class="meta"><?php echo esc_html($system['signals']); ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</main>
<?php
get_footer();
