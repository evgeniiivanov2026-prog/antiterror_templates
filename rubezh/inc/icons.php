<?php
/**
 * Фирменные знаки. Разметка статическая, без пользовательских данных.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SVG-знак темы.
 *
 * @param string $name mark|search|moon|sun.
 * @return string
 */
function rubezh_icon( $name ) {
	$icons = array(
		'mark'   => '<svg class="rz-icon rz-icon--mark" viewBox="0 0 48 48" aria-hidden="true"><path d="M9 42V7h6v35M9 24h26" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="37" cy="24" r="2.2" fill="currentColor"/></svg>',
		'search' => '<svg class="rz-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M16 16l5 5" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
		'moon'   => '<svg class="rz-icon rz-icon--moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 15.2A8.2 8.2 0 1 1 8.8 4 6.4 6.4 0 0 0 20 15.2Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
		'sun'    => '<svg class="rz-icon rz-icon--sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3.2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M12 3v2M12 19v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M3 12h2M19 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>',
	);

	return $icons[ $name ] ?? '';
}
