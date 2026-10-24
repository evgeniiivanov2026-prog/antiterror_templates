<?php
/**
 * Site footer.
 */

if (!defined('ABSPATH')) {
    exit;
}

$pages = [
    'zhurnal' => 'Журнал',
    'sistemy' => 'Системы',
    'glossariy' => 'Глоссарий',
    'o-zhurnale' => 'О журнале',
    'kontakty' => 'Контакты',
    'privacy-policy' => 'Конфиденциальность',
];
?>
<footer class="site-footer">
    <div class="wrap footer-grid">
        <div>
            <p class="footer-brand">GNSS<span>.</span>PRO</p>
            <p>Журнал о спутниковой навигации. Пишем, откуда берётся координата, какую точность обещает метод и где это обещание кончается.</p>
        </div>
        <div>
            <p class="footer-label">Рубрики</p>
            <ul class="footer-links">
                <?php foreach (gnss_ordered_categories() as $category) : ?>
                    <li><a href="<?php echo esc_url(get_category_link($category)); ?>"><?php echo esc_html($category->name); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <p class="footer-label">Журнал</p>
            <ul class="footer-links">
                <?php foreach ($pages as $slug => $label) : ?>
                    <?php $page = get_page_by_path($slug); ?>
                    <li><a href="<?php echo esc_url($page ? get_permalink($page) : home_url('/' . $slug . '/')); ?>"><?php echo esc_html($label); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <p class="footer-label">Редакция</p>
            <p><a href="mailto:hello@gnss.pro">hello@gnss.pro</a></p>
            <p>Поправки к числам и темы материалов принимаем письмом. Живой состав группировки ГЛОНАСС публикует <a href="https://glonass-iac.ru/" rel="noopener noreferrer">ИАЦ КВНО</a>.</p>
        </div>
    </div>
    <div class="wrap footer-bottom">
        <p>© <?php echo esc_html(wp_date('Y')); ?> GNSS.PRO</p>
        <p>Номинальные параметры систем не заменяют оперативный альманах.</p>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
