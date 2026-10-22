<?php
/**
 * Fresh-site demonstration issue.
 *
 * Samples are labeled, closed to indexing, and can be deleted from the dashboard.
 * A site that already has its own posts is left alone.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_switch_theme', 'atj_on_switch' );
/**
 * Install, flush, and seed when the site still looks new.
 */
function atj_on_switch() {
	atj_register_content_types();
	atj_install_votes_table();
	atj_maybe_seed();
	flush_rewrite_rules();
}

/**
 * Seed only a nearly empty site, and only once.
 */
function atj_maybe_seed() {
	if ( get_option( 'atj_seeded' ) ) {
		return;
	}

	$published = wp_count_posts( 'post' );
	$count     = isset( $published->publish ) ? (int) $published->publish : 0;
	$heroes    = wp_count_posts( 'atj_hero' );
	$hero_count = isset( $heroes->publish ) ? (int) $heroes->publish : 0;

	if ( $count > 1 || $hero_count > 0 ) {
		update_option( 'atj_seeded', 'skipped' );
		return;
	}

	atj_seed_demo();
	update_option( 'atj_seeded', '1' );
}

/**
 * Build the demonstration issue.
 */
function atj_seed_demo() {
	atj_enable_pretty_permalinks();
	atj_retire_default_content();
	atj_maybe_set_identity();

	$categories = array(
		'profilaktika' => atj_ensure_category( 'Профилактика', 'profilaktika' ),
		'pamyat'       => atj_ensure_category( 'Память', 'pamyat-zhurnal' ),
		'obshchestvo'  => atj_ensure_category( 'Общество', 'obshchestvo' ),
		'pravo'        => atj_ensure_category( 'Право', 'pravo' ),
	);

	$areas = array(
		'prosveshchenie' => atj_ensure_area( 'Просвещение', 'prosveshchenie' ),
		'pamyat'         => atj_ensure_area( 'Память', 'pamyat-iniciativ' ),
		'semi'           => atj_ensure_area( 'Поддержка семей', 'podderzhka-semey' ),
		'gorod'          => atj_ensure_area( 'Городская среда', 'gorodskaya-sreda' ),
	);

	$posts = array(
		array(
			'title'    => 'Спокойный тон важнее громкого заголовка',
			'category' => $categories['profilaktika'],
			'excerpt'  => 'Журнал об антитерроре легко сползает в крик. Здесь другая мерка: факт, понятный язык и уважение к людям.',
			'sticky'   => true,
			'body'     => array(
				'Журнал об антитерроре легко сползти в крик. Мы держим другую мерку: проверяемый факт, понятный язык и уважение к людям, о которых идёт речь.',
				'Громкая версия обычно не делает город безопаснее. Она множит слухи, ранит семьи и подменяет работу органов спектаклем.',
				'Поэтому в материалах нет кадров с места событий, догадок о виновных и подробностей, которые помогают причинить вред. Есть контекст, память и то, что общество может сделать без самосуда.',
				'Если факт не подтверждён, ему не место в выпуске. Сомнение в тексте обозначается прямо, а не маскируется под уверенность.',
			),
		),
		array(
			'title'    => 'Как в журнале появляется карточка памяти',
			'category' => $categories['pamyat'],
			'excerpt'  => 'Раздел «Память» — архив редакции, а не лента и не рейтинг.',
			'body'     => array(
				'Раздел «Память» — не лента и не рейтинг. Это архив, который ведёт редакция: имя, годы, роль и биография, которую можно подтвердить.',
				'Карточку не собирают из комментариев и не наполняют слухами. Если семья просит о чём-то не писать, редакция это соблюдает.',
				'Образцы в свежей установке темы существуют только затем, чтобы показать вёрстку. Перед публичным запуском их удаляют и заменяют реальными, согласованными историями.',
				'Порядок карточек задаётся в админке полем «Порядок» — так архив остаётся собранным, а не хронологией случайных публикаций.',
			),
		),
		array(
			'title'    => 'Голос читателя — это не референдум',
			'category' => $categories['obshchestvo'],
			'excerpt'  => 'Фонд инициатив фиксирует мнение аудитории журнала. Это не выборы и не решение органа власти.',
			'body'     => array(
				'Фонд инициатив устроен как открытая тетрадь журнала. Читатель поддерживает предложение или снимает голос. Это мнение аудитории, а не выборы и не решение органа власти.',
				'Один человек — один голос за инициативу. Без учётной записи голос держится на анонимной метке браузера и не привязан к имени. С учётной записью тот же голос сохраняется на устройствах, где вы вошли.',
				'Редакция публикует инициативы сама. Закрытое голосование остаётся в архиве: его видно, но пополнить уже нельзя.',
				'Числа на странице — это число голосов на сайте. К ним не добавляются охваты, поддержка партнёров и другие выдуманные величины.',
			),
		),
		array(
			'title'    => 'Что делать, если рядом опасность',
			'category' => $categories['pravo'],
			'excerpt'  => 'Угроза жизни — повод позвонить 112, а не писать подробности в комментарии.',
			'body'     => array(
				'Если есть угроза жизни и здоровью, звоните 112. Это работа экстренных служб, а не комментариев и не формы на сайте.',
				'Не подходите к подозрительному предмету и к людям, которые ведут себя опасно. Не пытайтесь выяснить что-либо самостоятельно и не публикуйте имена, фотографии, адреса и догадки.',
				'Не пересылайте непроверенные сообщения «чтобы предупредить всех»: так расходятся паника и вредные советы. В такой момент надёжнее официальный канал, а не цепочка пересылок.',
				'Журнал может объяснить правила заранее. Он не принимает оперативные сигналы и не ведёт расследований.',
			),
		),
		array(
			'title'    => 'Почему на сайте нет ящика для доносов',
			'category' => $categories['pravo'],
			'excerpt'  => 'Оперативные подробности нечего хранить в редакционной почте и тем более на публичной странице.',
			'body'     => array(
				'Нам писали бы подробности, которые нельзя хранить в обычной редакционной почте и тем более на публичной странице. Для этого существуют компетентные органы и номер 112.',
				'Сайт оставляет себе другую работу: объяснять, помнить и давать читателям спокойный способ поддержать общественную инициативу.',
				'Если вы хотите предложить тему материала или инициативу, напишите в редакцию обычным письмом — без оперативных деталей, без данных третьих лиц и без просьбы разобраться вместо служб.',
				'Адрес редакции задаётся в настройщике темы. Пока он пуст, кнопка письма не показывается: нерабочий ящик хуже отсутствующего.',
			),
		),
	);

	$sticky = array();
	foreach ( $posts as $post ) {
		$id = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $post['title'],
				'post_excerpt' => $post['excerpt'],
				'post_content' => atj_paragraphs( $post['body'] ),
				'post_category'=> array( (int) $post['category'] ),
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_atj_sample', '1' );
			if ( ! empty( $post['sticky'] ) ) {
				$sticky[] = (int) $id;
			}
		}
	}
	if ( $sticky ) {
		update_option( 'sticky_posts', $sticky );
	}

	$heroes = array(
		array(
			'title'   => 'Образец: карточка памяти',
			'role'    => 'Сотрудник службы, защищавшей людей',
			'years'   => 'гггг — гггг',
			'place'   => 'Город укажет редакция',
			'excerpt' => 'Замените образец подтверждённой биографией.',
			'order'   => 1,
			'body'    => array(
				'Это демонстрационная карточка. Она показывает, как устроен раздел памяти, и не описывает реального человека.',
				'В настоящей карточке — имя, годы и биография, которые можно подтвердить. Догадки, слухи и то, о чём семья просила молчать, сюда не входят.',
			),
		),
		array(
			'title'   => 'Образец: гражданский наставник',
			'role'    => 'Педагог или общественный наставник',
			'years'   => 'гггг — гггг',
			'place'   => 'Город укажет редакция',
			'excerpt' => 'Образец второй карточки для сетки архива.',
			'order'   => 2,
			'body'    => array(
				'Вторая демонстрационная карточка нужна только для ритма вёрстки. Удалите её вместе с остальными образцами перед публикацией.',
				'Портрет задаётся изображением записи. Если фотографии нет, на обложке остаётся монограмма.',
			),
		),
		array(
			'title'   => 'Образец: доброволец',
			'role'    => 'Участник мирной гражданской поддержки',
			'years'   => 'гггг — гггг',
			'place'   => 'Город укажет редакция',
			'excerpt' => 'Образец третьей карточки. Не является биографией.',
			'order'   => 3,
			'body'    => array(
				'Третья карточка замыкает демонстрационный ряд. Порядок на странице памяти совпадает с полем «Порядок».',
				'Комментарии к карточкам памяти отключены намеренно: архив не место для спора.',
			),
		),
	);

	foreach ( $heroes as $hero ) {
		$id = wp_insert_post(
			array(
				'post_type'    => 'atj_hero',
				'post_status'  => 'publish',
				'post_title'   => $hero['title'],
				'post_excerpt' => $hero['excerpt'],
				'post_content' => atj_paragraphs( $hero['body'] ),
				'menu_order'   => $hero['order'],
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_atj_sample', '1' );
			update_post_meta( $id, '_atj_role', $hero['role'] );
			update_post_meta( $id, '_atj_years', $hero['years'] );
			update_post_meta( $id, '_atj_place', $hero['place'] );
		}
	}

	$initiatives = array(
		array(
			'title'   => 'Школьные занятия о правилах безопасности',
			'area'    => $areas['prosveshchenie'],
			'excerpt' => 'Короткие занятия для подростков: как не оставаться один на один с давлением и к кому обратиться.',
			'body'    => array(
				'Инициатива предлагает просветительские занятия силами педагогов: как рассказать о давлении и вербовке спокойно, без страшных подробностей и без инструкций.',
				'На занятии говорят о простом: не оставаться с тревогой одному, обратиться к взрослому, которому доверяешь, и знать номер 112. Оперативных деталей на таких встречах нет.',
				'Это демонстрационная инициатива. Голоса, которые она наберёт в образце, можно снести вместе с записью.',
			),
		),
		array(
			'title'   => 'Архив памяти, который ведёт редакция',
			'area'    => $areas['pamyat'],
			'excerpt' => 'Собрать подтверждённые карточки в одном спокойном разделе и не превращать память в ленту.',
			'body'    => array(
				'Предложение о том, как вести раздел памяти: немного карточек, каждая проверена, порядок задаёт редакция, а не алгоритм.',
				'Публичная поддержка показывает, что читателям нужен такой архив. Она не заменяет согласие семей и проверку фактов.',
			),
		),
		array(
			'title'   => 'Поддержка семей погибших сотрудников',
			'area'    => $areas['semi'],
			'excerpt' => 'Общественная помощь семьям — адресно и без публичного разбора обстоятельств.',
			'body'    => array(
				'Инициатива описывает принцип, а не сбор денег на этой странице: помощь должна быть адресной, тихой и согласованной с теми, кому она адресована.',
				'Сайт не принимает платежи и не публикует обстоятельства гибели. Если редакционная коллегия возьмётся за тему, она сделает это отдельно и проверяемо.',
			),
		),
		array(
			'title'   => 'Открытые городские встречи',
			'area'    => $areas['gorod'],
			'excerpt' => 'Встречи педагогов и представителей органов о профилактике — без оперативных подробностей.',
			'body'    => array(
				'Речь о публичных разговорах в школах и библиотеках: куда звонить, как не распространять панику и почему самодеятельность опасна.',
				'Встреча не заменяет службу и не собирает сообщения о конкретных людях. Для угрозы по-прежнему есть номер 112.',
			),
		),
	);

	foreach ( $initiatives as $item ) {
		$id = wp_insert_post(
			array(
				'post_type'    => 'atj_initiative',
				'post_status'  => 'publish',
				'post_title'   => $item['title'],
				'post_excerpt' => $item['excerpt'],
				'post_content' => atj_paragraphs( $item['body'] ),
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_atj_sample', '1' );
			update_post_meta( $id, '_atj_status', 'open' );
			update_post_meta( $id, '_atj_vote_count', 0 );
			if ( $item['area'] ) {
				wp_set_object_terms( $id, array( (int) $item['area'] ), 'atj_area' );
			}
		}
	}

	$about = atj_ensure_page(
		'o-proekte',
		'О проекте',
		atj_paragraphs(
			array(
				'«Вестник антитеррора» соединяет три вещи: журнал, память и фонд инициатив. Журнал объясняет. Память хранит подтверждённые истории. Фонд даёт читателям открытый, но неофициальный способ поддержать общественное предложение.',
				'Тема сделана как гибкий шаблон. Главная собрана из секций, а любую другую страницу можно собрать блоками и шорткодами [atj_journal], [atj_heroes], [atj_initiatives] и [atj_vote id="…"].',
				'Сайт не принимает оперативные сообщения, не публикует чувствительные подробности и не подменяет экстренные службы. Если есть угроза, звоните 112.',
			)
		)
	);
	atj_ensure_page(
		'kontakty',
		'Контакты',
		atj_paragraphs(
			array(
				'Почта редакции задаётся в разделе «Внешний вид → Настроить → Вестник». Пока поле пустое, на сайте нет кнопки письма.',
				'Перед запуском добавьте политику конфиденциальности: голосование гостя оставляет в браузере служебную cookie atj_vid и не хранит IP-адрес.',
				'Юридические сведения организации, если они есть, публикуйте только настоящие. Тема намеренно не подставляет вымышленные реквизиты.',
			)
		)
	);

	if ( 'posts' === get_option( 'show_on_front' ) && ! get_option( 'page_on_front' ) ) {
		$front = atj_ensure_page( 'glavnaya', 'Главная', '<!-- Главная собирается шаблоном front-page.php. Добавьте блоки, если нужен дополнительный фрагмент. -->' );
		$blog  = atj_ensure_page( 'zhurnal', 'Журнал', '' );
		if ( $front && $blog ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $front );
			update_option( 'page_for_posts', $blog );
		}
	}

	if ( ! has_nav_menu( 'primary' ) ) {
		$menu_id = wp_create_nav_menu( 'Вестник' );
		if ( ! is_wp_error( $menu_id ) ) {
			$items = atj_nav_links();
			$contacts = get_page_by_path( 'kontakty' );
			if ( $contacts instanceof WP_Post ) {
				$items[] = array( 'Контакты', get_permalink( $contacts ) );
			}
			unset( $about );
			foreach ( $items as $item ) {
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
			$locations            = get_theme_mod( 'nav_menu_locations', array() );
			$locations['primary'] = (int) $menu_id;
			$locations['footer']  = (int) $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}
}

/**
 * Paragraph blocks.
 *
 * @param string[] $paragraphs Paragraphs.
 * @return string
 */
function atj_paragraphs( array $paragraphs ) {
	$html = '';
	foreach ( $paragraphs as $paragraph ) {
		$html .= "<!-- wp:paragraph -->\n<p>" . esc_html( $paragraph ) . "</p>\n<!-- /wp:paragraph -->\n\n";
	}
	return $html;
}

/**
 * Category, created once.
 *
 * @param string $name Name.
 * @param string $slug Slug.
 * @return int
 */
function atj_ensure_category( $name, $slug ) {
	$existing = term_exists( $slug, 'category' );
	if ( $existing ) {
		return (int) ( is_array( $existing ) ? $existing['term_id'] : $existing );
	}
	$created = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
	return is_wp_error( $created ) ? 0 : (int) $created['term_id'];
}

/**
 * Initiative area, created once.
 *
 * @param string $name Name.
 * @param string $slug Slug.
 * @return int
 */
function atj_ensure_area( $name, $slug ) {
	$existing = term_exists( $slug, 'atj_area' );
	if ( $existing ) {
		return (int) ( is_array( $existing ) ? $existing['term_id'] : $existing );
	}
	$created = wp_insert_term( $name, 'atj_area', array( 'slug' => $slug ) );
	return is_wp_error( $created ) ? 0 : (int) $created['term_id'];
}

/**
 * Page, created once.
 *
 * @param string $slug Slug.
 * @param string $title Title.
 * @param string $content Content.
 * @return int
 */
function atj_ensure_page( $slug, $title, $content ) {
	$existing = get_page_by_path( $slug );
	if ( $existing instanceof WP_Post ) {
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
 * Move the stock "Hello world" and sample page out of the issue.
 */
function atj_retire_default_content() {
	foreach ( array( 'hello-world', 'privet-mir' ) as $slug ) {
		$posts = get_posts(
			array(
				'name'        => $slug,
				'post_type'   => 'post',
				'post_status' => 'any',
				'numberposts' => 1,
			)
		);
		if ( $posts ) {
			wp_update_post(
				array(
					'ID'          => $posts[0]->ID,
					'post_status' => 'draft',
				)
			);
			unstick_post( $posts[0]->ID );
		}
	}

	foreach ( array( 'sample-page', 'primer-stranitsy' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			wp_update_post(
				array(
					'ID'          => $page->ID,
					'post_status' => 'draft',
				)
			);
		}
	}
}

/**
 * Fresh sites still on plain permalinks get readable addresses before menus are saved.
 */
function atj_enable_pretty_permalinks() {
	if ( get_option( 'permalink_structure' ) ) {
		return;
	}
	global $wp_rewrite;
	$wp_rewrite->set_permalink_structure( '/%postname%/' );
	$wp_rewrite->flush_rules( false );
}

/**
 * Give a stock installation a readable name. Leave a chosen name untouched.
 */
function atj_maybe_set_identity() {
	$generic_names = array( '', 'WordPress', 'My WordPress', 'My WordPress Website', 'Мой сайт', 'Just another WordPress site' );
	if ( in_array( (string) get_option( 'blogname' ), $generic_names, true ) ) {
		update_option( 'blogname', 'Вестник антитеррора' );
	}
	$generic_descriptions = array( '', 'Just another WordPress site', 'Ещё один сайт на WordPress', 'Еще один сайт на WordPress' );
	if ( in_array( (string) get_option( 'blogdescription' ), $generic_descriptions, true ) ) {
		update_option( 'blogdescription', 'Журнал, память и фонд инициатив' );
	}
}

/**
 * How many demonstration posts exist.
 *
 * @return int
 */
function atj_sample_count() {
	$query = new WP_Query(
		array(
			'post_type'      => array( 'post', 'atj_hero', 'atj_initiative' ),
			'post_status'    => 'any',
			'meta_key'       => '_atj_sample',
			'meta_value'     => '1',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	return (int) $query->found_posts;
}

add_action( 'admin_notices', 'atj_sample_notice' );
/**
 * Dashboard note while samples are published.
 */
function atj_sample_notice() {
	if ( ! current_user_can( 'delete_posts' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->base, array( 'dashboard', 'themes' ), true ) ) {
		return;
	}

	if ( isset( $_GET['atj_samples_deleted'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Образцы удалены. Страницы «О проекте» и «Контакты» сохранены.', 'atjournal' ) . '</p></div>';
		return;
	}

	$count = atj_sample_count();
	if ( ! $count ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=atj_delete_samples' ), 'atj_delete_samples' );
	echo '<div class="notice notice-info"><p>';
	echo esc_html(
		sprintf(
			/* translators: %d: number of sample posts */
			__( 'В теме опубликованы демонстрационные материалы: %d. Они помечены как образцы и закрыты от индексации.', 'atjournal' ),
			$count
		)
	);
	echo ' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Удалить образцы', 'atjournal' ) . '</a>';
	echo '</p></div>';
}

add_action( 'admin_post_atj_delete_samples', 'atj_delete_samples' );
/**
 * Delete demonstration posts, heroes, and initiatives.
 */
function atj_delete_samples() {
	if ( ! current_user_can( 'delete_posts' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'atjournal' ) );
	}
	check_admin_referer( 'atj_delete_samples' );

	$query = new WP_Query(
		array(
			'post_type'      => array( 'post', 'atj_hero', 'atj_initiative' ),
			'post_status'    => 'any',
			'meta_key'       => '_atj_sample',
			'meta_value'     => '1',
			'posts_per_page' => 100,
			'fields'         => 'ids',
		)
	);
	foreach ( $query->posts as $post_id ) {
		wp_delete_post( (int) $post_id, true );
	}

	wp_safe_redirect( admin_url( 'index.php?atj_samples_deleted=1' ) );
	exit;
}
