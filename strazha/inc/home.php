<?php
/**
 * Разделы главной. Дочерняя тема может снять действие и поставить своё.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'strazha_home_sections', 'strazha_home_mission', 10 );
add_action( 'strazha_home_sections', 'strazha_home_journal', 20 );
add_action( 'strazha_home_sections', 'strazha_home_memory', 30 );
add_action( 'strazha_home_sections', 'strazha_home_fund', 40 );
add_action( 'strazha_home_sections', 'strazha_home_help', 50 );

/**
 * Миссия и принципы.
 */
function strazha_home_mission() {
	if ( ! strazha_flag( 'show_mission' ) ) {
		return;
	}
	get_template_part( 'template-parts/home/mission' );
}

/**
 * Лента выпуска.
 */
function strazha_home_journal() {
	if ( ! strazha_flag( 'show_journal' ) ) {
		return;
	}
	get_template_part( 'template-parts/home/journal' );
}

/**
 * Стена памяти.
 */
function strazha_home_memory() {
	if ( ! strazha_flag( 'show_memory' ) ) {
		return;
	}
	get_template_part( 'template-parts/home/memory' );
}

/**
 * Фонд инициатив.
 */
function strazha_home_fund() {
	if ( ! strazha_flag( 'show_fund' ) ) {
		return;
	}
	get_template_part( 'template-parts/home/fund' );
}

/**
 * Спокойное напоминание про 112.
 */
function strazha_home_help() {
	if ( ! strazha_flag( 'show_help' ) ) {
		return;
	}
	get_template_part( 'template-parts/home/help' );
}
