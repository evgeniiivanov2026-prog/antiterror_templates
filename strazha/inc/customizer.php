<?php
/**
 * Настройки внешнего вида.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'customize_register', 'strazha_customize' );
/**
 * Панель «Стража».
 *
 * @param WP_Customize_Manager $wp_customize Кастомайзер.
 */
function strazha_customize( $wp_customize ) {
	$defaults = strazha_defaults();

	$wp_customize->add_panel(
		'strazha',
		array(
			'title'    => __( 'Стража', 'strazha' ),
			'priority' => 20,
		)
	);

	$wp_customize->add_section(
		'strazha_opening',
		array(
			'title' => __( 'Обложка выпуска', 'strazha' ),
			'panel' => 'strazha',
		)
	);
	$wp_customize->add_section(
		'strazha_sections',
		array(
			'title' => __( 'Разделы главной', 'strazha' ),
			'panel' => 'strazha',
		)
	);
	$wp_customize->add_section(
		'strazha_fund',
		array(
			'title' => __( 'Фонд и подвал', 'strazha' ),
			'panel' => 'strazha',
		)
	);

	$text_fields = array(
		'kicker'            => array( 'section' => 'strazha_opening', 'label' => __( 'Надзаголовок', 'strazha' ) ),
		'hero_title'        => array( 'section' => 'strazha_opening', 'label' => __( 'Заголовок', 'strazha' ) ),
		'hero_emphasis'     => array( 'section' => 'strazha_opening', 'label' => __( 'Акцентная строка', 'strazha' ) ),
		'mission_title'     => array( 'section' => 'strazha_sections', 'label' => __( 'Заголовок миссии', 'strazha' ) ),
		'memory_kicker'     => array( 'section' => 'strazha_sections', 'label' => __( 'Номер раздела памяти', 'strazha' ) ),
		'memory_title'      => array( 'section' => 'strazha_sections', 'label' => __( 'Заголовок памяти', 'strazha' ) ),
		'fund_kicker'       => array( 'section' => 'strazha_fund', 'label' => __( 'Номер фонда', 'strazha' ) ),
		'fund_title'        => array( 'section' => 'strazha_fund', 'label' => __( 'Заголовок фонда', 'strazha' ) ),
		'principle_1_title' => array( 'section' => 'strazha_sections', 'label' => __( 'Принцип 1 — название', 'strazha' ) ),
		'principle_2_title' => array( 'section' => 'strazha_sections', 'label' => __( 'Принцип 2 — название', 'strazha' ) ),
		'principle_3_title' => array( 'section' => 'strazha_sections', 'label' => __( 'Принцип 3 — название', 'strazha' ) ),
	);
	foreach ( $text_fields as $key => $field ) {
		strazha_add_text( $wp_customize, $key, $field['label'], $field['section'], $defaults[ $key ] );
	}

	$areas = array(
		'hero_lead'         => array( 'section' => 'strazha_opening', 'label' => __( 'Лид обложки', 'strazha' ) ),
		'mission_text'      => array( 'section' => 'strazha_sections', 'label' => __( 'Текст миссии', 'strazha' ) ),
		'principle_1_text'  => array( 'section' => 'strazha_sections', 'label' => __( 'Принцип 1 — текст', 'strazha' ) ),
		'principle_2_text'  => array( 'section' => 'strazha_sections', 'label' => __( 'Принцип 2 — текст', 'strazha' ) ),
		'principle_3_text'  => array( 'section' => 'strazha_sections', 'label' => __( 'Принцип 3 — текст', 'strazha' ) ),
		'memory_text'       => array( 'section' => 'strazha_sections', 'label' => __( 'Текст памяти', 'strazha' ) ),
		'fund_text'         => array( 'section' => 'strazha_fund', 'label' => __( 'Текст фонда', 'strazha' ) ),
		'footer_text'       => array( 'section' => 'strazha_fund', 'label' => __( 'Текст в подвале', 'strazha' ) ),
	);
	foreach ( $areas as $key => $field ) {
		strazha_add_textarea( $wp_customize, $key, $field['label'], $field['section'], $defaults[ $key ] );
	}

	$wp_customize->add_setting(
		'strazha_email',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_email',
		)
	);
	$wp_customize->add_control(
		'strazha_email',
		array(
			'section'     => 'strazha_fund',
			'label'       => __( 'Почта редакции', 'strazha' ),
			'description' => __( 'Пустое поле скрывает адрес. Экстренные сообщения сайт не принимает: в подвале остаётся 112.', 'strazha' ),
			'type'        => 'email',
		)
	);

	$choices = array( 0 => __( 'Авто: закреплённая или свежая запись', 'strazha' ) );
	$posts   = get_posts(
		array(
			'numberposts' => 40,
			'post_status' => 'publish',
		)
	);
	foreach ( $posts as $post ) {
		$choices[ $post->ID ] = $post->post_title;
	}
	$selected = absint( get_theme_mod( 'strazha_cover_post', 0 ) );
	if ( $selected && ! isset( $choices[ $selected ] ) ) {
		$choices[ $selected ] = get_the_title( $selected );
	}
	$wp_customize->add_setting(
		'strazha_cover_post',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'strazha_cover_post',
		array(
			'section' => 'strazha_opening',
			'label'   => __( 'Обложечный материал', 'strazha' ),
			'type'    => 'select',
			'choices' => $choices,
		)
	);

	$toggles = array(
		'show_mission' => __( 'Показывать миссию', 'strazha' ),
		'show_journal' => __( 'Показывать ленту журнала', 'strazha' ),
		'show_memory'  => __( 'Показывать стену памяти', 'strazha' ),
		'show_fund'    => __( 'Показывать фонд инициатив', 'strazha' ),
		'show_help'    => __( 'Показывать полосу 112', 'strazha' ),
	);
	foreach ( $toggles as $key => $label ) {
		strazha_add_checkbox( $wp_customize, $key, $label, 'strazha_sections', true );
	}
	strazha_add_checkbox(
		$wp_customize,
		'votes_require_login',
		__( 'Голосовать могут только вошедшие пользователи', 'strazha' ),
		'strazha_fund',
		false
	);

	$numbers = array(
		'journal_count' => array( 'label' => __( 'Материалов в ленте на главной', 'strazha' ), 'section' => 'strazha_sections', 'min' => 1, 'max' => 8 ),
		'memory_count'  => array( 'label' => __( 'Имён на главной', 'strazha' ), 'section' => 'strazha_sections', 'min' => 1, 'max' => 16 ),
		'fund_count'    => array( 'label' => __( 'Инициатив на главной', 'strazha' ), 'section' => 'strazha_fund', 'min' => 1, 'max' => 8 ),
	);
	foreach ( $numbers as $key => $field ) {
		$wp_customize->add_setting(
			'strazha_' . $key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			'strazha_' . $key,
			array(
				'section' => $field['section'],
				'label'   => $field['label'],
				'type'    => 'number',
				'input_attrs' => array(
					'min' => $field['min'],
					'max' => $field['max'],
				),
			)
		);
	}
}

/**
 * Текстовое поле.
 *
 * @param WP_Customize_Manager $wp_customize Кастомайзер.
 * @param string               $key          Ключ.
 * @param string               $label        Подпись.
 * @param string               $section      Секция.
 * @param string               $default      Значение.
 */
function strazha_add_text( $wp_customize, $key, $label, $section, $default ) {
	$wp_customize->add_setting(
		'strazha_' . $key,
		array(
			'default'           => $default,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'strazha_' . $key,
		array(
			'section' => $section,
			'label'   => $label,
			'type'    => 'text',
		)
	);
}

/**
 * Многострочное поле.
 *
 * @param WP_Customize_Manager $wp_customize Кастомайзер.
 * @param string               $key          Ключ.
 * @param string               $label        Подпись.
 * @param string               $section      Секция.
 * @param string               $default      Значение.
 */
function strazha_add_textarea( $wp_customize, $key, $label, $section, $default ) {
	$wp_customize->add_setting(
		'strazha_' . $key,
		array(
			'default'           => $default,
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	$wp_customize->add_control(
		'strazha_' . $key,
		array(
			'section' => $section,
			'label'   => $label,
			'type'    => 'textarea',
		)
	);
}

/**
 * Флажок.
 *
 * @param WP_Customize_Manager $wp_customize Кастомайзер.
 * @param string               $key          Ключ.
 * @param string               $label        Подпись.
 * @param string               $section      Секция.
 * @param bool                 $default      Значение.
 */
function strazha_add_checkbox( $wp_customize, $key, $label, $section, $default ) {
	$wp_customize->add_setting(
		'strazha_' . $key,
		array(
			'default'           => $default,
			'sanitize_callback' => static function ( $value ) {
				return (bool) $value;
			},
		)
	);
	$wp_customize->add_control(
		'strazha_' . $key,
		array(
			'section' => $section,
			'label'   => $label,
			'type'    => 'checkbox',
		)
	);
}
