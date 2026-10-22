<?php
/**
 * Foundation mission + confidential report + propose.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = aegis_option( 'aegis_foundation_title', __( 'Фонд инициатив антитеррора', 'aegis' ) );
$lead  = aegis_option( 'aegis_foundation_lead', __( 'Мы собираем проверяемые истории, бережём память и даём гражданам инструмент, чтобы выбрать, какие профилактические проекты усилить в первую очередь.', 'aegis' ) );
$email = aegis_email();
?>
<section id="foundation" class="band band--navy" aria-labelledby="foundation-title">
	<div class="shell foundation-grid">
		<header class="reveal">
			<p class="section-kicker section-kicker--light"><?php esc_html_e( '05 / Фонд', 'aegis' ); ?></p>
			<h2 id="foundation-title"><?php echo esc_html( $title ); ?></h2>
		</header>
		<div class="foundation-copy reveal">
			<p class="lede"><?php echo wp_kses_post( $lead ); ?></p>
			<div class="principles">
				<article>
					<span>А</span>
					<h3><?php esc_html_e( 'Ответственность', 'aegis' ); ?></h3>
					<p><?php esc_html_e( 'Работаем с фактами. Не публикуем сенсации и не романтизируем насилие.', 'aegis' ); ?></p>
				</article>
				<article>
					<span>Б</span>
					<h3><?php esc_html_e( 'Законность', 'aegis' ); ?></h3>
					<p><?php esc_html_e( 'Фонд не подменяет следствие. Оперативную информацию передаём по установленному порядку.', 'aegis' ); ?></p>
				</article>
				<article>
					<span>В</span>
					<h3><?php esc_html_e( 'Память', 'aegis' ); ?></h3>
					<p><?php esc_html_e( 'Карточки героев — только согласованные сведения. Имя важнее сюжета.', 'aegis' ); ?></p>
				</article>
			</div>
		</div>
	</div>
</section>

<section class="report" aria-labelledby="report-title">
	<div class="shell report__grid">
		<div>
			<p class="section-kicker section-kicker--light"><?php esc_html_e( '06 / Обращение', 'aegis' ); ?></p>
			<h2 id="report-title"><?php esc_html_e( 'Знаете об угрозе?', 'aegis' ); ?></h2>
			<p><?php esc_html_e( 'Не проводите расследование самостоятельно. Передайте сведения фонду или вызовите экстренные службы.', 'aegis' ); ?></p>
		</div>
		<div class="report__action">
			<?php if ( $email ) : ?>
				<a class="btn btn--light" href="mailto:<?php echo esc_attr( $email ); ?>?subject=<?php echo rawurlencode( __( 'Конфиденциальное обращение', 'aegis' ) ); ?>"><?php esc_html_e( 'Написать фонду', 'aegis' ); ?> <span aria-hidden="true">→</span></a>
			<?php endif; ?>
			<p><?php esc_html_e( 'В экстренной ситуации звоните 112.', 'aegis' ); ?></p>
		</div>
	</div>
</section>

<section id="propose" class="band band--paper" aria-labelledby="propose-title">
	<div class="shell propose">
		<div class="propose__intro reveal">
			<p class="section-kicker"><?php esc_html_e( '07 / Предложить', 'aegis' ); ?></p>
			<h2 id="propose-title"><?php esc_html_e( 'Новая инициатива', 'aegis' ); ?></h2>
			<p><?php esc_html_e( 'Заявка уходит на модерацию. После публикации граждане смогут проголосовать за приоритет.', 'aegis' ); ?></p>
		</div>
		<form class="propose-form" data-propose-form>
			<label>
				<span><?php esc_html_e( 'Название', 'aegis' ); ?></span>
				<input type="text" name="title" required minlength="8" maxlength="140" autocomplete="off">
			</label>
			<label>
				<span><?php esc_html_e( 'Описание', 'aegis' ); ?></span>
				<textarea name="description" required minlength="40" rows="5"></textarea>
			</label>
			<div class="propose-form__row">
				<label>
					<span><?php esc_html_e( 'Автор или группа', 'aegis' ); ?></span>
					<input type="text" name="author" maxlength="80" autocomplete="name">
				</label>
				<label>
					<span><?php esc_html_e( 'Тема', 'aegis' ); ?></span>
					<input type="text" name="topic" maxlength="60" placeholder="<?php esc_attr_e( 'Профилактика, память, помощь', 'aegis' ); ?>">
				</label>
			</div>
			<label class="hp" aria-hidden="true">
				<span>Website</span>
				<input type="text" name="website" tabindex="-1" autocomplete="off">
			</label>
			<button class="btn btn--primary" type="submit"><?php esc_html_e( 'Отправить на модерацию', 'aegis' ); ?></button>
			<p class="form-status" data-form-status role="status" aria-live="polite"></p>
		</form>
	</div>
</section>
