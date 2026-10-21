<?php
/**
 * Customizer — all homepage and foundation copy is editable.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'customize_register', 'vigilum_customize_register' );
function vigilum_customize_register( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_panel( 'vigilum', array(
		'title'    => __( 'Vigilum', 'vigilum' ),
		'priority' => 30,
	) );

	$wp_customize->add_section( 'vigilum_identity', array(
		'title' => __( 'Идентичность', 'vigilum' ),
		'panel' => 'vigilum',
	) );

	$wp_customize->add_section( 'vigilum_hero', array(
		'title' => __( 'Главный экран', 'vigilum' ),
		'panel' => 'vigilum',
	) );

	$wp_customize->add_section( 'vigilum_foundation', array(
		'title' => __( 'Фонд и контакты', 'vigilum' ),
		'panel' => 'vigilum',
	) );

	$fields = array(
		array( 'vigilum_kicker', 'vigilum_hero', __( 'Надзаголовок (kicker)', 'vigilum' ), 'text', 'Журнал антитеррора · Том 01' ),
		array( 'vigilum_headline', 'vigilum_hero', __( 'Заголовок', 'vigilum' ), 'textarea', "Слово против\nтеррора." ),
		array( 'vigilum_headline_em', 'vigilum_hero', __( 'Курсивная строка заголовка', 'vigilum' ), 'text', 'Память. Инициатива.' ),
		array( 'vigilum_lead', 'vigilum_hero', __( 'Лид', 'vigilum' ), 'textarea', 'Редакция и фонд собирают факты, хранят память героев антитеррора и дают гражданам голосовать за инициативы, которые укрепляют безопасность.' ),
		array( 'vigilum_issue', 'vigilum_identity', __( 'Номер выпуска', 'vigilum' ), 'text', '01 / 2026' ),
		array( 'vigilum_brand_small', 'vigilum_identity', __( 'Малая строка логотипа', 'vigilum' ), 'text', 'Журнал · Фонд' ),
		array( 'vigilum_brand_large', 'vigilum_identity', __( 'Крупная строка логотипа', 'vigilum' ), 'text', 'Vigilum' ),
		array( 'vigilum_email', 'vigilum_foundation', __( 'Почта редакции', 'vigilum' ), 'email', 'editor@example.org' ),
		array( 'vigilum_cta_label', 'vigilum_foundation', __( 'Текст кнопки обращения', 'vigilum' ), 'text', 'Написать редакции' ),
		array( 'vigilum_cta_note', 'vigilum_foundation', __( 'Примечание у обращения', 'vigilum' ), 'text', 'В экстренной ситуации звоните 112. Не проводите расследование самостоятельно.' ),
		array( 'vigilum_footer_note', 'vigilum_foundation', __( 'Юридическая строка в подвале', 'vigilum' ), 'textarea', 'Информация на сайте не заменяет обращение в экстренные службы. Публикуйте только проверяемые сведения.' ),
		array( 'vigilum_mission', 'vigilum_foundation', __( 'Миссия (коротко)', 'vigilum' ), 'textarea', 'Мы работаем с фактами, бережно относимся к памяти и даём обществу законный канал инициатив в сфере антитеррора.' ),
	);

	foreach ( $fields as $field ) {
		[ $id, $section, $label, $type, $default ] = $field;
		$wp_customize->add_setting( $id, array(
			'default'           => $default,
			'sanitize_callback' => 'email' === $type ? 'sanitize_email' : 'sanitize_textarea_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $label,
			'section' => $section,
			'type'    => in_array( $type, array( 'textarea', 'email', 'text' ), true ) ? $type : 'text',
		) );
	}

	$wp_customize->add_setting( 'vigilum_default_theme', array(
		'default'           => 'system',
		'sanitize_callback' => static function ( $value ) {
			return in_array( $value, array( 'system', 'light', 'dark' ), true ) ? $value : 'system';
		},
	) );
	$wp_customize->add_control( 'vigilum_default_theme', array(
		'label'   => __( 'Тема по умолчанию', 'vigilum' ),
		'section' => 'vigilum_identity',
		'type'    => 'select',
		'choices' => array(
			'system' => __( 'Системная', 'vigilum' ),
			'light'  => __( 'Светлая', 'vigilum' ),
			'dark'   => __( 'Тёмная', 'vigilum' ),
		),
	) );
}

function vigilum_mod( $key, $default = '' ) {
	$value = get_theme_mod( $key, $default );
	return is_string( $value ) ? $value : $default;
}
