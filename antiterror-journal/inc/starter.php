<?php
/**
 * First-run pages, sample entries and the menu.
 * Samples are marked and can be replaced or deleted.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_switch_theme', 'rubezh_on_switch' );

/**
 * Pretty permalinks, rewrite rules and the sample newsroom.
 */
function rubezh_on_switch() {
	global $wp_rewrite;

	/*
	 * update_option() alone leaves the in-memory rewrite object empty, and
	 * flush_rewrite_rules() then saves an empty ruleset. set_permalink_structure()
	 * updates both, before post types add their archives.
	 */
	if ( ! get_option( 'permalink_structure' ) ) {
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
	}

	if ( ! post_type_exists( 'rubezh_hero' ) || ! post_type_exists( 'rubezh_initiative' ) ) {
		rubezh_register_content_types();
	}

	flush_rewrite_rules();
	rubezh_seed();
}

/**
 * Create the starter newsroom once.
 */
function rubezh_seed() {
	if ( get_option( 'rubezh_seeded' ) ) {
		return;
	}

	$taglines = array( '', 'Just another WordPress site', 'Ещё один сайт на WordPress' );
	if ( in_array( (string) get_option( 'blogdescription' ), $taglines, true ) ) {
		update_option( 'blogdescription', 'Журнал антитеррора: память, хроника и фонд инициатив' );
	}
	if ( in_array( (string) get_option( 'blogname' ), array( '', 'WordPress', 'Мой сайт' ), true ) ) {
		update_option( 'blogname', 'Рубеж' );
	}

	rubezh_retire_hello_world();

	$chronicle = rubezh_ensure_term( 'Хроника', 'category' );
	$memory    = rubezh_ensure_term( 'Память', 'category' );
	$fund      = rubezh_ensure_term( 'Фонд', 'category' );
	$safety    = rubezh_ensure_term( 'Безопасность', 'category' );

	$education = rubezh_ensure_term( 'Просвещение', 'rubezh_track' );
	$remembrance = rubezh_ensure_term( 'Память', 'rubezh_track' );
	$care      = rubezh_ensure_term( 'Забота', 'rubezh_track' );
	$law       = rubezh_ensure_term( 'Право', 'rubezh_track' );

	rubezh_ensure_entry(
		array(
			'post_type'    => 'post',
			'post_name'    => 'tihij-ton',
			'post_title'   => 'Тихий тон сильнее сирены',
			'post_excerpt' => 'Как журнал говорит об антитерроре, не превращая чужую беду в зрелище.',
			'post_content' => rubezh_paragraphs(
				array(
					'Журнал антитеррора легко спутать с лентой тревоги. Мы выбираем другой ритм: сначала факт, потом контекст, и только затем вывод.',
					'Громкий заголовок не делает город безопаснее. Он делает читателя беспомощным. Поэтому в «Рубеже» нет кадров, которые можно разобрать как инструкцию, и нет чужой боли как оформления.',
					'Редакция публикует хронику, правовые ориентиры и истории памяти. Если материал нельзя проверить, ему не место на полосе.',
				),
				'Сенсация заканчивается на клике. Память остаётся с людьми.'
			),
		),
		array( '_rubezh_sample' => '1' ),
		array( 'category' => array( $chronicle ) )
	);

	rubezh_ensure_entry(
		array(
			'post_type'    => 'post',
			'post_name'    => 'pamyat-ne-vitrina',
			'post_title'   => 'Память — не витрина',
			'post_excerpt' => 'Имя нельзя публиковать только потому, что оно усиливает материал.',
			'post_content' => rubezh_paragraphs(
				array(
					'Имя человека нельзя публиковать только потому, что оно усиливает текст. Нужны согласие близких или открытый документальный источник, точные даты и отказ от домысла.',
					'В шаблоне стоят профили-образцы. Они показывают вёрстку раздела и помечены. Перед публичным запуском их заменяют подтверждёнными историями или снимают с публикации.',
					'Рядом с именем мы держим роль, место и короткую формулу жизни — без натуралистических подробностей.',
				)
			),
		),
		array( '_rubezh_sample' => '1' ),
		array( 'category' => array( $memory ) )
	);

	rubezh_ensure_entry(
		array(
			'post_type'    => 'post',
			'post_name'    => 'kak-ustroen-fond',
			'post_title'   => 'Как устроен фонд инициатив',
			'post_excerpt' => 'Голос на сайте — публичная поддержка, а не юридический референдум.',
			'post_content' => rubezh_paragraphs(
				array(
					'Голос на сайте — это публичная поддержка, а не юридический референдум. Один браузер или один аккаунт может поддержать инициативу один раз.',
					'Статус «принята» выставляет редакция, когда идея переходит в работу. Счётчик лишь показывает, сколько людей отозвалось.',
					'Инициатива должна быть конкретной, законной и полезной людям. Призывы к насилию и любые практические описания опасных действий не публикуются.',
				)
			),
		),
		array( '_rubezh_sample' => '1' ),
		array( 'category' => array( $fund ) )
	);

	rubezh_ensure_entry(
		array(
			'post_type'    => 'post',
			'post_name'    => 'esli-ryadom-opasnost',
			'post_title'   => 'Если рядом опасность',
			'post_excerpt' => 'Сначала безопасность людей. Потом текст.',
			'post_content' => rubezh_paragraphs(
				array(
					'Если есть угроза жизни или здоровью, звоните 112. Отойдите в безопасное место. Не вступайте в контакт и не пытайтесь проверить подозрение самостоятельно.',
					'Не распространяйте непроверенные детали: чужие имена, перемещения, устройство места. Слух мешает тем, кто реально помогает.',
					'В редакцию можно писать о темах номера и общественных инициативах. Оперативные сообщения редакция не принимает и никуда не пересылает как экстренная служба.',
				),
				'Сначала безопасность людей. Потом текст.'
			),
		),
		array( '_rubezh_sample' => '1' ),
		array( 'category' => array( $safety ) )
	);

	rubezh_ensure_entry(
		array(
			'post_type'    => 'rubezh_hero',
			'post_name'    => 'mariya-lebedeva',
			'post_title'   => 'Мария Лебедева',
			'post_excerpt' => 'Демонстрационный профиль врача скорой помощи.',
			'menu_order'   => 1,
			'post_content' => rubezh_paragraphs(
				array(
					'Это демонстрационный профиль шаблона «Рубеж». Он нужен, чтобы показать масштаб имени, годы и короткую формулу жизни.',
					'Замените его документально подтверждённой историей или удалите до публикации. Не добавляйте натуралистических подробностей.',
				)
			),
		),
		array(
			'_rubezh_sample'  => '1',
			'_rubezh_role'    => 'Врач скорой помощи',
			'_rubezh_years'   => '1979—2024',
			'_rubezh_place'   => 'Образец, не документ',
			'_rubezh_epitaph' => 'Оставалась там, где людям ещё можно было помочь.',
		)
	);

	rubezh_ensure_entry(
		array(
			'post_type'    => 'rubezh_hero',
			'post_name'    => 'igor-savelev',
			'post_title'   => 'Игорь Савельев',
			'post_excerpt' => 'Демонстрационный профиль сотрудника транспортной безопасности.',
			'menu_order'   => 2,
			'post_content' => rubezh_paragraphs(
				array(
					'Образец показывает, как в разделе памяти читается роль и место службы. Это не биография реального человека.',
					'Когда появится подтверждённый профиль, снимите пометку «демонстрационный материал» в боковой панели редактора.',
				)
			),
		),
		array(
			'_rubezh_sample'  => '1',
			'_rubezh_role'    => 'Транспортная безопасность',
			'_rubezh_years'   => '1986—2025',
			'_rubezh_place'   => 'Образец, не документ',
			'_rubezh_epitaph' => 'Держал порядок там, где люди просто ехали домой.',
		)
	);

	rubezh_ensure_entry(
		array(
			'post_type'    => 'rubezh_hero',
			'post_name'    => 'nina-koltsova',
			'post_title'   => 'Нина Кольцова',
			'post_excerpt' => 'Демонстрационный профиль учительницы.',
			'menu_order'   => 3,
			'post_content' => rubezh_paragraphs(
				array(
					'Третий образец нужен, чтобы список памяти не выглядел одиночной карточкой. Имя вымышлено.',
					'В настоящем профиле достаточно спокойного текста: кем человек был и что о нём можно сказать точно.',
				)
			),
		),
		array(
			'_rubezh_sample'  => '1',
			'_rubezh_role'    => 'Учительница',
			'_rubezh_years'   => '1968—2023',
			'_rubezh_place'   => 'Образец, не документ',
			'_rubezh_epitaph' => 'Учила отличить слух от факта.',
		)
	);

	rubezh_ensure_entry(
		array(
			'post_type'    => 'rubezh_initiative',
			'post_name'    => 'urok-pamyati',
			'post_title'   => 'Урок памяти без сенсации',
			'post_excerpt' => 'Школьное занятие о достоинстве людей и о том, как не разносить непроверенное.',
			'menu_order'   => 1,
			'post_content' => rubezh_paragraphs(
				array(
					'Инициатива предлагает методическую рамку для педагогов: как говорить с подростками о последствиях терактов, не превращая урок в пересказ слухов.',
					'Голос поддерживает идею, но не утверждает бюджет и не обязывает школы. Это публичная поддержка, не решение органа власти.',
				)
			),
		),
		array(
			'_rubezh_sample' => '1',
			'_rubezh_status' => 'collecting',
			'_rubezh_goal'   => '100',
			'_rubezh_votes'  => '0',
		),
		array( 'rubezh_track' => array( $education ) )
	);

	rubezh_ensure_entry(
		array(
			'post_type'    => 'rubezh_initiative',
			'post_name'    => 'otkrytyj-arhiv',
			'post_title'   => 'Открытый архив имён',
			'post_excerpt' => 'Публичный реестр только тех имён, которые можно подтвердить документом или согласием семьи.',
			'menu_order'   => 2,
			'post_content' => rubezh_paragraphs(
				array(
					'Архив должен уметь сказать «нет»: если подтверждения нет, имени на сайте нет. Образец показывает статус «на рассмотрении».',
				)
			),
		),
		array(
			'_rubezh_sample' => '1',
			'_rubezh_status' => 'review',
			'_rubezh_goal'   => '80',
			'_rubezh_votes'  => '0',
		),
		array( 'rubezh_track' => array( $remembrance ) )
	);

	rubezh_ensure_entry(
		array(
			'post_type'    => 'rubezh_initiative',
			'post_name'    => 'tihaya-podderzhka',
			'post_title'   => 'Тихая поддержка семей',
			'post_excerpt' => 'Канал связи семей с проверенными службами психологической помощи — без публикации частных историй.',
			'menu_order'   => 3,
			'post_content' => rubezh_paragraphs(
				array(
					'Семье не нужна витрина. Ей нужен понятный способ дойти до помощи, которая уже существует и отвечает за свою работу.',
				)
			),
		),
		array(
			'_rubezh_sample' => '1',
			'_rubezh_status' => 'collecting',
			'_rubezh_goal'   => '60',
			'_rubezh_votes'  => '0',
		),
		array( 'rubezh_track' => array( $care ) )
	);

	rubezh_ensure_entry(
		array(
			'post_type'    => 'rubezh_initiative',
			'post_name'    => 'pravovoj-navigator',
			'post_title'   => 'Правовой навигатор',
			'post_excerpt' => 'Короткая памятка: куда звонить, что можно сообщить официально и чего не делать самостоятельно.',
			'menu_order'   => 4,
			'post_content' => rubezh_paragraphs(
				array(
					'Статус «принята» в образце показывает, как выглядит инициатива, которую редакция уже взяла в работу. Голосование для такого статуса закрыто.',
					'Памятка говорит только о законном порядке: экстренный номер, отказ от самодеятельности и отказ от распространения слухов.',
				)
			),
		),
		array(
			'_rubezh_sample' => '1',
			'_rubezh_status' => 'adopted',
			'_rubezh_goal'   => '40',
			'_rubezh_votes'  => '0',
		),
		array( 'rubezh_track' => array( $law ) )
	);

	rubezh_ensure_entry(
		array(
			'post_type'    => 'page',
			'post_name'    => 'zhurnal',
			'post_title'   => 'Журнал',
			'post_content' => '',
		),
		array( '_wp_page_template' => 'page-journal' )
	);

	rubezh_ensure_entry(
		array(
			'post_type'    => 'page',
			'post_name'    => 'o-zhurnale',
			'post_title'   => 'О журнале',
			'post_excerpt' => 'Три раздела: журнал, память героев и фонд инициатив.',
			'post_content' => rubezh_paragraphs(
				array(
					'«Рубеж» — редакционный шаблон о противодействии терроризму. В нём три раздела: журнал, память героев и фонд инициатив, где люди могут поддержать идею голосом.',
					'Сайт не заменяет экстренные службы. Если есть угроза жизни, звоните 112. Редакции можно предлагать темы номера и общественные инициативы — после того как в настройках темы указана почта.',
					'Профили с пометкой «Образец» вымышлены и стоят здесь, чтобы была видна вёрстка. Их заменяют документальными материалами до публикации.',
				)
			),
		)
	);

	rubezh_seed_menu();
	update_option( 'rubezh_seeded', RUBEZH_VERSION );
}

/**
 * Draft the default WordPress sample post so it does not lead the issue.
 */
function rubezh_retire_hello_world() {
	$posts = get_posts(
		array(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'numberposts' => 5,
		)
	);
	foreach ( $posts as $post ) {
		if ( in_array( $post->post_title, array( 'Hello world!', 'Привет, мир!' ), true ) ) {
			wp_update_post(
				array(
					'ID'          => $post->ID,
					'post_status' => 'draft',
				)
			);
		}
	}
}

/**
 * Term by name.
 *
 * @param string $name     Term name.
 * @param string $taxonomy Taxonomy.
 * @return int
 */
function rubezh_ensure_term( $name, $taxonomy ) {
	$existing = get_term_by( 'name', $name, $taxonomy );
	if ( $existing && ! is_wp_error( $existing ) ) {
		return (int) $existing->term_id;
	}
	$created = wp_insert_term( $name, $taxonomy );
	if ( is_wp_error( $created ) ) {
		return 0;
	}
	return (int) $created['term_id'];
}

/**
 * Insert a post once, matched by slug.
 *
 * @param array<string, mixed>              $args  Post args.
 * @param array<string, string>             $meta  Meta.
 * @param array<string, array<int, int>>    $terms Terms by taxonomy.
 * @return int
 */
function rubezh_ensure_entry( $args, $meta = array(), $terms = array() ) {
	$slug = isset( $args['post_name'] ) ? $args['post_name'] : sanitize_title( $args['post_title'] );
	$found = get_posts(
		array(
			'name'        => $slug,
			'post_type'   => $args['post_type'],
			'post_status' => 'any',
			'numberposts' => 1,
		)
	);
	if ( $found ) {
		return (int) $found[0]->ID;
	}
	$args['post_name']   = $slug;
	$args['post_status'] = 'publish';
	kses_remove_filters();
	$id = wp_insert_post( $args, true );
	kses_init_filters();
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, $key, $value );
	}
	foreach ( $terms as $taxonomy => $term_ids ) {
		$term_ids = array_filter( array_map( 'intval', $term_ids ) );
		if ( $term_ids ) {
			wp_set_object_terms( $id, $term_ids, $taxonomy );
		}
	}
	return (int) $id;
}

/**
 * Block content from plain paragraphs.
 *
 * @param string[] $paragraphs Paragraphs.
 * @param string   $quote      Optional pull quote.
 * @return string
 */
function rubezh_paragraphs( $paragraphs, $quote = '' ) {
	$html = '';
	foreach ( $paragraphs as $paragraph ) {
		$html .= "<!-- wp:paragraph -->\n<p>" . esc_html( $paragraph ) . "</p>\n<!-- /wp:paragraph -->\n\n";
	}
	if ( '' !== $quote ) {
		$html .= "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><p>" . esc_html( $quote ) . "</p></blockquote>\n<!-- /wp:quote -->\n";
	}
	return $html;
}

/**
 * Primary and footer menu.
 */
function rubezh_seed_menu() {
	$name = 'Рубеж';
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		$menu_id = (int) $menu->term_id;
	} else {
		$menu_id = (int) wp_create_nav_menu( $name );
	}
	if ( ! $menu_id ) {
		return;
	}
	$existing = wp_get_nav_menu_items( $menu_id );
	if ( empty( $existing ) ) {
		$position = 1;
		foreach ( rubezh_default_links() as $link ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'    => $link['label'],
					'menu-item-url'      => $link['url'],
					'menu-item-status'   => 'publish',
					'menu-item-type'     => 'custom',
					'menu-item-position' => $position,
				)
			);
			$position++;
		}
	}
	$locations            = get_theme_mod( 'nav_menu_locations' );
	$locations            = is_array( $locations ) ? $locations : array();
	$locations['primary'] = $menu_id;
	$locations['footer']  = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}
