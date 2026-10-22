<?php
/**
 * Custom post types: memory of heroes and civic initiatives.
 *
 * @package Aegis
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'aegis_register_types' );
function aegis_register_types() {
	register_post_type(
		'aegis_hero',
		array(
			'labels' => array(
				'name'          => __( 'Герои', 'aegis' ),
				'singular_name' => __( 'Герой', 'aegis' ),
				'add_new_item'  => __( 'Добавить карточку памяти', 'aegis' ),
				'edit_item'     => __( 'Редактировать карточку памяти', 'aegis' ),
				'search_items'  => __( 'Искать в зале памяти', 'aegis' ),
				'all_items'     => __( 'Все герои', 'aegis' ),
			),
			'public'       => true,
			'has_archive'  => 'memory',
			'rewrite'      => array( 'slug' => 'memory' ),
			'menu_icon'    => 'dashicons-awards',
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields' ),
		)
	);

	register_taxonomy(
		'aegis_service',
		'aegis_hero',
		array(
			'labels' => array(
				'name'          => __( 'Службы', 'aegis' ),
				'singular_name' => __( 'Служба', 'aegis' ),
			),
			'public'       => true,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'service' ),
		)
	);

	register_post_type(
		'aegis_initiative',
		array(
			'labels' => array(
				'name'          => __( 'Инициативы', 'aegis' ),
				'singular_name' => __( 'Инициатива', 'aegis' ),
				'add_new_item'  => __( 'Добавить инициативу', 'aegis' ),
				'edit_item'     => __( 'Редактировать инициативу', 'aegis' ),
				'search_items'  => __( 'Искать инициативы', 'aegis' ),
				'all_items'     => __( 'Все инициативы', 'aegis' ),
			),
			'public'       => true,
			'has_archive'  => 'initiatives',
			'rewrite'      => array( 'slug' => 'initiatives' ),
			'menu_icon'    => 'dashicons-megaphone',
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields', 'comments' ),
		)
	);

	register_taxonomy(
		'aegis_topic',
		'aegis_initiative',
		array(
			'labels' => array(
				'name'          => __( 'Темы инициатив', 'aegis' ),
				'singular_name' => __( 'Тема', 'aegis' ),
			),
			'public'       => true,
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'topic' ),
		)
	);
}

add_action( 'add_meta_boxes', 'aegis_meta_boxes' );
function aegis_meta_boxes() {
	add_meta_box( 'aegis_hero_meta', __( 'Данные памяти', 'aegis' ), 'aegis_hero_meta_box', 'aegis_hero', 'side' );
	add_meta_box( 'aegis_initiative_meta', __( 'Данные инициативы', 'aegis' ), 'aegis_initiative_meta_box', 'aegis_initiative', 'side' );
}

function aegis_hero_meta_box( $post ) {
	wp_nonce_field( 'aegis_hero_meta', 'aegis_hero_nonce' );
	$meta = aegis_hero_meta( $post->ID );
	aegis_field( 'aegis_years', __( 'Годы жизни', 'aegis' ), $meta['years'] );
	aegis_field( 'aegis_service', __( 'Служба / подразделение', 'aegis' ), $meta['service'] );
	aegis_field( 'aegis_award', __( 'Награда / звание', 'aegis' ), $meta['award'] );
	aegis_field( 'aegis_feat_date', __( 'Дата подвига или гибели', 'aegis' ), $meta['date'] );
}

function aegis_initiative_meta_box( $post ) {
	wp_nonce_field( 'aegis_initiative_meta', 'aegis_initiative_nonce' );
	$meta   = aegis_initiative_meta( $post->ID );
	$votes  = $meta['votes'];
	$status = $meta['status'];
	aegis_field( 'aegis_author_name', __( 'Автор инициативы', 'aegis' ), $meta['author'] );
	?>
	<p>
		<label for="aegis_status"><?php esc_html_e( 'Статус', 'aegis' ); ?></label>
		<select name="aegis_status" id="aegis_status">
			<?php foreach ( array( 'open', 'review', 'accepted', 'closed' ) as $key ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( aegis_status_label( $key ) ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="aegis_votes"><?php esc_html_e( 'Голоса', 'aegis' ); ?></label>
		<input type="number" min="0" name="aegis_votes" id="aegis_votes" value="<?php echo esc_attr( $votes ); ?>">
	</p>
	<?php
}

function aegis_field( $name, $label, $value ) {
	printf(
		'<p><label for="%1$s">%2$s</label><input type="text" class="widefat" name="%1$s" id="%1$s" value="%3$s"></p>',
		esc_attr( $name ),
		esc_html( $label ),
		esc_attr( $value )
	);
}

add_action( 'save_post_aegis_hero', 'aegis_save_hero_meta' );
function aegis_save_hero_meta( $post_id ) {
	if ( ! aegis_can_save( $post_id, 'aegis_hero_nonce', 'aegis_hero_meta' ) ) {
		return;
	}
	foreach ( array( 'aegis_years', 'aegis_service', 'aegis_award', 'aegis_feat_date' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, '_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
}

add_action( 'save_post_aegis_initiative', 'aegis_default_initiative_meta', 5 );
function aegis_default_initiative_meta( $post_id ) {
	if ( ! metadata_exists( 'post', $post_id, '_aegis_votes' ) ) {
		update_post_meta( $post_id, '_aegis_votes', 0 );
	}
	if ( ! metadata_exists( 'post', $post_id, '_aegis_status' ) ) {
		update_post_meta( $post_id, '_aegis_status', 'open' );
	}
}

add_action( 'save_post_aegis_initiative', 'aegis_save_initiative_meta' );
function aegis_save_initiative_meta( $post_id ) {
	if ( ! aegis_can_save( $post_id, 'aegis_initiative_nonce', 'aegis_initiative_meta' ) ) {
		return;
	}
	if ( isset( $_POST['aegis_author_name'] ) ) {
		update_post_meta( $post_id, '_aegis_author_name', sanitize_text_field( wp_unslash( $_POST['aegis_author_name'] ) ) );
	}
	if ( isset( $_POST['aegis_status'] ) ) {
		$status = sanitize_key( wp_unslash( $_POST['aegis_status'] ) );
		if ( in_array( $status, array( 'open', 'review', 'accepted', 'closed' ), true ) ) {
			update_post_meta( $post_id, '_aegis_status', $status );
		}
	}
	if ( isset( $_POST['aegis_votes'] ) ) {
		update_post_meta( $post_id, '_aegis_votes', absint( $_POST['aegis_votes'] ) );
	}
}

function aegis_can_save( $post_id, $nonce_field, $action ) {
	if ( ! isset( $_POST[ $nonce_field ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce_field ] ) ), $action ) ) {
		return false;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	return true;
}

add_action( 'pre_get_posts', 'aegis_sort_initiatives' );
function aegis_sort_initiatives( WP_Query $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'aegis_initiative' ) ) {
		$query->set( 'meta_key', '_aegis_votes' );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'order', 'DESC' );
	}
}

add_filter( 'manage_aegis_initiative_posts_columns', 'aegis_initiative_columns' );
function aegis_initiative_columns( $columns ) {
	$columns['aegis_votes']  = __( 'Голоса', 'aegis' );
	$columns['aegis_status'] = __( 'Статус', 'aegis' );
	return $columns;
}

add_action( 'manage_aegis_initiative_posts_custom_column', 'aegis_initiative_column', 10, 2 );
function aegis_initiative_column( $column, $post_id ) {
	if ( 'aegis_votes' === $column ) {
		echo absint( get_post_meta( $post_id, '_aegis_votes', true ) );
	}
	if ( 'aegis_status' === $column ) {
		echo esc_html( aegis_status_label( get_post_meta( $post_id, '_aegis_status', true ) ) );
	}
}
