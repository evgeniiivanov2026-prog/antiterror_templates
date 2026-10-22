<?php
/**
 * Шаблонные метки и общие помощники.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Значения по умолчанию. Их же читает кастомайзер.
 *
 * @return array<string, mixed>
 */
function strazha_defaults() {
	return array(
		'kicker'                => 'Антитеррор · общественный журнал',
		'hero_title'            => 'Смотреть ясно.',
		'hero_emphasis'         => 'Помнить точно.',
		'hero_lead'             => 'Редакционный журнал о противодействии терроризму: хроника, стена памяти героев и открытый фонд гражданских инициатив.',
		'email'                 => '',
		'cover_post'            => 0,
		'show_mission'          => true,
		'show_journal'          => true,
		'show_memory'           => true,
		'show_fund'             => true,
		'show_help'             => true,
		'mission_title'         => 'Зачем этот журнал',
		'mission_text'          => 'Стража собирает проверенные материалы, бережно хранит имена и даёт людям спокойный способ поддержать гражданскую инициативу. Без сенсаций и без призывов действовать самостоятельно.',
		'principle_1_title'     => 'Точность',
		'principle_1_text'      => 'Отделяем хронику от комментария и не публикуем непроверенные версии.',
		'principle_2_title'     => 'Память',
		'principle_2_text'      => 'Имя и биография важнее сводки. Мемориал ведётся спокойно, без спектакля.',
		'principle_3_title'     => 'Закон',
		'principle_3_text'      => 'Только правовое поле. При угрозе — 112, без самосуда и самодеятельности.',
		'journal_count'         => 4,
		'memory_count'          => 8,
		'fund_count'            => 4,
		'memory_kicker'         => '03 / Память',
		'memory_title'          => 'Герои антитеррора',
		'memory_text'           => 'Люди, чья служба и гражданская смелость остаются в памяти. Каждая карточка — биография, не сводка.',
		'fund_kicker'           => '04 / Фонд',
		'fund_title'            => 'Фонд инициатив',
		'fund_text'             => 'Открытые предложения в сфере антитеррора: профилактика, память, образование, городская безопасность. Один человек — один голос. Это не сбор средств.',
		'footer_text'           => 'Журнал памяти, хроники и гражданских инициатив в сфере антитеррора.',
		'votes_require_login'   => false,
	);
}

/**
 * Настройка темы с запасным значением.
 *
 * @param string $key Ключ без префикса strazha_.
 * @return mixed
 */
function strazha_mod( $key ) {
	$defaults = strazha_defaults();
	$default  = array_key_exists( $key, $defaults ) ? $defaults[ $key ] : '';
	return get_theme_mod( 'strazha_' . $key, $default );
}

/**
 * Булев флажок кастомайзера.
 *
 * @param string $key Ключ.
 */
function strazha_flag( $key ) {
	return (bool) strazha_mod( $key );
}

/**
 * Целая настройка в границах.
 *
 * @param string $key Ключ.
 * @param int    $min Минимум.
 * @param int    $max Максимум.
 */
function strazha_int_mod( $key, $min, $max ) {
	$value = absint( strazha_mod( $key ) );
	return max( $min, min( $max, $value ) );
}

if ( ! function_exists( 'strazha_journal_url' ) ) {
	/**
	 * Адрес ленты журнала.
	 */
	function strazha_journal_url() {
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page && 'page' === get_option( 'show_on_front' ) ) {
			return get_permalink( $posts_page );
		}
		return home_url( '/#journal' );
	}
}

if ( ! function_exists( 'strazha_brand' ) ) {
	/**
	 * Знак и название, либо пользовательский логотип.
	 */
	function strazha_brand( $linked = null ) {
		if ( has_custom_logo() ) {
			the_custom_logo();
			return;
		}

		if ( null === $linked ) {
			$linked = ! is_front_page();
		}
		$tag = $linked ? 'a' : 'div';
		?>
		<<?php echo esc_html( $tag ); ?> class="brand"<?php echo $linked ? ' href="' . esc_url( home_url( '/' ) ) . '"' : ''; ?>>
			<svg class="brand__mark" viewBox="0 0 36 44" aria-hidden="true">
				<path d="M7 2.5h22v39H7z" fill="none" stroke="currentColor" stroke-width="1.5"/>
				<path d="M18 11v14" fill="none" stroke="currentColor" stroke-width="1.5"/>
				<circle cx="18" cy="30" r="2.1" fill="currentColor"/>
			</svg>
			<span class="brand__text">
				<strong><?php bloginfo( 'name' ); ?></strong>
				<?php if ( get_bloginfo( 'description', 'display' ) ) : ?>
					<em><?php bloginfo( 'description' ); ?></em>
				<?php endif; ?>
			</span>
		</<?php echo esc_html( $tag ); ?>>
		<?php
	}
}

if ( ! function_exists( 'strazha_fallback_menu' ) ) {
	/**
	 * Меню, пока редакция не назначила своё.
	 */
	function strazha_fallback_menu() {
		$items = array(
			array(
				'label'   => __( 'Журнал', 'strazha' ),
				'url'     => strazha_journal_url(),
				'current' => is_home() && ! is_front_page(),
			),
			array(
				'label'   => __( 'Память', 'strazha' ),
				'url'     => get_post_type_archive_link( 'hero' ),
				'current' => is_post_type_archive( 'hero' ) || is_singular( 'hero' ) || is_tax( 'hero_unit' ),
			),
			array(
				'label'   => __( 'Инициативы', 'strazha' ),
				'url'     => get_post_type_archive_link( 'initiative' ),
				'current' => is_post_type_archive( 'initiative' ) || is_singular( 'initiative' ) || is_tax( 'initiative_field' ),
			),
		);

		echo '<ul class="menu">';
		foreach ( $items as $item ) {
			if ( empty( $item['url'] ) ) {
				continue;
			}
			printf(
				'<li%1$s><a href="%2$s"%3$s>%4$s</a></li>',
				! empty( $item['current'] ) ? ' class="current-menu-item"' : '',
				esc_url( $item['url'] ),
				! empty( $item['current'] ) ? ' aria-current="page"' : '',
				esc_html( $item['label'] )
			);
		}
		echo '</ul>';
	}
}

if ( ! function_exists( 'strazha_navigation' ) ) {
	/**
	 * Вывести меню локации.
	 *
	 * @param string $location Локация.
	 */
	function strazha_navigation( $location = 'primary' ) {
		if ( has_nav_menu( $location ) ) {
			wp_nav_menu(
				array(
					'theme_location' => $location,
					'container'      => false,
					'menu_class'     => 'menu',
					'fallback_cb'    => false,
					'depth'          => 2,
				)
			);
			return;
		}

		if ( 'primary' === $location || 'footer' === $location ) {
			strazha_fallback_menu();
		}
	}
}

if ( ! function_exists( 'strazha_breadcrumbs' ) ) {
	/**
	 * Короткая цепочка разделов.
	 */
	function strazha_breadcrumbs() {
		if ( is_front_page() ) {
			return;
		}

		echo '<nav class="crumbs" aria-label="' . esc_attr__( 'Навигация по сайту', 'strazha' ) . '"><ol>';
		printf(
			'<li><a href="%1$s">%2$s</a></li>',
			esc_url( home_url( '/' ) ),
			esc_html__( 'Главная', 'strazha' )
		);

		if ( is_singular( 'post' ) ) {
			$cats = get_the_category();
			if ( $cats ) {
				printf(
					'<li><a href="%1$s">%2$s</a></li>',
					esc_url( get_category_link( $cats[0] ) ),
					esc_html( $cats[0]->name )
				);
			}
			printf( '<li aria-current="page">%s</li>', esc_html( get_the_title() ) );
		} elseif ( is_singular() ) {
			$object = get_post_type_object( get_post_type() );
			if ( $object && ! empty( $object->has_archive ) ) {
				printf(
					'<li><a href="%1$s">%2$s</a></li>',
					esc_url( get_post_type_archive_link( get_post_type() ) ),
					esc_html( $object->labels->name )
				);
			}
			printf( '<li aria-current="page">%s</li>', esc_html( get_the_title() ) );
		} elseif ( is_search() ) {
			echo '<li aria-current="page">' . esc_html__( 'Поиск', 'strazha' ) . '</li>';
		} elseif ( is_404() ) {
			echo '<li aria-current="page">404</li>';
		} elseif ( is_category() || is_tag() || is_tax() ) {
			printf( '<li aria-current="page">%s</li>', esc_html( single_term_title( '', false ) ) );
		} elseif ( is_post_type_archive() ) {
			printf( '<li aria-current="page">%s</li>', esc_html( post_type_archive_title( '', false ) ) );
		} elseif ( is_home() ) {
			echo '<li aria-current="page">' . esc_html__( 'Журнал', 'strazha' ) . '</li>';
		} elseif ( is_author() ) {
			printf( '<li aria-current="page">%s</li>', esc_html( get_the_author() ) );
		} else {
			$title = wp_strip_all_tags( get_the_archive_title() );
			if ( $title ) {
				printf( '<li aria-current="page">%s</li>', esc_html( $title ) );
			}
		}

		echo '</ol></nav>';
	}
}

if ( ! function_exists( 'strazha_term_name' ) ) {
	/**
	 * Первое имя термина.
	 *
	 * @param int    $post_id  Запись.
	 * @param string $taxonomy Таксономия.
	 */
	function strazha_term_name( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}
		return $terms[0]->name;
	}
}

if ( ! function_exists( 'strazha_section_heading' ) ) {
	/**
	 * Шапка нумерованного раздела.
	 *
	 * @param string               $number Номер.
	 * @param string               $title  Заголовок.
	 * @param string               $text   Пояснение.
	 * @param array<string,string> $link   Ссылка: url, label.
	 */
	function strazha_section_heading( $number, $title, $text = '', $link = array() ) {
		?>
		<header class="section__head">
			<p class="section__num"><?php echo esc_html( $number ); ?></p>
			<div class="section__copy">
				<h2><?php echo esc_html( $title ); ?></h2>
				<?php if ( $text ) : ?>
					<p><?php echo esc_html( $text ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $link['url'] ) && ! empty( $link['label'] ) ) : ?>
				<a class="textlink" href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?><span aria-hidden="true"> →</span></a>
			<?php endif; ?>
		</header>
		<?php
	}
}

/**
 * Идентификатор обложечной записи.
 */
function strazha_cover_id() {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}

	$picked = absint( strazha_mod( 'cover_post' ) );
	if ( $picked && 'post' === get_post_type( $picked ) && 'publish' === get_post_status( $picked ) ) {
		$cached = $picked;
		return $cached;
	}

	$sticky = array_map( 'absint', (array) get_option( 'sticky_posts' ) );
	$sticky = array_values( array_filter( $sticky ) );
	if ( $sticky ) {
		rsort( $sticky );
		foreach ( $sticky as $sticky_id ) {
			if ( 'publish' === get_post_status( $sticky_id ) ) {
				$cached = $sticky_id;
				return $cached;
			}
		}
	}

	$latest = get_posts(
		array(
			'numberposts' => 1,
			'fields'      => 'ids',
			'post_status' => 'publish',
		)
	);
	$cached = $latest ? (int) $latest[0] : 0;
	return $cached;
}

/**
 * Статусы инициатив.
 *
 * @return array<string, string>
 */
function strazha_statuses() {
	return array(
		'collecting' => __( 'Идёт голосование', 'strazha' ),
		'review'     => __( 'На рассмотрении', 'strazha' ),
		'adopted'    => __( 'Принята', 'strazha' ),
		'archived'   => __( 'Архив', 'strazha' ),
	);
}

/**
 * Статус инициативы.
 *
 * @param int $post_id Запись.
 */
function strazha_initiative_status( $post_id ) {
	$status = get_post_meta( $post_id, '_strazha_status', true );
	$known  = strazha_statuses();
	if ( ! isset( $known[ $status ] ) ) {
		return 'collecting';
	}
	return $status;
}

/**
 * Число голосов.
 *
 * @param int $post_id Запись.
 */
function strazha_vote_count( $post_id ) {
	$value = get_post_meta( $post_id, '_strazha_votes', true );
	if ( '' === $value ) {
		return 0;
	}
	return max( 0, (int) $value );
}

/**
 * Текущий адрес, чтобы вернуть человека после голоса без JS.
 */
function strazha_current_url() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : '';
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	if ( ! is_string( $host ) || ! is_string( $uri ) ) {
		return home_url( '/' );
	}
	$scheme = is_ssl() ? 'https://' : 'http://';
	return esc_url_raw( $scheme . $host . $uri );
}

if ( ! function_exists( 'strazha_vote_notice' ) ) {
	/**
	 * Сообщение после голосования без JavaScript.
	 */
	function strazha_vote_notice() {
		if ( empty( $_GET['voted'] ) ) {
			return;
		}
		$code = sanitize_key( wp_unslash( $_GET['voted'] ) );
		$map  = array(
			'added'     => __( 'Голос учтён. Его можно отозвать, пока инициатива открыта.', 'strazha' ),
			'removed'   => __( 'Голос снят.', 'strazha' ),
			'closed'    => __( 'Голосование по этой инициативе закрыто.', 'strazha' ),
			'missing'   => __( 'Инициатива не найдена.', 'strazha' ),
			'limited'   => __( 'Слишком много попыток. Подождите немного.', 'strazha' ),
			'login'     => __( 'Чтобы голосовать, нужно войти.', 'strazha' ),
			'protected' => __( 'Эта инициатива закрыта паролем.', 'strazha' ),
			'error'     => __( 'Не удалось сохранить голос.', 'strazha' ),
		);
		if ( ! isset( $map[ $code ] ) ) {
			return;
		}
		printf(
			'<div class="notice shell" role="status"><p>%s</p></div>',
			esc_html( $map[ $code ] )
		);
	}
}

if ( ! function_exists( 'strazha_feed_more' ) ) {
	/**
	 * Кнопка подгрузки и обычная пагинация для ленты записей.
	 */
	function strazha_feed_more() {
		global $wp_query;
		if ( empty( $wp_query->max_num_pages ) || $wp_query->max_num_pages < 2 ) {
			return;
		}
		?>
		<div class="feed-more">
			<button
				type="button"
				class="more"
				hidden
				data-feed
				data-page="<?php echo esc_attr( max( 1, (int) get_query_var( 'paged' ) ) ); ?>"
				data-max="<?php echo esc_attr( (int) $wp_query->max_num_pages ); ?>"
				data-cat="<?php echo esc_attr( is_category() ? get_queried_object_id() : 0 ); ?>"
				data-tag="<?php echo esc_attr( is_tag() ? get_queried_object_id() : 0 ); ?>"
				data-author="<?php echo esc_attr( is_author() ? get_queried_object_id() : 0 ); ?>"
				data-year="<?php echo esc_attr( (int) get_query_var( 'year' ) ); ?>"
				data-month="<?php echo esc_attr( (int) get_query_var( 'monthnum' ) ); ?>"
				data-day="<?php echo esc_attr( (int) get_query_var( 'day' ) ); ?>"
				data-s="<?php echo esc_attr( get_search_query() ); ?>"
			><?php esc_html_e( 'Показать ещё', 'strazha' ); ?></button>
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 1,
					'prev_text' => __( 'Назад', 'strazha' ),
					'next_text' => __( 'Дальше', 'strazha' ),
				)
			);
			?>
		</div>
		<?php
	}
}

/**
 * Инициалы для карточки без портрета.
 *
 * @param string $title Имя.
 */
function strazha_initials( $title ) {
	$parts  = preg_split( '/\s+/u', trim( wp_strip_all_tags( $title ) ) );
	$letters = '';
	if ( ! is_array( $parts ) ) {
		return '';
	}
	foreach ( array_slice( $parts, 0, 2 ) as $part ) {
		$letters .= mb_substr( $part, 0, 1 );
	}
	return mb_strtoupper( $letters );
}

/**
 * Принципы редакции: из кастомайзера, с фильтром для дочерней темы.
 *
 * @return array<int, array{title: string, text: string}>
 */
function strazha_principles() {
	$items = array(
		array(
			'title' => (string) strazha_mod( 'principle_1_title' ),
			'text'  => (string) strazha_mod( 'principle_1_text' ),
		),
		array(
			'title' => (string) strazha_mod( 'principle_2_title' ),
			'text'  => (string) strazha_mod( 'principle_2_text' ),
		),
		array(
			'title' => (string) strazha_mod( 'principle_3_title' ),
			'text'  => (string) strazha_mod( 'principle_3_text' ),
		),
	);

	$items = array_values(
		array_filter(
			$items,
			static function ( $item ) {
				return '' !== trim( $item['title'] . $item['text'] );
			}
		)
	);

	return apply_filters( 'strazha_principles', $items );
}

if ( ! function_exists( 'strazha_status_filters' ) ) {
	/**
	 * Фильтр фонда по статусу.
	 *
	 * @param string $base Базовый адрес.
	 */
	function strazha_status_filters( $base = '' ) {
		if ( ! $base ) {
			if ( is_tax( 'initiative_field' ) ) {
				$link = get_term_link( get_queried_object() );
				$base = is_wp_error( $link ) ? get_post_type_archive_link( 'initiative' ) : $link;
			} else {
				$base = get_post_type_archive_link( 'initiative' );
			}
		}
		if ( ! $base ) {
			return;
		}

		$current = isset( $_GET['istatus'] ) ? sanitize_key( wp_unslash( $_GET['istatus'] ) ) : '';
		if ( ! isset( strazha_statuses()[ $current ] ) ) {
			$current = '';
		}

		echo '<nav class="filters" aria-label="' . esc_attr__( 'Статус инициатив', 'strazha' ) . '">';
		printf(
			'<a href="%1$s"%2$s>%3$s</a>',
			esc_url( remove_query_arg( 'istatus', $base ) ),
			'' === $current ? ' aria-current="page"' : '',
			esc_html__( 'Все', 'strazha' )
		);
		foreach ( strazha_statuses() as $key => $label ) {
			printf(
				'<a href="%1$s"%2$s>%3$s</a>',
				esc_url( add_query_arg( 'istatus', $key, $base ) ),
				$current === $key ? ' aria-current="page"' : '',
				esc_html( $label )
			);
		}
		echo '</nav>';
	}
}
