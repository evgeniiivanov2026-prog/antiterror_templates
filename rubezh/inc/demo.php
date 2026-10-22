<?php
/**
 * Демо-контент при первой активации — редактор сразу видит журнал, память и фонд.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_switch_theme', 'rubezh_maybe_seed' );
function rubezh_maybe_seed() {
	rubezh_register_cpts();
	if ( get_option( 'rubezh_seeded' ) ) {
		return;
	}
	$heroes = wp_count_posts( 'hero' );
	if ( $heroes && (int) $heroes->publish > 0 ) {
		update_option( 'rubezh_seeded', 1 );
		return;
	}
	rubezh_seed_content();
	update_option( 'rubezh_seeded', 1 );
}

function rubezh_seed_content() {
	$cats = array();
	foreach ( array(
		'analitika'    => 'Аналитика',
		'pravo'        => 'Право',
		'profilaktika' => 'Профилактика',
		'reportazh'    => 'Репортаж',
	) as $slug => $name ) {
		$term = term_exists( $name, 'category' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
		if ( ! is_wp_error( $term ) ) {
			$cats[ $slug ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
		}
	}

	$services = array();
	foreach ( array( 'МВД', 'ФСБ', 'Росгвардия', 'Гражданский подвиг' ) as $name ) {
		$term = term_exists( $name, 'hero_service' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'hero_service' );
		}
		if ( ! is_wp_error( $term ) ) {
			$services[ $name ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
		}
	}

	$topics = array();
	foreach ( array( 'Образование', 'Память', 'Технологии', 'Семьи' ) as $name ) {
		$term = term_exists( $name, 'initiative_topic' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'initiative_topic' );
		}
		if ( ! is_wp_error( $term ) ) {
			$topics[ $name ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
		}
	}

	$posts = array(
		array(
			'title'   => 'Как общество отвечает на угрозу: журнал, а не сенсация',
			'excerpt' => 'Почему антитеррор в публичном поле должен звучать спокойно, проверяемо и без театрализации насилия.',
			'cat'     => 'analitika',
			'content' => "<p>Журнал «Рубеж» собирает материалы о противодействии терроризму как о гражданской работе: факты, право, память и профилактика. Мы не публикуем инструкции и не воспроизводим сцены насилия.</p><p>Задача выпуска — дать читателю ясную рамку: что можно сделать легально, как хранить имена героев и какие инициативы заслуживают поддержки.</p>",
		),
		array(
			'title'   => 'Правовая рамка: что обязан знать каждый редактор',
			'excerpt' => 'Граница между информированием и вредом. Как писать о терактах, не становясь рупором страха.',
			'cat'     => 'pravo',
			'content' => '<p>Публикации о терроризме подчиняются закону и профессиональной этике. Мы фиксируем официальные сведения, не раскрываем оперативные детали и не даём слово пропаганде насилия.</p>',
		),
		array(
			'title'   => 'Школа и улица: профилактика без запугивания',
			'excerpt' => 'Педагоги и родители спрашивают, как говорить с подростками. Короткий гид без паники.',
			'cat'     => 'profilaktika',
			'content' => '<p>Профилактика работает там, где есть доверие. Тема безопасности в школе — это навыки обращения за помощью, цифровая гигиена и уважение к памяти, а не демонстрация жестокости.</p>',
		),
		array(
			'title'   => 'Город после удара: как восстанавливают доверие',
			'excerpt' => 'Репортаж о мемориальных практиках и работе волонтёров — без вымышленных цифр и героев.',
			'cat'     => 'reportazh',
			'content' => '<p>После чрезвычайного события городу нужна правда и тишина одновременно. Журнал описывает ритуалы памяти и работу служб так, чтобы не травмировать повторно.</p>',
		),
		array(
			'title'   => 'Открытые данные и закрытые раны',
			'excerpt' => 'Какие сведения общество имеет право знать — и какие обязано беречь.',
			'cat'     => 'analitika',
			'content' => '<p>Прозрачность фонда не означает публикацию персональных данных пострадавших. Мы разделяем отчётность и частную жизнь.</p>',
		),
		array(
			'title'   => 'Семьи героев: сопровождение, а не лозунги',
			'excerpt' => 'Почему фонд инициатив начинается с конкретных просьб близких, а не с громких кампаний.',
			'cat'     => 'reportazh',
			'content' => '<p>Голос семьи важнее постановки. Инициативы, которые проходят голосование на «Рубеже», проверяются на законность и этичность до публикации.</p>',
		),
	);

	foreach ( $posts as $item ) {
		$id = wp_insert_post( array(
			'post_title'   => $item['title'],
			'post_excerpt' => $item['excerpt'],
			'post_content' => $item['content'],
			'post_status'  => 'publish',
			'post_type'    => 'post',
		) );
		if ( $id && ! is_wp_error( $id ) && isset( $cats[ $item['cat'] ] ) ) {
			wp_set_post_categories( $id, array( $cats[ $item['cat'] ] ) );
		}
	}

	$heroes = array(
		array( 'name' => 'Алексей Морозов', 'years' => '1979–2004', 'rank' => 'Майор', 'service' => 'ФСБ', 'place' => 'Северный Кавказ', 'line' => 'Закрыл группу от огня, вывел гражданских.' ),
		array( 'name' => 'Ирина Белова', 'years' => '1984–2010', 'rank' => 'Старший лейтенант', 'service' => 'МВД', 'place' => 'Москва', 'line' => 'Обеспечила эвакуацию людей из здания.' ),
		array( 'name' => 'Павел Крылов', 'years' => '1972–2002', 'rank' => 'Подполковник', 'service' => 'Росгвардия', 'place' => 'Дагестан', 'line' => 'Удержал позицию до подхода подкрепления.' ),
		array( 'name' => 'Мария Савельева', 'years' => '1990–2013', 'rank' => 'Медик', 'service' => 'Гражданский подвиг', 'place' => 'Волгоград', 'line' => 'Оказывала помощь раненым, не оставив пост.' ),
	);

	foreach ( $heroes as $hero ) {
		$id = wp_insert_post( array(
			'post_title'   => $hero['name'],
			'post_excerpt' => $hero['line'],
			'post_content' => '<p>' . esc_html( $hero['line'] ) . '</p><p>Карточка приведена как пример структуры памяти. Замените её проверяемой биографией и согласием семьи перед публикацией.</p>',
			'post_status'  => 'publish',
			'post_type'    => 'hero',
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_rubezh_years', $hero['years'] );
			update_post_meta( $id, '_rubezh_rank', $hero['rank'] );
			update_post_meta( $id, '_rubezh_place', $hero['place'] );
			update_post_meta( $id, '_rubezh_line', $hero['line'] );
			if ( isset( $services[ $hero['service'] ] ) ) {
				wp_set_object_terms( $id, array( $services[ $hero['service'] ] ), 'hero_service' );
			}
		}
	}

	$initiatives = array(
		array( 'title' => 'Уроки цифровой устойчивости в школах', 'topic' => 'Образование', 'author' => 'Педагогический совет', 'votes' => 184, 'excerpt' => 'Программа для 8–11 классов: как распознавать вербовку и куда сообщать, не становясь следователем.' ),
		array( 'title' => 'Мемориальный маршрут городов', 'topic' => 'Память', 'author' => 'Союз семей', 'votes' => 151, 'excerpt' => 'Единые таблички и аудиогид о героях антитеррора — без пафоса и без забвения.' ),
		array( 'title' => 'Фонд сопровождения семей', 'topic' => 'Семьи', 'author' => 'Инициативная группа', 'votes' => 129, 'excerpt' => 'Юридическая и психологическая помощь семьям погибших — прозрачный реестр и отчётность.' ),
		array( 'title' => 'Открытый архив памяти', 'topic' => 'Технологии', 'author' => 'Архивисты', 'votes' => 96, 'excerpt' => 'Оцифровка писем и наград с согласием родственников. Поиск по имени, служба, год.' ),
	);

	foreach ( $initiatives as $item ) {
		$id = wp_insert_post( array(
			'post_title'   => $item['title'],
			'post_excerpt' => $item['excerpt'],
			'post_content' => '<p>' . esc_html( $item['excerpt'] ) . '</p><p>Инициатива открыта для общественного приоритета. Один голос с устройства. Фонд рассматривает лидеров рейтинга в правовом поле.</p>',
			'post_status'  => 'publish',
			'post_type'    => 'initiative',
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_rubezh_author', $item['author'] );
			update_post_meta( $id, '_rubezh_votes', $item['votes'] );
			update_post_meta( $id, '_rubezh_status', 'open' );
			if ( isset( $topics[ $item['topic'] ] ) ) {
				wp_set_object_terms( $id, array( $topics[ $item['topic'] ] ), 'initiative_topic' );
			}
		}
	}

	$page_id = wp_insert_post( array(
		'post_title'   => 'О проекте',
		'post_name'    => 'o-proekte',
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => '<p>«Рубеж» — журнальный шаблон антитеррора. Он соединяет редакционную сетку JournalX и гражданский тон темы Root «Академический»: институт, а не сенсация.</p><p>На сайте нет оперативных инструкций. В экстренной ситуации звоните 112.</p>',
	) );

	if ( $page_id && ! is_wp_error( $page_id ) && ! wp_get_nav_menu_object( 'rubezh-primary' ) ) {
		$menu_id = wp_create_nav_menu( 'Рубеж — основное' );
		$items   = array(
			array( 'title' => 'Журнал', 'url' => home_url( '/#journal' ) ),
			array( 'title' => 'Память', 'url' => rubezh_archive_link( 'hero' ) ),
			array( 'title' => 'Фонд', 'url' => rubezh_archive_link( 'initiative' ) ),
			array( 'title' => 'О проекте', 'object' => $page_id ),
		);
		foreach ( $items as $item ) {
			$args = array(
				'menu-item-title'  => $item['title'],
				'menu-item-status' => 'publish',
			);
			if ( isset( $item['object'] ) ) {
				$args['menu-item-type']      = 'post_type';
				$args['menu-item-object']    = 'page';
				$args['menu-item-object-id'] = $item['object'];
			} else {
				$args['menu-item-type'] = 'custom';
				$args['menu-item-url']  = $item['url'];
			}
			wp_update_nav_menu_item( $menu_id, 0, $args );
		}
		$locations            = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $menu_id;
		$locations['footer']  = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}
