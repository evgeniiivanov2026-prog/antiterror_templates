<?php
/**
 * Вспомогательные функции шаблонов.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function rubezh_status_label( $status ) {
	$map = array(
		'open'      => __( 'Открыта', 'rubezh' ),
		'review'    => __( 'На рассмотрении', 'rubezh' ),
		'accepted'  => __( 'Принята', 'rubezh' ),
		'completed' => __( 'Реализована', 'rubezh' ),
	);
	return isset( $map[ $status ] ) ? $map[ $status ] : $map['open'];
}

function rubezh_first_term( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return null;
	}
	return $terms[0];
}

function rubezh_reading_time( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$words   = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );
	$minutes = max( 1, (int) ceil( $words / 180 ) );
	return sprintf( _n( '%s минута чтения', '%s минут чтения', $minutes, 'rubezh' ), number_format_i18n( $minutes ) );
}

function rubezh_archive_link( $post_type ) {
	$link = get_post_type_archive_link( $post_type );
	return $link ? $link : home_url( '/' );
}

function rubezh_query_posts( $args = array() ) {
	return new WP_Query( wp_parse_args( $args, array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => false,
	) ) );
}

function rubezh_query_heroes( $count = 6 ) {
	return new WP_Query( array(
		'post_type'      => 'hero',
		'posts_per_page' => $count,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );
}

function rubezh_query_initiatives( $count = 4 ) {
	return new WP_Query( array(
		'post_type'      => 'initiative',
		'posts_per_page' => $count,
		'post_status'    => 'publish',
		'meta_key'       => '_rubezh_votes',
		'orderby'        => array(
			'meta_value_num' => 'DESC',
			'date'           => 'DESC',
		),
	) );
}

function rubezh_fallback_menu() {
	?>
	<ul class="menu">
		<li><a href="<?php echo esc_url( home_url( '/#journal' ) ); ?>"><?php esc_html_e( 'Журнал', 'rubezh' ); ?></a></li>
		<li><a href="<?php echo esc_url( rubezh_archive_link( 'hero' ) ); ?>"><?php esc_html_e( 'Память', 'rubezh' ); ?></a></li>
		<li><a href="<?php echo esc_url( rubezh_archive_link( 'initiative' ) ); ?>"><?php esc_html_e( 'Фонд', 'rubezh' ); ?></a></li>
		<li><a href="<?php echo esc_url( home_url( '/#report' ) ); ?>"><?php esc_html_e( 'Обращение', 'rubezh' ); ?></a></li>
	</ul>
	<?php
}

function rubezh_share_url( $network, $url, $title ) {
	$url   = rawurlencode( $url );
	$title = rawurlencode( $title );
	$map   = array(
		'telegram' => 'https://t.me/share/url?url=' . $url . '&text=' . $title,
		'vk'       => 'https://vk.com/share.php?url=' . $url . '&title=' . $title,
	);
	return isset( $map[ $network ] ) ? $map[ $network ] : '';
}
