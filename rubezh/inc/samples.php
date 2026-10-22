<?php
/**
 * Первичная сборка издания. Образцы явно помечены и не изображают реальных людей.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'rubezh_finish_setup', 20 );
/**
 * Дожимает имя и постоянные ссылки, если включение темы прошло на служебных настройках.
 */
function rubezh_finish_setup() {
	if ( ! get_option( 'rubezh_named' ) ) {
		rubezh_seed_identity();
		update_option( 'rubezh_named', 1 );
	}
	if ( get_option( 'rubezh_seeded' ) && ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules( false );
	}
	rubezh_remove_default_post();
}

add_action( 'after_switch_theme', 'rubezh_after_switch' );
/**
 * Правила, таблица и образцы после включения темы.
 */
function rubezh_after_switch() {
	rubezh_register_content_types();
	rubezh_install_tables();
	rubezh_seed_samples();
	flush_rewrite_rules();
}

/**
 * Образцы и страницы — один раз, на ещё не настроенном сайте.
 */
function rubezh_seed_samples() {
	if ( get_option( 'rubezh_seeded' ) ) {
		return;
	}

	rubezh_seed_identity();
	update_option( 'rubezh_named', 1 );
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	rubezh_remove_default_post();
	rubezh_seed_terms();
	$pages = rubezh_seed_pages();
	rubezh_seed_posts();
	rubezh_seed_heroes();
	rubezh_seed_initiatives();
	rubezh_seed_menu( $pages );
	update_option( 'rubezh_seeded', 1 );
}

/**
 * Имя и описание, только если стоят служебные значения WordPress.
 */
function rubezh_seed_identity() {
	$names = array( '', 'WordPress', 'Мой сайт', 'My WordPress Website' );
	if ( in_array( (string) get_option( 'blogname' ), $names, true ) ) {
		update_option( 'blogname', 'Рубеж' );
	}
	$descriptions = array(
		'',
		'Just another WordPress site',
		'Ещё один сайт на WordPress',
		'Просто ещё один сайт на WordPress',
	);
	if ( in_array( (string) get_option( 'blogdescription' ), $descriptions, true ) ) {
		update_option( 'blogdescription', 'Летопись об антитерроре, память и фонд инициатив' );
	}
}

/**
 * Редакционные рубрики.
 */
/**
 * Убирает служебную запись WordPress, чтобы она не вставала в выпуск.
 */
function rubezh_remove_default_post() {
	if ( get_option( 'rubezh_hello_cleared' ) ) {
		return;
	}
	$candidates = get_posts(
		array(
			'post_type'   => 'post',
			'post_status' => 'any',
			'numberposts' => 20,
		)
	);
	foreach ( $candidates as $candidate ) {
		$titles = array( 'Hello world!', 'Привет, мир!' );
		if ( in_array( $candidate->post_title, $titles, true ) && ! get_post_meta( $candidate->ID, '_rubezh_sample', true ) ) {
			wp_delete_post( $candidate->ID, true );
		}
	}
	update_option( 'rubezh_hello_cleared', 1 );
}

function rubezh_seed_terms() {
	$terms = array(
		'hronika'      => 'Хроника',
		'razbor'       => 'Разбор',
		'profilaktika' => 'Профилактика',
	);
	foreach ( $terms as $slug => $name ) {
		if ( ! term_exists( $slug, 'category' ) ) {
			wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
	}
}

/**
 * Главная и страница записей, если сайт ещё показывает последние записи.
 *
 * @return array{front: int, journal: int}
 */
function rubezh_seed_pages() {
	$front   = rubezh_ensure_page( 'glavnaya', 'Главная', '' );
	$journal = rubezh_ensure_page( 'letopis', 'Летопись', '' );

	if ( 'posts' === get_option( 'show_on_front' ) && ! get_option( 'page_on_front' ) && $front && $journal ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front );
		update_option( 'page_for_posts', $journal );
	}

	return array(
		'front'   => (int) $front,
		'journal' => (int) $journal,
	);
}

/**
 * @param string $slug    Ярлык.
 * @param string $title   Заголовок.
 * @param string $content Текст.
 * @return int
 */
function rubezh_ensure_page( $slug, $title, $content ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		return (int) $existing->ID;
	}
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => $content,
		)
	);
	return is_wp_error( $id ) ? 0 : (int) $id;
}

/**
 * Три объясняющих материала. Это документация шаблона, не новости.
 */
function rubezh_seed_posts() {
	$items = array(
		array(
			'slug'     => 'obrazec-kak-ustroen-vypusk',
			'title'    => 'Образец. Как устроен этот выпуск',
			'category' => 'hronika',
			'excerpt'  => 'Демонстрационная запись о составе главной: летопись, память и фонд инициатив.',
			'content'  => '<p>Это демонстрационный материал шаблона «Рубеж». Удалите его или замените проверенной публикацией.</p><p>Главная собрана из пяти разделов. Их порядок и состав меняются в настройщике: Внешний вид → Настроить → Рубеж. Летопись берёт обычные записи, память и инициативы — отдельные типы записей в консоли.</p><p>Рубрики «Хроника», «Разбор» и «Профилактика» задают тон ленты. Названия можно переименовать.</p>',
		),
		array(
			'slug'     => 'obrazec-pravila-pamyati',
			'title'    => 'Образец. Что публиковать в разделе памяти',
			'category' => 'razbor',
			'excerpt'  => 'Короткие правила карточки: только проверенные сведения и спокойный тон.',
			'content'  => '<p>Карточка памяти — это имя, годы, роль, место и короткая справка. Не добавляйте непроверенные биографии, графические подробности и домыслы.</p><p>Образцы в разделе памяти не изображают реальных людей. Перед публикацией сайта их нужно удалить и завести записи по документам, которые редакция готова подтвердить.</p>',
		),
		array(
			'slug'     => 'obrazec-kak-ustroen-golos',
			'title'    => 'Образец. Как устроен голос за инициативу',
			'category' => 'profilaktika',
			'excerpt'  => 'Поддержка инициативы — общественная позиция. Она не заменяет решение органа.',
			'content'  => '<p>Посетитель может один раз поддержать открытую инициативу и снять поддержку тем же действием. Вошедший пользователь голосует со своего аккаунта, гость — с этого браузера.</p><p>В настройщике можно оставить голос только для вошедших. Адрес посетителя в таблицу голосов не записывается. Итог на сайте — не референдум и не поручение государственным органам.</p>',
		),
	);

	$first = 0;
	foreach ( $items as $item ) {
		$existing = get_posts(
			array(
				'name'        => $item['slug'],
				'post_type'   => 'post',
				'post_status' => 'any',
				'numberposts' => 1,
			)
		);
		if ( $existing ) {
			$first = $first ? $first : (int) $existing[0]->ID;
			continue;
		}
		$term = get_term_by( 'slug', $item['category'], 'category' );
		$id   = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_name'    => $item['slug'],
				'post_title'   => $item['title'],
				'post_excerpt' => $item['excerpt'],
				'post_content' => $item['content'],
				'post_category'=> $term ? array( (int) $term->term_id ) : array(),
			)
		);
		if ( ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_rubezh_sample', '1' );
			if ( ! $first ) {
				$first = (int) $id;
			}
		}
	}
	if ( $first ) {
		stick_post( $first );
	}
}

/**
 * Три пустые формы карточек, чтобы была видна лента памяти.
 */
function rubezh_seed_heroes() {
	$items = array(
		array(
			'slug'    => 'obrazec-kartochki-pamyati',
			'title'   => 'Образец: поле имени',
			'years'   => 'гггг—гггг',
			'role'    => 'Роль или служба',
			'place'   => 'Населённый пункт',
			'excerpt' => 'Краткая проверенная справка. Замените текст перед публикацией.',
		),
		array(
			'slug'    => 'obrazec-vtoraya-kartochka',
			'title'   => 'Образец: вторая карточка',
			'years'   => 'гггг—гггг',
			'role'    => 'Гражданская роль',
			'place'   => 'Регион',
			'excerpt' => 'Портрет можно поставить изображением записи. Без фото карточка остаётся типографской.',
		),
		array(
			'slug'    => 'obrazec-tretya-kartochka',
			'title'   => 'Образец: третья карточка',
			'years'   => 'гггг—гггг',
			'role'    => 'Служба',
			'place'   => 'Город',
			'excerpt' => 'Порядок карточек задаётся полем «Порядок» в атрибутах страницы.',
		),
	);
	$order = 1;
	foreach ( $items as $item ) {
		if ( rubezh_post_exists( $item['slug'], 'hero' ) ) {
			++$order;
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'hero',
				'post_status'  => 'publish',
				'post_name'    => $item['slug'],
				'post_title'   => $item['title'],
				'post_excerpt' => $item['excerpt'],
				'post_content' => '<p>Демонстрационная карточка шаблона. Она не описывает реального человека. Удалите запись и добавьте сведения, которые редакция может подтвердить.</p>',
				'menu_order'   => $order,
			)
		);
		if ( ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_rubezh_sample', '1' );
			update_post_meta( $id, '_rubezh_years', $item['years'] );
			update_post_meta( $id, '_rubezh_role', $item['role'] );
			update_post_meta( $id, '_rubezh_place', $item['place'] );
		}
		++$order;
	}
}

/**
 * Три гражданских примера. Без тактических подробностей.
 */
function rubezh_seed_initiatives() {
	$items = array(
		array(
			'slug'    => 'obrazec-zanyatiya-o-perepiske',
			'title'   => 'Образец. Школьные занятия об осторожной переписке',
			'excerpt' => 'Разговор с подростками о том, что незнакомое сообщение можно не продолжать и показать взрослому.',
			'content' => '<p>Демонстрационная инициатива. Предложение остаётся на уровне школьного разговора и не описывает приёмы вербовки.</p><p>Замените текст реальной инициативой, укажите, кто её предложил, и оставьте статус «Приём голосов», если поддержку ещё можно собирать.</p>',
		),
		array(
			'slug'    => 'obrazec-memorialnaya-polka',
			'title'   => 'Образец. Мемориальная полка в городской библиотеке',
			'excerpt' => 'Открытая полка с проверенными книгами и короткими справками, без сенсационных подробностей.',
			'content' => '<p>Демонстрационная инициатива о спокойном месте памяти. Укажите библиотеку и состав полки только когда договорённость реальна.</p>',
		),
		array(
			'slug'    => 'obrazec-psihologicheskaya-podderzhka',
			'title'   => 'Образец. Группа психологической поддержки для семей',
			'excerpt' => 'Регулярные встречи со специалистом для родных людей, которых затронуло насилие.',
			'content' => '<p>Демонстрационная инициатива. Не публикуйте контакты специалистов и расписание, пока группа действительно собирается.</p>',
		),
	);
	$order = 1;
	foreach ( $items as $item ) {
		if ( rubezh_post_exists( $item['slug'], 'initiative' ) ) {
			++$order;
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'initiative',
				'post_status'  => 'publish',
				'post_name'    => $item['slug'],
				'post_title'   => $item['title'],
				'post_excerpt' => $item['excerpt'],
				'post_content' => $item['content'],
				'menu_order'   => $order,
			)
		);
		if ( ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_rubezh_sample', '1' );
			update_post_meta( $id, '_rubezh_status', 'open' );
			update_post_meta( $id, '_rubezh_proposer', 'Демонстрационный автор' );
			update_post_meta( $id, '_rubezh_votes', 0 );
		}
		++$order;
	}
}

/**
 * @param string $slug Ярлык.
 * @param string $type Тип.
 * @return bool
 */
function rubezh_post_exists( $slug, $type ) {
	$existing = get_posts(
		array(
			'name'        => $slug,
			'post_type'   => $type,
			'post_status' => 'any',
			'numberposts' => 1,
		)
	);
	return ! empty( $existing );
}

/**
 * Меню, если основное ещё не назначено.
 *
 * @param array{front: int, journal: int} $pages Страницы.
 */
function rubezh_seed_menu( $pages ) {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! empty( $locations['primary'] ) ) {
		return;
	}
	$menu_id = wp_create_nav_menu( 'Рубеж' );
	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	$items = array(
		array( __( 'Главная', 'rubezh' ), get_permalink( $pages['front'] ) ),
		array( __( 'Летопись', 'rubezh' ), get_permalink( $pages['journal'] ) ),
		array( __( 'Память', 'rubezh' ), get_post_type_archive_link( 'hero' ) ),
		array( __( 'Инициативы', 'rubezh' ), get_post_type_archive_link( 'initiative' ) ),
		array( __( 'Принципы', 'rubezh' ), home_url( '/#principi' ) ),
	);
	foreach ( $items as $item ) {
		if ( ! $item[1] ) {
			continue;
		}
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => $item[0],
				'menu-item-url'    => $item[1],
				'menu-item-status' => 'publish',
				'menu-item-type'   => 'custom',
			)
		);
	}
	$locations['primary'] = (int) $menu_id;
	$locations['footer']  = (int) $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

add_action( 'admin_notices', 'rubezh_sample_notice' );
/**
 * Напоминание в консоли, пока образцы опубликованы.
 */
function rubezh_sample_notice() {
	if ( ! current_user_can( 'edit_posts' ) || ! rubezh_has_samples() ) {
		return;
	}
	if ( get_user_meta( get_current_user_id(), 'rubezh_hide_sample_notice', true ) ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=rubezh_dismiss_samples' ), 'rubezh_dismiss_samples' );
	echo '<div class="notice notice-warning"><p>' . esc_html__( 'На сайте опубликованы демонстрационные материалы «Рубежа». Замените их проверенными сведениями или удалите. Образцы памяти не описывают реальных людей.', 'rubezh' ) . ' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Скрыть напоминание', 'rubezh' ) . '</a></p></div>';
}

add_action( 'admin_post_rubezh_dismiss_samples', 'rubezh_dismiss_samples' );
/**
 * Скрывает напоминание у текущего пользователя. Публичная плашка остаётся.
 */
function rubezh_dismiss_samples() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'rubezh' ) );
	}
	check_admin_referer( 'rubezh_dismiss_samples' );
	update_user_meta( get_current_user_id(), 'rubezh_hide_sample_notice', '1' );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
}
