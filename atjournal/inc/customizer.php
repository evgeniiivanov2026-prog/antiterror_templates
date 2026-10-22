<?php
/**
 * A short list of editorial settings. Layout stays in the templates.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'customize_register', 'atj_customizer' );
/**
 * Register customizer fields.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function atj_customizer( $wp_customize ) {
	$wp_customize->add_section(
		'atj_journal',
		array(
			'title'    => __( 'Вестник', 'atjournal' ),
			'priority' => 30,
		)
	);

	$fields = array(
		'atj_kicker'   => array(
			'label'   => __( 'Надзаголовок', 'atjournal' ),
			'type'    => 'text',
			'sanitize'=> 'sanitize_text_field',
		),
		'atj_issue'    => array(
			'label'   => __( 'Пометка выпуска', 'atjournal' ),
			'type'    => 'text',
			'sanitize'=> 'sanitize_text_field',
		),
		'atj_headline' => array(
			'label'       => __( 'Заголовок на главной', 'atjournal' ),
			'description' => __( 'Каждая строка — отдельная строка заголовка. Последняя будет курсивом.', 'atjournal' ),
			'type'        => 'textarea',
			'sanitize'    => 'sanitize_textarea_field',
		),
		'atj_lead'     => array(
			'label'   => __( 'Лид на главной', 'atjournal' ),
			'type'    => 'textarea',
			'sanitize'=> 'sanitize_textarea_field',
		),
		'atj_email'    => array(
			'label'       => __( 'Почта редакции', 'atjournal' ),
			'description' => __( 'Пока адрес пуст, кнопка письма скрыта. Не указывайте ящик, которого ещё нет.', 'atjournal' ),
			'type'        => 'email',
			'sanitize'    => 'sanitize_email',
		),
	);

	foreach ( $fields as $id => $field ) {
		$key = substr( $id, 4 );
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => atj_defaults()[ $key ] ?? '',
				'sanitize_callback' => $field['sanitize'],
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'       => $field['label'],
				'description' => $field['description'] ?? '',
				'section'     => 'atj_journal',
				'type'        => $field['type'],
			)
		);
	}
}
