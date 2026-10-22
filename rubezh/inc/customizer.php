<?php
/**
 * Настройщик: состав главной, тексты, цвета, режим голоса.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'customize_register', 'rubezh_customize_register' );
/**
 * @param WP_Customize_Manager $wp Настройщик.
 */
function rubezh_customize_register( $wp ) {
	require_once get_template_directory() . '/inc/class-order-control.php';

	$wp->add_panel(
		'rubezh',
		array(
			'title'    => __( 'Рубеж', 'rubezh' ),
			'priority' => 30,
		)
	);

	$wp->add_section(
		'rubezh_identity',
		array(
			'title' => __( 'Издание', 'rubezh' ),
			'panel' => 'rubezh',
		)
	);
	rubezh_add_text( $wp, 'rubezh_kicker', __( 'Строка издания', 'rubezh' ), 'rubezh_identity' );
	rubezh_add_text( $wp, 'rubezh_issue', __( 'Номер выпуска', 'rubezh' ), 'rubezh_identity', __( 'Необязательно. Например: Выпуск 12.', 'rubezh' ) );
	rubezh_add_text( $wp, 'rubezh_email', __( 'Почта редакции', 'rubezh' ), 'rubezh_identity', __( 'Если поле пустое, кнопка письма не показывается.', 'rubezh' ), 'sanitize_email' );
	rubezh_add_textarea( $wp, 'rubezh_legal', __( 'Юридическая строка в подвале', 'rubezh' ), 'rubezh_identity' );

	$wp->add_section(
		'rubezh_sections',
		array(
			'title' => __( 'Состав главной', 'rubezh' ),
			'panel' => 'rubezh',
		)
	);
	$wp->add_setting(
		'rubezh_section_order',
		array(
			'default'           => rubezh_defaults()['section_order'],
			'sanitize_callback' => 'rubezh_sanitize_order',
		)
	);
	$wp->add_control(
		new Rubezh_Order_Control(
			$wp,
			'rubezh_section_order',
			array(
				'label'       => __( 'Порядок разделов', 'rubezh' ),
				'description' => __( 'Скрытый раздел остаётся в списке и вернётся, когда его снова включат.', 'rubezh' ),
				'section'     => 'rubezh_sections',
			)
		)
	);
	foreach ( rubezh_section_catalog() as $key => $label ) {
		$setting = 'rubezh_show_' . $key;
		$wp->add_setting(
			$setting,
			array(
				'default'           => true,
				'sanitize_callback' => static function ( $value ) {
					return (bool) $value;
				},
			)
		);
		$wp->add_control(
			$setting,
			array(
				'type'    => 'checkbox',
				'label'   => sprintf( /* translators: %s: section name */ __( 'Показывать: %s', 'rubezh' ), $label ),
				'section' => 'rubezh_sections',
			)
		);
	}

	$wp->add_section(
		'rubezh_vote',
		array(
			'title' => __( 'Голосование', 'rubezh' ),
			'panel' => 'rubezh',
		)
	);
	$wp->add_setting(
		'rubezh_vote_mode',
		array(
			'default'           => 'public',
			'sanitize_callback' => static function ( $value ) {
				return 'users' === $value ? 'users' : 'public';
			},
		)
	);
	$wp->add_control(
		'rubezh_vote_mode',
		array(
			'type'    => 'radio',
			'section' => 'rubezh_vote',
			'label'   => __( 'Кто может поддержать инициативу', 'rubezh' ),
			'choices' => array(
				'public' => __( 'Любой посетитель, один голос с браузера', 'rubezh' ),
				'users'  => __( 'Только вошедшие пользователи', 'rubezh' ),
			),
		)
	);

	$wp->add_section(
		'rubezh_principles',
		array(
			'title' => __( 'Принципы', 'rubezh' ),
			'panel' => 'rubezh',
		)
	);
	rubezh_add_text( $wp, 'rubezh_mission_title', __( 'Заголовок', 'rubezh' ), 'rubezh_principles' );
	rubezh_add_textarea( $wp, 'rubezh_mission_lede', __( 'Вводный абзац', 'rubezh' ), 'rubezh_principles' );
	rubezh_add_textarea( $wp, 'rubezh_mission_text', __( 'Пояснение', 'rubezh' ), 'rubezh_principles' );
	for ( $i = 1; $i <= 3; $i++ ) {
		rubezh_add_text( $wp, 'rubezh_principle_' . $i . '_title', sprintf( /* translators: %d: principle index */ __( 'Принцип %d, заголовок', 'rubezh' ), $i ), 'rubezh_principles' );
		rubezh_add_textarea( $wp, 'rubezh_principle_' . $i . '_text', sprintf( /* translators: %d: principle index */ __( 'Принцип %d, текст', 'rubezh' ), $i ), 'rubezh_principles' );
	}

	$wp->add_section(
		'rubezh_signal',
		array(
			'title' => __( 'Экстренная плашка', 'rubezh' ),
			'panel' => 'rubezh',
		)
	);
	rubezh_add_text( $wp, 'rubezh_signal_title', __( 'Заголовок', 'rubezh' ), 'rubezh_signal' );
	rubezh_add_textarea( $wp, 'rubezh_signal_text', __( 'Текст', 'rubezh' ), 'rubezh_signal' );
	rubezh_add_textarea( $wp, 'rubezh_signal_note', __( 'Нижняя оговорка', 'rubezh' ), 'rubezh_signal' );

	$wp->add_section(
		'rubezh_colors',
		array(
			'title'       => __( 'Цвета светлой темы', 'rubezh' ),
			'description' => __( 'Тёмная тема сохраняет ночную палитру. Для кнопки выбирайте достаточно тёмный сигнал: на нём светлый текст.', 'rubezh' ),
			'panel'       => 'rubezh',
		)
	);
	foreach (
		array(
			'rubezh_color_paper'  => __( 'Бумага', 'rubezh' ),
			'rubezh_color_ink'    => __( 'Чернила', 'rubezh' ),
			'rubezh_color_signal' => __( 'Сигнал', 'rubezh' ),
		) as $id => $label
	) {
		$slug = str_replace( 'rubezh_color_', '', $id );
		$wp->add_setting(
			$id,
			array(
				'default'           => 'paper' === $slug ? '#f3efe6' : ( 'ink' === $slug ? '#1c1915' : '#7c2433' ),
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);
		$wp->add_control(
			new WP_Customize_Color_Control(
				$wp,
				$id,
				array(
					'label'   => $label,
					'section' => 'rubezh_colors',
				)
			)
		);
	}
}

/**
 * @param WP_Customize_Manager $wp        Настройщик.
 * @param string               $id        Идентификатор.
 * @param string               $label     Подпись.
 * @param string               $section   Секция.
 * @param string               $description Описание.
 * @param callable|string      $sanitize  Очистка.
 */
function rubezh_add_text( $wp, $id, $label, $section, $description = '', $sanitize = 'sanitize_text_field' ) {
	$key     = str_replace( 'rubezh_', '', $id );
	$default = rubezh_defaults()[ $key ] ?? '';
	$wp->add_setting(
		$id,
		array(
			'default'           => $default,
			'sanitize_callback' => $sanitize,
		)
	);
	$wp->add_control(
		$id,
		array(
			'type'        => 'text',
			'label'       => $label,
			'description' => $description,
			'section'     => $section,
		)
	);
}

/**
 * @param WP_Customize_Manager $wp      Настройщик.
 * @param string               $id      Идентификатор.
 * @param string               $label   Подпись.
 * @param string               $section Секция.
 */
function rubezh_add_textarea( $wp, $id, $label, $section ) {
	$key = str_replace( 'rubezh_', '', $id );
	$wp->add_setting(
		$id,
		array(
			'default'           => rubezh_defaults()[ $key ] ?? '',
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	$wp->add_control(
		$id,
		array(
			'type'    => 'textarea',
			'label'   => $label,
			'section' => $section,
		)
	);
}

add_action( 'customize_controls_enqueue_scripts', 'rubezh_customize_controls' );
/**
 * Сценарий порядка разделов.
 */
function rubezh_customize_controls() {
	wp_enqueue_script(
		'rubezh-customize',
		get_template_directory_uri() . '/assets/js/customizer.js',
		array( 'customize-controls' ),
		RUBEZH_VERSION,
		true
	);
}
