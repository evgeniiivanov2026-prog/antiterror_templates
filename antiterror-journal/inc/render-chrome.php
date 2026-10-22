<?php
/**
 * Header, footer and the emergency band.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site header.
 *
 * @return string
 */
function rubezh_render_header() {
	$vote = get_post_type_archive_link( 'rubezh_initiative' );
	if ( is_front_page() ) {
		$vote_url = '#initiatives';
	} else {
		$vote_url = $vote ? $vote : home_url( '/iniciativy/' );
	}
	ob_start();
	?>
	<a class="rz-skip" href="#content"><?php esc_html_e( 'К содержанию', 'rubezh' ); ?></a>
	<header class="rz-header">
		<div class="rz-shell rz-header__inner">
			<a class="rz-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<?php
				$logo_id = (int) get_theme_mod( 'custom_logo' );
				if ( $logo_id ) {
					echo wp_get_attachment_image( $logo_id, 'thumbnail', false, array( 'class' => 'custom-logo' ) );
				} else {
					echo rubezh_mark(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static mark.
				}
				?>
				<span class="rz-logo__text">
					<strong><?php esc_html_e( 'Журнал антитеррора', 'rubezh' ); ?></strong>
					<b><?php echo esc_html( rubezh_site_name() ); ?></b>
				</span>
			</a>
			<nav class="rz-nav" aria-label="<?php esc_attr_e( 'Основная навигация', 'rubezh' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'rz-nav__list',
						'fallback_cb'    => 'rubezh_fallback_menu',
						'depth'          => 2,
					)
				);
				?>
			</nav>
			<div class="rz-header__tools">
				<button class="rz-icon-btn" type="button" data-search-open aria-expanded="false" aria-controls="rz-search-panel" aria-label="<?php esc_attr_e( 'Открыть поиск', 'rubezh' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6"></circle><path d="M16 16l5 5"></path></svg>
				</button>
				<button class="rz-icon-btn rz-theme" type="button" data-theme-toggle aria-label="<?php esc_attr_e( 'Включить тёмную тему', 'rubezh' ); ?>">
					<svg class="rz-theme__moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.7 15.1A9 9 0 0 1 8.9 3.3 9 9 0 1 0 20.7 15.1Z"></path></svg>
					<svg class="rz-theme__sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path></svg>
				</button>
				<a class="rz-header__vote" href="<?php echo esc_url( $vote_url ); ?>"><?php esc_html_e( 'Голосовать', 'rubezh' ); ?> <span aria-hidden="true">→</span></a>
				<button class="rz-icon-btn rz-burger" type="button" aria-expanded="false" aria-controls="rz-mobile" aria-label="<?php esc_attr_e( 'Открыть меню', 'rubezh' ); ?>">
					<span></span><span></span>
				</button>
			</div>
		</div>
		<div id="rz-search-panel" class="rz-search-panel" hidden>
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="rz-q"><?php esc_html_e( 'Поиск по журналу', 'rubezh' ); ?></label>
				<input id="rz-q" type="search" name="s" placeholder="<?php esc_attr_e( 'Поиск по журналу, памяти и инициативам', 'rubezh' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
			</form>
		</div>
		<div id="rz-mobile" class="rz-mobile" hidden>
			<nav class="rz-shell" aria-label="<?php esc_attr_e( 'Мобильная навигация', 'rubezh' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'rz-mobile__list',
						'fallback_cb'    => 'rubezh_fallback_menu',
						'depth'          => 2,
					)
				);
				?>
				<a href="<?php echo esc_url( $vote_url ); ?>"><?php esc_html_e( 'Голосовать за инициативу', 'rubezh' ); ?></a>
			</nav>
		</div>
	</header>
	<?php
	return (string) ob_get_clean();
}

/**
 * Site footer.
 *
 * @return string
 */
function rubezh_render_footer() {
	$email = rubezh_setting( 'email' );
	ob_start();
	?>
	<footer class="rz-footer">
		<div class="rz-shell rz-footer__grid">
			<div class="rz-footer__brand">
				<a class="rz-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php echo rubezh_mark(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="rz-logo__text">
						<strong><?php esc_html_e( 'Журнал антитеррора', 'rubezh' ); ?></strong>
						<b><?php echo esc_html( rubezh_site_name() ); ?></b>
					</span>
				</a>
				<p><?php echo esc_html( get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) : __( 'Память, хроника и фонд инициатив.', 'rubezh' ) ); ?></p>
			</div>
			<nav class="rz-footer__nav" aria-label="<?php esc_attr_e( 'Навигация в подвале', 'rubezh' ); ?>">
				<p><?php esc_html_e( 'Разделы', 'rubezh' ); ?></p>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'rz-nav__list',
						'fallback_cb'    => 'rubezh_fallback_menu',
						'depth'          => 1,
					)
				);
				?>
			</nav>
			<div class="rz-footer__contact">
				<p><?php esc_html_e( 'Связаться', 'rubezh' ); ?></p>
				<?php if ( $email ) : ?>
					<a href="<?php echo esc_url( 'mailto:' . antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
				<?php endif; ?>
				<a href="tel:112"><?php esc_html_e( 'Экстренная помощь — 112', 'rubezh' ); ?></a>
			</div>
		</div>
		<div class="rz-shell rz-footer__bottom">
			<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( rubezh_site_name() ); ?></span>
			<span><?php esc_html_e( 'Материалы с пометкой «Образец» демонстрационные. Сайт не заменяет обращение в экстренные службы.', 'rubezh' ); ?></span>
		</div>
	</footer>
	<?php
	return (string) ob_get_clean();
}

/**
 * Emergency / editorial contact band.
 *
 * @return string
 */
function rubezh_render_signal() {
	$email = sanitize_email( rubezh_setting( 'email' ) );
	ob_start();
	?>
	<section id="help" class="rz-band rz-reveal" aria-labelledby="rz-help-title">
		<div class="rz-shell rz-band__grid">
			<div>
				<p class="rz-index"><?php esc_html_e( '04 — Если рядом опасность', 'rubezh' ); ?></p>
				<h2 id="rz-help-title"><?php esc_html_e( 'Сначала 112. Потом текст.', 'rubezh' ); ?></h2>
				<p><?php esc_html_e( 'Отойдите в безопасное место, не подходите и не пытайтесь проверять угрозу сами. Не публикуйте непроверенные детали. Редакция не принимает оперативные сообщения и не заменяет службы.', 'rubezh' ); ?></p>
			</div>
			<div class="rz-band__side">
				<?php if ( $email ) : ?>
					<a class="rz-button rz-button--light" href="<?php echo esc_url( 'mailto:' . antispambot( $email ) . '?subject=' . rawurlencode( 'Редакции журнала' ) ); ?>"><?php esc_html_e( 'Написать в редакцию', 'rubezh' ); ?> <span aria-hidden="true">→</span></a>
					<p><?php esc_html_e( 'Только темы номера и общественные инициативы.', 'rubezh' ); ?></p>
				<?php else : ?>
					<p><?php esc_html_e( 'Почта редакции ещё не указана. Экстренный номер — 112.', 'rubezh' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}
