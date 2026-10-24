<?php
/**
 * Site footer.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
</main>
<footer class="site-footer">
    <div class="shell footer-grid">
        <div class="footer-brand">
            <strong>GNSS.PRO</strong>
            <p>Независимый журнал о ГЛОНАСС и спутниковой навигации. Объясняем штатную архитектуру, открытые сигналы и точность по опубликованным документам.</p>
        </div>
        <div>
            <h2>Разделы</h2>
            <ul>
                <?php foreach (gnss_sections() as $section) : ?>
                    <li><a href="<?php echo esc_url(get_category_link($section)); ?>"><?php echo esc_html($section->name); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h2>Журнал</h2>
            <?php
            wp_nav_menu([
                'theme_location' => 'footer',
                'container' => false,
                'menu_class' => '',
                'fallback_cb' => false,
                'depth' => 1,
            ]);
            ?>
        </div>
    </div>
    <div class="shell footer-base">
        <p>© <?php echo esc_html(wp_date('Y')); ?> GNSS.PRO. Издание не является официальным ресурсом оператора ГЛОНАСС.</p>
        <p><a href="<?php echo esc_url(get_feed_link()); ?>">RSS</a></p>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
