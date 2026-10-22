<?php
/**
 * Customizer — flexible editorial identity.
 *
 * @package AntiterrorJournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'customize_register', 'atj_customize_register' );
function atj_customize_register( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_panel(
		'atj_panel',
		array(
			'title'       => __( 'Журнал антитеррора', 'antiterror-journal' ),
			'description' => __( 'Гибкие тексты главной, фонда и памяти героев.', 'antiterror-journal' ),
			'priority'    => 30,
		)
	);

	$sections = array(
		'atj_identity'  => __( 'Выпуск и идентичность', 'antiterror-journal' ),
		'atj_hero'      => __( 'Обложка главной', 'antiterror-journal' ),
		'atj_memorial'  => __( 'Память героев', 'antiterror-journal' ),
		'atj_fund'      => __( 'Фонд инициатив', 'antiterror-journal' ),
		'atj_contacts'  => __( 'Обращение и контакты', 'antiterror-journal' ),
	);

	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section( $id, array( 'title' => $title, 'panel' => 'atj_panel' ) );
	}

	$fields = array(
		'atj_kicker' => array( 'atj_identity', __( 'Надзаголовок', 'antiterror-journal' ), 'Журнал антитеррора', 'text' ),
		'atj_issue'  => array( 'atj_identity', __( 'Номер выпуска', 'antiterror-journal' ), 'Выпуск 01', 'text' ),
		'atj_logo_line' => array( 'atj_identity', __( 'Короткая строка логотипа', 'antiterror-journal' ), 'Антитеррор', 'text' ),
		'atj_hero_title' => array( 'atj_hero', __( 'Заголовок обложки', 'antiterror-journal' ), "Память сильнее\nстраха.", 'textarea' ),
		'atj_hero_lead' => array( 'atj_hero', __( 'Лид обложки', 'antiterror-journal' ), 'Редакция собирает проверяемые материалы о противодействии терроризму, сохраняет память героев и открывает фонд гражданских инициатив.', 'textarea' ),
		'atj_hero_primary' => array( 'atj_hero', __( 'Текст основной кнопки', 'antiterror-journal' ), 'Читать выпуск', 'text' ),
		'atj_memorial_intro' => array( 'atj_memorial', __( 'Вступление раздела памяти', 'antiterror-journal' ), 'Карточки памяти публикуются только по проверяемым сведениям. Мы называем имена, чтобы помнить дело, а не превращать трагедию в сенсацию.', 'textarea' ),
		'atj_fund_intro' => array( 'atj_fund', __( 'Вступление фонда', 'antiterror-journal' ), 'Читатели голосуют за инициативы в сфере антитеррора: просвещение, профилактика, помощь семьям и гражданский контроль. Редакция модерирует каждую заявку.', 'textarea' ),
		'atj_contact_email' => array( 'atj_contacts', __( 'Почта редакции', 'antiterror-journal' ), get_option( 'admin_email' ), 'email' ),
		'atj_report_text' => array( 'atj_contacts', __( 'Текст блока обращения', 'antiterror-journal' ), 'Если вам известны сведения об угрозе, передайте их редакции. Не вступайте в конфликт и не проводите расследование самостоятельно.', 'textarea' ),
	);

	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $field[2],
				'sanitize_callback' => 'email' === $field[3] ? 'sanitize_email' : ( 'textarea' === $field[3] ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
				'transport'         => 'refresh',
			)
		);
		$control_class = 'textarea' === $field[3] ? 'WP_Customize_Control' : 'WP_Customize_Control';
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $field[1],
				'section' => $field[0],
				'type'    => $field[3],
			)
		);
		unset( $control_class );
	}

	$wp_customize->add_setting(
		'atj_allow_guest_votes',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);
	$wp_customize->add_control(
		'atj_allow_guest_votes',
		array(
			'label'   => __( 'Разрешить голосование гостям', 'antiterror-journal' ),
			'section' => 'atj_fund',
			'type'    => 'checkbox',
		)
	);
}

function atj_mod( $key, $fallback = '' ) {
	$value = get_theme_mod( $key, $fallback );
	return is_string( $value ) ? $value : $fallback;
}
