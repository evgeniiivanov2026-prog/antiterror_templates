<?php
/**
 * Seed respectful demo content once after activation.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_switch_theme', 'aegis_seed_demo' );
function aegis_seed_demo() {
	if ( get_option( 'aegis_demo_seeded' ) ) {
		return;
	}

	$journal = wp_insert_term( __( 'Аналитика', 'aegis' ), 'category' );
	$memory  = wp_insert_term( __( 'Память', 'aegis' ), 'category' );
	$law     = wp_insert_term( __( 'Право', 'aegis' ), 'category' );

	$cat_ids = array(
		'journal' => ! is_wp_error( $journal ) ? $journal['term_id'] : 1,
		'memory'  => ! is_wp_error( $memory ) ? $memory['term_id'] : 1,
		'law'     => ! is_wp_error( $law ) ? $law['term_id'] : 1,
	);

	$posts = array(
		array(
			'title'   => __( 'Как редакция антитеррора работает с источниками', 'aegis' ),
			'excerpt' => __( 'Протокол проверки фактов, анонимности свидетелей и границ публикации.', 'aegis' ),
			'cat'     => $cat_ids['journal'],
			'sticky'  => true,
			'body'    => aegis_demo_article_sources(),
		),
		array(
			'title'   => __( 'Школьная профилактика без паники и зрелищности', 'aegis' ),
			'excerpt' => __( 'Что работает в образовательных программах и чего стоит избегать.', 'aegis' ),
			'cat'     => $cat_ids['journal'],
			'body'    => aegis_demo_article_school(),
		),
		array(
			'title'   => __( 'Зал памяти: этика портрета и имя в публичном поле', 'aegis' ),
			'excerpt' => __( 'Как рассказывать о героях, не превращая гибель в контент.', 'aegis' ),
			'cat'     => $cat_ids['memory'],
			'body'    => aegis_demo_article_memory(),
		),
		array(
			'title'   => __( 'Гражданская инициатива и правовое поле', 'aegis' ),
			'excerpt' => __( 'Где заканчивается общественный контроль и начинается компетенция спецслужб.', 'aegis' ),
			'cat'     => $cat_ids['law'],
			'body'    => aegis_demo_article_law(),
		),
	);

	$sticky = array();
	foreach ( $posts as $item ) {
		$id = wp_insert_post( array(
			'post_title'   => $item['title'],
			'post_excerpt' => $item['excerpt'],
			'post_content' => $item['body'],
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_category'=> array( $item['cat'] ),
		) );
		if ( ! is_wp_error( $id ) && ! empty( $item['sticky'] ) ) {
			$sticky[] = $id;
		}
	}
	if ( $sticky ) {
		update_option( 'sticky_posts', $sticky );
	}

	$services = array(
		__( 'Спецподразделения', 'aegis' ),
		__( 'Спасатели', 'aegis' ),
		__( 'Медицина', 'aegis' ),
	);
	foreach ( $services as $name ) {
		wp_insert_term( $name, 'aegis_service' );
	}

	$heroes = array(
		array(
			'title'   => __( 'Сотрудник спецподразделения', 'aegis' ),
			'years'   => '1978–2004',
			'service' => __( 'Антитеррористическое подразделение', 'aegis' ),
			'award'   => __( 'Орден Мужества', 'aegis' ),
			'date'    => '2004',
			'excerpt' => __( 'Демонстрационная карточка шаблона. Замените на проверенные редакционные материалы.', 'aegis' ),
			'body'    => __( '<p>Это демонстрационная карточка памяти. В рабочей редакции публикуйте только согласованные с семьями и проверяемые сведения: имя, годы, служба, краткое описание подвига без натурализма.</p><p>Aegis намеренно не выдумывает биографии реальных людей. Карточка показывает композицию зала памяти: портрет, служба, награда, сдержанный текст.</p>', 'aegis' ),
		),
		array(
			'title'   => __( 'Спасатель поискового отряда', 'aegis' ),
			'years'   => '1985–2010',
			'service' => __( 'Аварийно-спасательная служба', 'aegis' ),
			'award'   => __( 'Медаль «За отвагу»', 'aegis' ),
			'date'    => '2010',
			'excerpt' => __( 'Демонстрационная карточка. Память спасателей, работавших на месте теракта.', 'aegis' ),
			'body'    => __( '<p>Демонстрационный текст. Расскажите о человеке через службу и поступок, а не через сцену насилия. Избегайте графических деталей и слухов.</p>', 'aegis' ),
		),
		array(
			'title'   => __( 'Врач скорой помощи', 'aegis' ),
			'years'   => '1969–2002',
			'service' => __( 'Медицина катастроф', 'aegis' ),
			'award'   => __( 'Знак отличия', 'aegis' ),
			'date'    => '2002',
			'excerpt' => __( 'Демонстрационная карточка. Медики, которые оставались с пострадавшими.', 'aegis' ),
			'body'    => __( '<p>Демонстрационный текст. Карточка памяти может говорить о гражданском подвиге так же ясно, как о боевом.</p>', 'aegis' ),
		),
		array(
			'title'   => __( 'Кинолог', 'aegis' ),
			'years'   => '1982–2011',
			'service' => __( 'Кинологическая служба', 'aegis' ),
			'award'   => __( 'Ведомственная награда', 'aegis' ),
			'date'    => '2011',
			'excerpt' => __( 'Демонстрационная карточка. Расчёт и собака на линии поиска.', 'aegis' ),
			'body'    => __( '<p>Демонстрационный текст. Зал памяти Aegis рассчитан на сдержанный портретный ритм, а не на ленту сенсаций.</p>', 'aegis' ),
		),
	);

	foreach ( $heroes as $hero ) {
		$id = wp_insert_post( array(
			'post_type'    => 'aegis_hero',
			'post_status'  => 'publish',
			'post_title'   => $hero['title'],
			'post_excerpt' => $hero['excerpt'],
			'post_content' => $hero['body'],
		) );
		if ( is_wp_error( $id ) ) {
			continue;
		}
		update_post_meta( $id, '_aegis_years', $hero['years'] );
		update_post_meta( $id, '_aegis_service', $hero['service'] );
		update_post_meta( $id, '_aegis_award', $hero['award'] );
		update_post_meta( $id, '_aegis_feat_date', $hero['date'] );
	}

	$topics = array(
		__( 'Профилактика', 'aegis' ),
		__( 'Память', 'aegis' ),
		__( 'Помощь семьям', 'aegis' ),
		__( 'Образование', 'aegis' ),
	);
	foreach ( $topics as $name ) {
		wp_insert_term( $name, 'aegis_topic' );
	}

	$initiatives = array(
		array(
			'title'  => __( 'Аллея памяти у городской школы', 'aegis' ),
			'author' => __( 'Инициативная группа педагогов', 'aegis' ),
			'votes'  => 128,
			'status' => 'open',
			'topic'  => __( 'Память', 'aegis' ),
			'body'   => __( '<p>Предложение создать сдержанное мемориальное пространство у школы: имена, годы, лаконичная стела, образовательный маршрут без зрелищности.</p><p>Голос здесь — не лайк, а гражданская поддержка приоритета фонда.</p>', 'aegis' ),
		),
		array(
			'title'  => __( 'Цифровой курс для классных руководителей', 'aegis' ),
			'author' => __( 'Методическое объединение', 'aegis' ),
			'votes'  => 96,
			'status' => 'open',
			'topic'  => __( 'Образование', 'aegis' ),
			'body'   => __( '<p>Короткий модуль: как говорить с подростками о радикализации, не пугая и не романтизируя насилие. Материалы проверяет редакция и профильные специалисты.</p>', 'aegis' ),
		),
		array(
			'title'  => __( 'Фонд поддержки семей погибших', 'aegis' ),
			'author' => __( 'Попечительский совет', 'aegis' ),
			'votes'  => 211,
			'status' => 'review',
			'topic'  => __( 'Помощь семьям', 'aegis' ),
			'body'   => __( '<p>Прозрачный контур адресной помощи: юридическое сопровождение, психологическая поддержка, образовательные гранты детям. Публичный отчёт раз в квартал.</p>', 'aegis' ),
		),
		array(
			'title'  => __( 'Городская линия профилактики в транспорте', 'aegis' ),
			'author' => __( 'Жители района', 'aegis' ),
			'votes'  => 54,
			'status' => 'open',
			'topic'  => __( 'Профилактика', 'aegis' ),
			'body'   => __( '<p>Информационные носители и обучение персонала без панических слоганов. Акцент на наблюдательности и порядке обращения в экстренные службы.</p>', 'aegis' ),
		),
	);

	foreach ( $initiatives as $item ) {
		$id = wp_insert_post( array(
			'post_type'    => 'aegis_initiative',
			'post_status'  => 'publish',
			'post_title'   => $item['title'],
			'post_content' => $item['body'],
			'post_excerpt' => wp_trim_words( wp_strip_all_tags( $item['body'] ), 22 ),
		) );
		if ( is_wp_error( $id ) ) {
			continue;
		}
		update_post_meta( $id, '_aegis_author_name', $item['author'] );
		update_post_meta( $id, '_aegis_status', $item['status'] );
		update_post_meta( $id, '_aegis_votes', $item['votes'] );
		wp_set_object_terms( $id, $item['topic'], 'aegis_topic' );
	}

	flush_rewrite_rules();
	update_option( 'aegis_demo_seeded', 1 );
}

function aegis_demo_article_sources() {
	return __( '<p>Журнальная линия Aegis наследует редакционную плотность JournalX: крупный вход, мета, связанное чтение. Гражданская интонация — от институциональной темы Фонда Шевякова: факты, сдержанность, ответственность.</p><h2>Что публикуем</h2><p>Только проверяемые сведения. Анонимные источники помечаются, чувствительные детали снимаются. Сенсация не является форматом выпуска.</p><h2>Что не публикуем</h2><p>Инструкции, схемы, персональные данные свидетелей и изображения, которые унижают пострадавших. В экстренной ситуации читатель направляется в 112, а не в комментарии.</p>', 'aegis' );
}

function aegis_demo_article_school() {
	return __( '<p>Профилактика в школе работает, когда она тихая и регулярная: речь педагога, понятный канал обращения, отсутствие зрелищных реконструкций.</p><p>Шаблон даёт журналу рубрики, а фонду — инициативы, которые школа может выдвинуть на голосование: курс для классных руководителей, родительские встречи, мемориальный маршрут.</p>', 'aegis' );
}

function aegis_demo_article_memory() {
	return __( '<p>Зал памяти в Aegis отделён от ленты новостей. Портрет, имя, служба, годы. Текст короткий. Никакого счётчика «популярности» на карточке героя.</p><p>Демонстрационные карточки нужно заменить на согласованные материалы редакции. Не публикуйте вымышленные биографии реальных людей.</p>', 'aegis' );
}

function aegis_demo_article_law() {
	return __( '<p>Фонд инициатив не подменяет следствие и не собирает оперативную информацию в открытом голосовании. Гражданский голос расставляет приоритеты профилактики, памяти и помощи семьям.</p><p>Если человек знает об угрозе — он пишет фонду конфиденциально или звонит 112. Это разделение намеренно проведено в интерфейсе.</p>', 'aegis' );
}
