<?php
/**
 * Appearance → Рубеж.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'rubezh_register_settings' );
add_action( 'admin_menu', 'rubezh_settings_menu' );
add_action( 'admin_notices', 'rubezh_admin_notice' );
add_action( 'admin_post_rubezh_dismiss_notice', 'rubezh_dismiss_notice' );

/**
 * Settings group.
 */
function rubezh_register_settings() {
	register_setting(
		'rubezh',
		'rubezh_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'rubezh_sanitize_settings',
			'default'           => array(),
		)
	);
}

/**
 * Clean the settings payload.
 *
 * @param mixed $input Raw.
 * @return array<string, string>
 */
function rubezh_sanitize_settings( $input ) {
	$input = is_array( $input ) ? $input : array();
	return array(
		'issue_no'    => isset( $input['issue_no'] ) ? sanitize_text_field( $input['issue_no'] ) : '07',
		'issue_label' => isset( $input['issue_label'] ) ? sanitize_text_field( $input['issue_label'] ) : '',
		'email'       => isset( $input['email'] ) ? sanitize_email( $input['email'] ) : '',
		'lead'        => isset( $input['lead'] ) ? sanitize_textarea_field( $input['lead'] ) : '',
	);
}

/**
 * Theme page under Appearance.
 */
function rubezh_settings_menu() {
	add_theme_page(
		__( 'Рубеж', 'rubezh' ),
		__( 'Рубеж', 'rubezh' ),
		'manage_options',
		'rubezh',
		'rubezh_settings_page'
	);
}

/**
 * Settings screen.
 */
function rubezh_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$settings = rubezh_settings();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Рубеж', 'rubezh' ); ?></h1>
		<p><?php esc_html_e( 'Номер выпуска, почта редакции и лид главной. Состав полосы меняется в редакторе: Внешний вид → Редактор → Шаблоны → Главная.', 'rubezh' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'rubezh' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="rubezh-issue-no"><?php esc_html_e( 'Номер выпуска', 'rubezh' ); ?></label></th>
					<td><input class="regular-text" id="rubezh-issue-no" name="rubezh_settings[issue_no]" value="<?php echo esc_attr( $settings['issue_no'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="rubezh-issue-label"><?php esc_html_e( 'Сезон или подпись', 'rubezh' ); ?></label></th>
					<td><input class="regular-text" id="rubezh-issue-label" name="rubezh_settings[issue_label]" value="<?php echo esc_attr( $settings['issue_label'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="rubezh-email"><?php esc_html_e( 'Почта редакции', 'rubezh' ); ?></label></th>
					<td>
						<input class="regular-text" type="email" id="rubezh-email" name="rubezh_settings[email]" value="<?php echo esc_attr( $settings['email'] ); ?>">
						<p class="description"><?php esc_html_e( 'Пока почта пустая, на сайте остаётся только ориентир «звоните 112». Редакция не заменяет экстренные службы.', 'rubezh' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="rubezh-lead"><?php esc_html_e( 'Лид на обложке', 'rubezh' ); ?></label></th>
					<td><textarea class="large-text" rows="4" id="rubezh-lead" name="rubezh_settings[lead]"><?php echo esc_textarea( $settings['lead'] ); ?></textarea></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * One-time orientation for the person who activates the theme.
 */
function rubezh_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) || get_user_meta( get_current_user_id(), 'rubezh_notice_off', true ) ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=rubezh_dismiss_notice' ), 'rubezh_dismiss_notice' );
	echo '<div class="notice notice-info"><p>' . esc_html__( 'Рубеж включён. Профили памяти и инициативы с пометкой «Образец» — демонстрационные, их нужно заменить документальными материалами до публикации. Почта и номер выпуска: Внешний вид → Рубеж.', 'rubezh' ) . ' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Скрыть', 'rubezh' ) . '</a></p></div>';
}

/**
 * Dismiss the orientation notice.
 */
function rubezh_dismiss_notice() {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'rubezh_dismiss_notice' ) ) {
		wp_die( esc_html__( 'Не удалось скрыть уведомление.', 'rubezh' ), 403 );
	}
	update_user_meta( get_current_user_id(), 'rubezh_notice_off', '1' );
	wp_safe_redirect( admin_url() );
	exit;
}
