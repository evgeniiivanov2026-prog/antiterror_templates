<?php
/**
 * Customizer — flexible editorial copy.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'customize_register', 'aegis_customize' );
function aegis_customize( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_panel(
		'aegis_panel',
		array(
			'title'       => __( 'Aegis — журнал и фонд', 'aegis' ),
			'description' => __( 'Тексты главной, контакты и видимость разделов.', 'aegis' ),
			'priority'    => 30,
		)
	);

	$wp_customize->add_section( 'aegis_masthead', array(
		'title' => __( 'Шапка и выпуск', 'aegis' ),
		'panel' => 'aegis_panel',
	) );

	aegis_text_control( $wp_customize, 'aegis_kicker', 'aegis_masthead', __( 'Кернер журнала', 'aegis' ), __( 'Журнал антитеррора • Память • Инициативы', 'aegis' ) );
	aegis_text_control( $wp_customize, 'aegis_city', 'aegis_masthead', __( 'Город / регион выпуска', 'aegis' ), __( 'Россия', 'aegis' ) );
	aegis_text_control( $wp_customize, 'aegis_email', 'aegis_masthead', __( 'Почта редакции и фонда', 'aegis' ), get_option( 'admin_email' ) );

	$wp_customize->add_section( 'aegis_hero', array(
		'title' => __( 'Герой главной', 'aegis' ),
		'panel' => 'aegis_panel',
	) );

	aegis_text_control( $wp_customize, 'aegis_hero_title', 'aegis_hero', __( 'Заголовок', 'aegis' ), __( 'Память держит линию. Общество держит строй.', 'aegis' ) );
	$wp_customize->add_setting( 'aegis_hero_lead', array(
		'default'           => __( 'Aegis — журнальный шаблон антитеррора: расследования и аналитика, зал памяти героев и фонд гражданских инициатив, за которые можно проголосовать.', 'aegis' ),
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'aegis_hero_lead', array(
		'label'   => __( 'Лид', 'aegis' ),
		'section' => 'aegis_hero',
		'type'    => 'textarea',
	) );
	aegis_text_control( $wp_customize, 'aegis_hero_cta', 'aegis_hero', __( 'Текст кнопки', 'aegis' ), __( 'Поддержать инициативу', 'aegis' ) );
	aegis_text_control( $wp_customize, 'aegis_hero_cta_url', 'aegis_hero', __( 'Ссылка кнопки', 'aegis' ), '#initiatives' );

	$wp_customize->add_section( 'aegis_sections', array(
		'title' => __( 'Разделы главной', 'aegis' ),
		'panel' => 'aegis_panel',
	) );

	foreach ( array(
		'aegis_show_journal'     => __( 'Показывать журнал', 'aegis' ),
		'aegis_show_memory'      => __( 'Показывать память героев', 'aegis' ),
		'aegis_show_initiatives' => __( 'Показывать фонд инициатив', 'aegis' ),
		'aegis_show_foundation'  => __( 'Показывать блок фонда', 'aegis' ),
	) as $id => $label ) {
		$wp_customize->add_setting( $id, array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $label,
			'section' => 'aegis_sections',
			'type'    => 'checkbox',
		) );
	}

	$wp_customize->add_section( 'aegis_foundation', array(
		'title' => __( 'Фонд', 'aegis' ),
		'panel' => 'aegis_panel',
	) );

	aegis_text_control( $wp_customize, 'aegis_foundation_title', 'aegis_foundation', __( 'Заголовок фонда', 'aegis' ), __( 'Фонд инициатив антитеррора', 'aegis' ) );
	$wp_customize->add_setting( 'aegis_foundation_lead', array(
		'default'           => __( 'Мы собираем проверяемые истории, бережём память и даём гражданам инструмент, чтобы выбрать, какие профилактические проекты усилить в первую очередь.', 'aegis' ),
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'aegis_foundation_lead', array(
		'label'   => __( 'Текст фонда', 'aegis' ),
		'section' => 'aegis_foundation',
		'type'    => 'textarea',
	) );
}

function aegis_text_control( WP_Customize_Manager $wp_customize, $id, $section, $label, $default ) {
	$wp_customize->add_setting( $id, array(
		'default'           => $default,
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( $id, array(
		'label'   => $label,
		'section' => $section,
		'type'    => 'text',
	) );
}
