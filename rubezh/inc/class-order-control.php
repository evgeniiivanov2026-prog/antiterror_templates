<?php
/**
 * Порядок разделов главной.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Список разделов с кнопками порядка.
 */
class Rubezh_Order_Control extends WP_Customize_Control {
	/**
	 * Тип элемента.
	 *
	 * @var string
	 */
	public $type = 'rubezh-order';

	/**
	 * Разметка в настройщике.
	 */
	public function render_content() {
		$labels = rubezh_section_catalog();
		$keys   = explode( ',', rubezh_sanitize_order( (string) $this->value() ) );
		echo '<span class="customize-control-title">' . esc_html( $this->label ) . '</span>';
		if ( $this->description ) {
			echo '<span class="description customize-control-description">' . esc_html( $this->description ) . '</span>';
		}
		echo '<ul class="rubezh-order" data-setting="' . esc_attr( $this->id ) . '">';
		foreach ( $keys as $key ) {
			if ( ! isset( $labels[ $key ] ) ) {
				continue;
			}
			echo '<li data-key="' . esc_attr( $key ) . '"><span>' . esc_html( $labels[ $key ] ) . '</span>';
			echo '<button type="button" class="button is-up">' . esc_html__( 'Выше', 'rubezh' ) . '</button>';
			echo '<button type="button" class="button is-down">' . esc_html__( 'Ниже', 'rubezh' ) . '</button></li>';
		}
		echo '</ul>';
	}
}
