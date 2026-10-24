<?php
/**
 * Contact form and status message.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

$code = isset($_GET['contact']) ? sanitize_key(wp_unslash($_GET['contact'])) : '';
$messages = [
    'sent' => 'Письмо сохранено. Редакция увидит его в кабинете сайта.',
    'missing' => 'Нужны имя, текст не короче 10 символов и согласие на сохранение письма.',
    'email' => 'Похоже, в адресе почты ошибка.',
    'limit' => 'С этого адреса только что уже отправили письмо. Подождите полминуты.',
    'invalid' => 'Не удалось принять форму. Обновите страницу и отправьте ещё раз.',
];
?>
<?php if (isset($messages[$code])) : ?>
    <p class="<?php echo $code === 'sent' ? 'notice' : 'form-error'; ?>" role="status"><?php echo esc_html($messages[$code]); ?></p>
<?php endif; ?>
<form class="contact-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="gnss_contact">
    <?php wp_nonce_field('gnss_contact', 'gnss_contact_nonce'); ?>
    <p class="hp">
        <label>Компания <input type="text" name="gnss_company" tabindex="-1" autocomplete="off"></label>
    </p>
    <label for="gnss_name">Имя
        <input id="gnss_name" name="gnss_name" type="text" required maxlength="120" autocomplete="name">
    </label>
    <label for="gnss_email">Почта
        <input id="gnss_email" name="gnss_email" type="email" required maxlength="160" autocomplete="email">
    </label>
    <label for="gnss_topic">Тема
        <select id="gnss_topic" name="gnss_topic">
            <option value="question">Вопрос</option>
            <option value="correction">Уточнение к материалу</option>
            <option value="other">Другое</option>
        </select>
    </label>
    <label for="gnss_message">Письмо
        <textarea id="gnss_message" name="gnss_message" required minlength="10" maxlength="5000"></textarea>
    </label>
    <label class="check">
        <input type="checkbox" name="gnss_consent" value="1" required>
        <span>Понимаю, что письмо сохранится в редакции и будет использовано только для ответа.</span>
    </label>
    <button class="button" type="submit">Отправить</button>
</form>
