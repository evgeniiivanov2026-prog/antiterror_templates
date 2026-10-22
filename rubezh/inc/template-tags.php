<?php
/**
 * Разметка и значения по умолчанию.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Значения настройщика.
 *
 * @return array<string, string>
 */
function rubezh_defaults() {
	return array(
		'kicker'              => 'Журнал общественной безопасности',
		'issue'               => '',
		'email'               => '',
		'legal'               => '',
		'vote_mode'           => 'public',
		'mission_title'       => 'Факты, память и общий голос',
		'mission_lede'        => 'Летопись фиксирует события и объясняет их без сенсаций. Рядом — память о людях, которые противостояли террору, и фонд инициатив, где можно поддержать гражданское предложение.',
		'mission_text'        => 'Порядок разделов, цвета и тексты меняются в настройщике. Публикуйте только то, что можно проверить.',
		'signal_title'        => 'Если угроза происходит сейчас',
		'signal_text'         => 'Не вступайте в конфликт и не проводите собственное расследование. Если опасность происходит сейчас, звоните 112. Редакции можно передать только то, что удаётся проверить.',
		'signal_note'         => 'Информация на сайте не заменяет обращение в экстренные службы.',
		'principle_1_title'   => 'Законность',
		'principle_1_text'    => 'Материалы и обращения не подменяют работу уполномоченных органов.',
		'principle_2_title'   => 'Достоинство',
		'principle_2_text'    => 'Имена и биографии публикуются бережно и только после проверки.',
		'principle_3_title'   => 'Участие',
		'principle_3_text'    => 'Голос за инициативу — это открытая поддержка, а не официальное решение.',
		'section_order'       => 'journal,memorial,initiatives,principles,signal',
	);
}

/**
 * Разделы главной.
 *
 * @return array<string, string>
 */
function rubezh_section_catalog() {
	return array(
		'journal'      => __( 'Летопись', 'rubezh' ),
		'memorial'     => __( 'Память', 'rubezh' ),
		'initiatives'  => __( 'Инициативы', 'rubezh' ),
		'principles'   => __( 'Принципы', 'rubezh' ),
		'signal'       => __( 'Сигнал', 'rubezh' ),
	);
}

/**
 * Настройка темы с запасным значением.
 *
 * @param string $key Ключ без префикса rubezh_.
 * @return string
 */
function rubezh_mod( $key ) {
	$defaults = rubezh_defaults();
	$default  = $defaults[ $key ] ?? '';
	$value    = get_theme_mod( 'rubezh_' . $key, $default );
	if ( is_string( $value ) ) {
		return $value;
	}
	return (string) $default;
}

/**
 * Оставляет только известные разделы и дополняет пропущенные.
 *
 * @param string $value Список через запятую.
 * @return string
 */
function rubezh_sanitize_order( $value ) {
	$allowed = array_keys( rubezh_section_catalog() );
	$incoming = array_filter( array_map( 'sanitize_key', explode( ',', (string) $value ) ) );
	$ordered  = array();

	foreach ( $incoming as $key ) {
		if ( in_array( $key, $allowed, true ) && ! in_array( $key, $ordered, true ) ) {
			$ordered[] = $key;
		}
	}

	foreach ( $allowed as $key ) {
		if ( ! in_array( $key, $ordered, true ) ) {
			$ordered[] = $key;
		}
	}

	return implode( ',', $ordered );
}

/**
 * Раздел включён в настройщике.
 *
 * @param string $key Ключ раздела.
 * @return bool
 */
function rubezh_section_enabled( $key ) {
	return (bool) get_theme_mod( 'rubezh_show_' . $key, true );
}

/**
 * Разделы главной в заданном порядке.
 *
 * @return string[]
 */
function rubezh_active_sections() {
	$order    = explode( ',', rubezh_sanitize_order( rubezh_mod( 'section_order' ) ) );
	$sections = array_values( array_filter( $order, 'rubezh_section_enabled' ) );
	return apply_filters( 'rubezh_active_sections', $sections );
}

/**
 * Подпись выпуска: дата, номер, рубрика издания.
 *
 * @return string
 */
function rubezh_dateline() {
	$parts = array( wp_date( 'j F Y' ) );
	$issue = trim( rubezh_mod( 'issue' ) );
	if ( '' !== $issue ) {
		$parts[] = $issue;
	}
	$parts[] = rubezh_mod( 'kicker' );
	return implode( '  ·  ', $parts );
}

/**
 * Слоган, если в WordPress оставлен служебный текст.
 *
 * @return string
 */
function rubezh_tagline() {
	$tagline  = trim( (string) get_bloginfo( 'description', 'display' ) );
	$defaults = array(
		'',
		'Just another WordPress site',
		'Ещё один сайт на WordPress',
		'Просто ещё один сайт на WordPress',
	);
	if ( in_array( $tagline, $defaults, true ) ) {
		return rubezh_mod( 'kicker' );
	}
	return $tagline;
}

/**
 * Адрес полной ленты записей.
 *
 * @return string
 */
function rubezh_journal_url() {
	$page = (int) get_option( 'page_for_posts' );
	if ( $page ) {
		return (string) get_permalink( $page );
	}
	return home_url( '/' );
}

/**
 * @param string $text  Строка.
 * @param int    $start Начало.
 * @param int    $length Длина.
 * @return string
 */
function rubezh_substr( $text, $start, $length ) {
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $text, $start, $length );
	}
	return substr( $text, $start, $length );
}

/**
 * @param string $text Строка.
 * @return string
 */
function rubezh_strtoupper( $text ) {
	if ( function_exists( 'mb_strtoupper' ) ) {
		return mb_strtoupper( $text );
	}
	return strtoupper( $text );
}

/**
 * Монограмма карточки. У образцов — подпись, а не инициалы.
 *
 * @param int $post_id Запись.
 * @return string
 */
function rubezh_monogram( $post_id ) {
	if ( get_post_meta( $post_id, '_rubezh_sample', true ) ) {
		return 'Образец';
	}
	$title = trim( wp_strip_all_tags( get_the_title( $post_id ) ) );
	$parts = preg_split( '/\s+/u', $title );
	$letters = '';
	if ( is_array( $parts ) ) {
		foreach ( array_slice( $parts, 0, 2 ) as $part ) {
			$letters .= rubezh_strtoupper( rubezh_substr( $part, 0, 1 ) );
		}
	}
	return '' !== $letters ? $letters : '·';
}

/**
 * Короткий текст карточки.
 *
 * @param int $post_id Запись.
 * @param int $words   Число слов.
 * @return string
 */
function rubezh_excerpt( $post_id, $words = 28 ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return '';
	}
	$text = $post->post_excerpt ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	return wp_trim_words( $text, $words, '…' );
}

/**
 * Оценка времени чтения.
 *
 * @param int|null $post_id Запись.
 * @return string
 */
function rubezh_reading_time( $post_id = null ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return '';
	}
	$text  = trim( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
	$parts = preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY );
	$count = is_array( $parts ) ? count( $parts ) : 0;
	$minutes = max( 1, (int) round( $count / 180 ) );
	return sprintf(
		/* translators: %d: minutes */
		__( '%d мин', 'rubezh' ),
		$minutes
	);
}

/**
 * Русская форма слова по числу.
 *
 * @param int      $number Число.
 * @param string[] $forms  Три формы: один, два, пять.
 * @return string
 */
function rubezh_ru_plural( $number, $forms ) {
	$number = abs( (int) $number ) % 100;
	$tail   = $number % 10;
	if ( $number > 10 && $number < 20 ) {
		return $forms[2];
	}
	if ( 1 === $tail ) {
		return $forms[0];
	}
	if ( $tail > 1 && $tail < 5 ) {
		return $forms[1];
	}
	return $forms[2];
}

/**
 * Подпись счётчика голосов.
 *
 * @param int $count Число.
 * @return string
 */
function rubezh_votes_label( $count ) {
	$count = absint( $count );
	$forms = apply_filters(
		'rubezh_vote_forms',
		array(
			__( 'голос', 'rubezh' ),
			__( 'голоса', 'rubezh' ),
			__( 'голосов', 'rubezh' ),
		)
	);
	return sprintf( '%d %s', $count, rubezh_ru_plural( $count, $forms ) );
}

/**
 * Статусы инициативы.
 *
 * @return array<string, string>
 */
function rubezh_status_labels() {
	return array(
		'open'      => __( 'Приём голосов', 'rubezh' ),
		'review'    => __( 'На рассмотрении', 'rubezh' ),
		'supported' => __( 'Поддержана', 'rubezh' ),
		'archived'  => __( 'В архиве', 'rubezh' ),
	);
}

/**
 * @param int $post_id Инициатива.
 * @return string
 */
function rubezh_initiative_status( $post_id ) {
	$status = (string) get_post_meta( $post_id, '_rubezh_status', true );
	$labels = rubezh_status_labels();
	return isset( $labels[ $status ] ) ? $status : 'open';
}

/**
 * @param string $status Ключ статуса.
 * @return string
 */
function rubezh_status_label( $status ) {
	$labels = rubezh_status_labels();
	return $labels[ $status ] ?? $labels['open'];
}

/**
 * Есть ли опубликованные демонстрационные записи.
 *
 * @return bool
 */
function rubezh_has_samples() {
	static $has = null;
	if ( null !== $has ) {
		return $has;
	}
	$query = new WP_Query(
		array(
			'post_type'              => array( 'post', 'hero', 'initiative' ),
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'meta_key'               => '_rubezh_sample',
			'meta_value'             => '1',
		)
	);
	$has = ! empty( $query->posts );
	return $has;
}

/**
 * Пункты меню, если область не назначена.
 *
 * @return array<int, array{url: string, label: string, current: bool}>
 */
function rubezh_nav_items() {
	$journal = rubezh_journal_url();
	$memory  = get_post_type_archive_link( 'hero' );
	$fund    = get_post_type_archive_link( 'initiative' );
	$items   = array(
		array(
			'url'     => home_url( '/' ),
			'label'   => __( 'Главная', 'rubezh' ),
			'current' => is_front_page(),
		),
		array(
			'url'     => $journal,
			'label'   => __( 'Летопись', 'rubezh' ),
			'current' => is_home() && ! is_front_page(),
		),
		array(
			'url'     => $memory ? $memory : home_url( '/pamyat/' ),
			'label'   => __( 'Память', 'rubezh' ),
			'current' => is_post_type_archive( 'hero' ) || is_singular( 'hero' ),
		),
		array(
			'url'     => $fund ? $fund : home_url( '/iniciativy/' ),
			'label'   => __( 'Инициативы', 'rubezh' ),
			'current' => is_post_type_archive( 'initiative' ) || is_singular( 'initiative' ),
		),
		array(
			'url'     => home_url( '/#principi' ),
			'label'   => __( 'Принципы', 'rubezh' ),
			'current' => false,
		),
	);
	return $items;
}

/**
 * Запасное меню.
 */
function rubezh_primary_fallback() {
	rubezh_menu_fallback( 'rz-nav__list' );
}

/**
 * Запасное меню подвала.
 */
function rubezh_footer_fallback() {
	rubezh_menu_fallback( 'rz-footer__links' );
}

/**
 * @param string $class Класс списка.
 */
function rubezh_menu_fallback( $class ) {
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( rubezh_nav_items() as $item ) {
		echo '<li><a href="' . esc_url( $item['url'] ) . '"';
		if ( $item['current'] ) {
			echo ' aria-current="page"';
		}
		echo '>' . esc_html( $item['label'] ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Хлебные крошки.
 */
function rubezh_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	echo '<nav class="rz-crumbs" aria-label="' . esc_attr__( 'Путь по сайту', 'rubezh' ) . '"><ol>';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Главная', 'rubezh' ) . '</a></li>';

	if ( is_singular( 'post' ) ) {
		$categories = get_the_category();
		if ( $categories ) {
			echo '<li><a href="' . esc_url( get_category_link( $categories[0] ) ) . '">' . esc_html( $categories[0]->name ) . '</a></li>';
		}
		echo '<li aria-current="page">' . esc_html( get_the_title() ) . '</li>';
	} elseif ( is_singular( 'hero' ) ) {
		$archive = get_post_type_archive_link( 'hero' );
		echo '<li><a href="' . esc_url( $archive ? $archive : home_url( '/' ) ) . '">' . esc_html__( 'Память', 'rubezh' ) . '</a></li>';
		echo '<li aria-current="page">' . esc_html( get_the_title() ) . '</li>';
	} elseif ( is_singular( 'initiative' ) ) {
		$archive = get_post_type_archive_link( 'initiative' );
		echo '<li><a href="' . esc_url( $archive ? $archive : home_url( '/' ) ) . '">' . esc_html__( 'Инициативы', 'rubezh' ) . '</a></li>';
		echo '<li aria-current="page">' . esc_html( get_the_title() ) . '</li>';
	} elseif ( is_singular( 'page' ) ) {
		echo '<li aria-current="page">' . esc_html( get_the_title() ) . '</li>';
	} elseif ( is_category() || is_tag() || is_tax() || is_date() || is_author() ) {
		echo '<li aria-current="page">' . esc_html( wp_strip_all_tags( get_the_archive_title() ) ) . '</li>';
	} elseif ( is_search() ) {
		echo '<li aria-current="page">' . esc_html__( 'Поиск', 'rubezh' ) . '</li>';
	} elseif ( is_post_type_archive() ) {
		echo '<li aria-current="page">' . esc_html( post_type_archive_title( '', false ) ) . '</li>';
	}

	echo '</ol></nav>';
}

/**
 * Мета записи: дата, автор, время чтения.
 *
 * @param int|null $post_id Запись.
 */
function rubezh_entry_meta( $post_id = null ) {
	$post_id   = $post_id ? (int) $post_id : get_the_ID();
	$author_id = (int) get_post_field( 'post_author', $post_id );
	echo '<p class="rz-meta">';
	echo '<time datetime="' . esc_attr( get_the_date( 'c', $post_id ) ) . '">' . esc_html( get_the_date( 'j F Y', $post_id ) ) . '</time>';
	echo '<a href="' . esc_url( get_author_posts_url( $author_id ) ) . '">' . esc_html( get_the_author_meta( 'display_name', $author_id ) ) . '</a>';
	echo '<span>' . esc_html( rubezh_reading_time( $post_id ) ) . '</span>';
	echo '</p>';
}

/**
 * Рубрики записи строкой.
 *
 * @param int|null $post_id Запись.
 */
function rubezh_category_label( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$type    = get_post_type( $post_id );
	if ( 'post' !== $type ) {
		$object = get_post_type_object( $type );
		echo esc_html( $object ? $object->labels->singular_name : '' );
		return;
	}
	$categories = get_the_category( $post_id );
	if ( ! $categories ) {
		esc_html_e( 'Летопись', 'rubezh' );
		return;
	}
	echo esc_html( $categories[0]->name );
}

/**
 * Заголовок раздела.
 *
 * @param string $number Номер.
 * @param string $label  Короткая подпись.
 * @param string $title  Заголовок.
 * @param string $id     id заголовка.
 * @param string $deck   Пояснение.
 */
function rubezh_section_heading( $number, $label, $title, $id, $deck = '' ) {
	echo '<header class="rz-section-head">';
	echo '<div>';
	echo '<p class="rz-index"><span>' . esc_html( $number ) . '</span> ' . esc_html( $label ) . '</p>';
	echo '<h2 id="' . esc_attr( $id ) . '">' . esc_html( $title ) . '</h2>';
	if ( '' !== $deck ) {
		echo '<p class="rz-deck">' . esc_html( $deck ) . '</p>';
	}
	echo '</div>';
	echo '</header>';
}

/**
 * Список инициатив с общей шкалой поддержки.
 *
 * @param WP_Query $query Выборка.
 */
function rubezh_render_ballots( $query ) {
	$posts = $query->posts;
	if ( ! $posts ) {
		return;
	}
	$counts = array();
	foreach ( $posts as $post ) {
		$counts[ $post->ID ] = rubezh_vote_count( $post->ID );
	}
	$max   = max( 1, max( $counts ) );
	$voted = rubezh_voted_map( array_keys( $counts ) );

	echo '<div class="rz-vote-list" data-vote-list>';
	foreach ( $posts as $post ) {
		get_template_part(
			'template-parts/content/ballot',
			null,
			array(
				'post_id' => $post->ID,
				'max'     => $max,
				'voted'   => ! empty( $voted[ $post->ID ] ),
			)
		);
	}
	echo '</div>';
}

/**
 * Пагинация журнала. Кнопка подгрузки дополняет обычные ссылки.
 *
 * @param bool $load_more Показывать подгрузку.
 */
function rubezh_journal_pager( $load_more = false ) {
	global $wp_query;
	if ( $wp_query->max_num_pages < 2 ) {
		return;
	}
	$current = max( 1, (int) get_query_var( 'paged' ) );
	echo '<nav class="rz-pager" data-pager aria-label="' . esc_attr__( 'Страницы летописи', 'rubezh' ) . '">';
	echo wp_kses_post(
		(string) paginate_links(
			array(
				'total'     => (int) $wp_query->max_num_pages,
				'current'   => $current,
				'prev_text' => __( 'Назад', 'rubezh' ),
				'next_text' => __( 'Дальше', 'rubezh' ),
				'type'      => 'plain',
			)
		)
	);
	echo '</nav>';

	if ( $load_more && $current < (int) $wp_query->max_num_pages ) {
		$cat = is_category() ? (int) get_queried_object_id() : 0;
		echo '<button type="button" class="rz-more" data-load-more hidden data-page="' . esc_attr( (string) ( $current + 1 ) ) . '" data-cat="' . esc_attr( (string) $cat ) . '">' . esc_html__( 'Показать ещё', 'rubezh' ) . '</button>';
	}
}

/**
 * Светлый ли цвет для контраста подписи на кнопке.
 *
 * @param string $hex HEX.
 * @return bool
 */
function rubezh_hex_is_light( $hex ) {
	$hex = ltrim( $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) ) {
		return false;
	}
	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );
	$luminance = ( 0.2126 * $r + 0.7152 * $g + 0.0722 * $b ) / 255;
	return $luminance > 0.62;
}
