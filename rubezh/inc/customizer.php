<?php
/**
 * Настройки темы: выпуск, тексты секций, контакты.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'customize_register', 'rubezh_customize' );
function rubezh_customize( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_panel(
		'rubezh',
		array(
			'title'    => __( 'Рубеж — журнал', 'rubezh' ),
			'priority' => 30,
		)
	);

	$wp_customize->add_section( 'rubezh_cover', array(
		'title' => __( 'Обложка выпуска', 'rubezh' ),
		'panel' => 'rubezh',
	) );

	$fields = array(
		'rubezh_issue'   => array( 'label' => __( 'Номер выпуска', 'rubezh' ), 'default' => '№ 04 / осень 2027', 'section' => 'rubezh_cover' ),
		'rubezh_kicker'  => array( 'label' => __( 'Крикер', 'rubezh' ), 'default' => 'Антитеррор · Память · Гражданское действие', 'section' => 'rubezh_cover' ),
		'rubezh_title'   => array( 'label' => __( 'Заголовок обложки', 'rubezh' ), 'default' => 'Память сильнее страха', 'section' => 'rubezh_cover' ),
		'rubezh_lead'    => array( 'label' => __( 'Лид', 'rubezh' ), 'default' => 'Журнал фиксирует факты, хранит имена героев антитеррора и собирает гражданские инициативы, которые делают среду безопаснее.', 'section' => 'rubezh_cover', 'type' => 'textarea' ),
		'rubezh_email'   => array( 'label' => __( 'Почта фонда', 'rubezh' ), 'default' => 'info@example.org', 'section' => 'rubezh_cover' ),
		'rubezh_city'    => array( 'label' => __( 'Город / юрисдикция', 'rubezh' ), 'default' => 'Россия', 'section' => 'rubezh_cover' ),
	);

	$wp_customize->add_section( 'rubezh_sections', array(
		'title' => __( 'Секции главной', 'rubezh' ),
		'panel' => 'rubezh',
	) );

	$fields['rubezh_show_journal']    = array( 'label' => __( 'Показывать журнал', 'rubezh' ), 'default' => true, 'section' => 'rubezh_sections', 'type' => 'checkbox' );
	$fields['rubezh_show_memorial']   = array( 'label' => __( 'Показывать память героев', 'rubezh' ), 'default' => true, 'section' => 'rubezh_sections', 'type' => 'checkbox' );
	$fields['rubezh_show_fund']       = array( 'label' => __( 'Показывать фонд инициатив', 'rubezh' ), 'default' => true, 'section' => 'rubezh_sections', 'type' => 'checkbox' );
	$fields['rubezh_show_cta']        = array( 'label' => __( 'Показывать обращение', 'rubezh' ), 'default' => true, 'section' => 'rubezh_sections', 'type' => 'checkbox' );
	$fields['rubezh_memorial_title']  = array( 'label' => __( 'Заголовок памяти', 'rubezh' ), 'default' => 'Имена, которые стоят между угрозой и людьми', 'section' => 'rubezh_sections' );
	$fields['rubezh_fund_title']      = array( 'label' => __( 'Заголовок фонда', 'rubezh' ), 'default' => 'Голосуйте за инициативы антитеррора', 'section' => 'rubezh_sections' );

	foreach ( $fields as $id => $args ) {
		$type = isset( $args['type'] ) ? $args['type'] : 'text';
		$wp_customize->add_setting( $id, array(
			'default'           => $args['default'],
			'sanitize_callback' => ( 'checkbox' === $type ) ? 'rest_sanitize_boolean' : ( 'textarea' === $type ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $args['label'],
			'section' => $args['section'],
			'type'    => $type,
		) );
	}
}

function rubezh_option( $key, $fallback = '' ) {
	$value = get_theme_mod( $key, $fallback );
	return ( '' === $value || null === $value ) ? $fallback : $value;
}
