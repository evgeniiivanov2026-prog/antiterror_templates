<?php
/**
 * Обложка.
 *
 * @package Strazha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cover_id = strazha_cover_id();
$cats     = get_categories(
	array(
		'hide_empty' => true,
		'number'     => 8,
	)
);
?>
<section class="opening">
	<div class="shell opening__grid">
		<div class="opening__copy">
			<p class="kicker"><?php echo esc_html( strazha_mod( 'kicker' ) ); ?></p>
			<h1>
				<?php echo esc_html( strazha_mod( 'hero_title' ) ); ?>
				<?php if ( strazha_mod( 'hero_emphasis' ) ) : ?>
					<em><?php echo esc_html( strazha_mod( 'hero_emphasis' ) ); ?></em>
				<?php endif; ?>
			</h1>
			<p class="opening__lead"><?php echo esc_html( strazha_mod( 'hero_lead' ) ); ?></p>
			<div class="opening__actions">
				<?php if ( strazha_flag( 'show_journal' ) ) : ?>
					<a class="button" href="#journal"><?php esc_html_e( 'Читать выпуск', 'strazha' ); ?><span aria-hidden="true"> →</span></a>
				<?php else : ?>
					<a class="button" href="<?php echo esc_url( strazha_journal_url() ); ?>"><?php esc_html_e( 'Читать выпуск', 'strazha' ); ?><span aria-hidden="true"> →</span></a>
				<?php endif; ?>
				<?php if ( strazha_flag( 'show_memory' ) ) : ?>
					<a class="textlink" href="#memory"><?php esc_html_e( 'Стена памяти', 'strazha' ); ?><span aria-hidden="true"> ↓</span></a>
				<?php else : ?>
					<a class="textlink" href="<?php echo esc_url( get_post_type_archive_link( 'hero' ) ); ?>"><?php esc_html_e( 'Стена памяти', 'strazha' ); ?><span aria-hidden="true"> →</span></a>
				<?php endif; ?>
			</div>
		</div>
		<div class="opening__feature">
			<?php
			if ( $cover_id ) {
				$cover = new WP_Query(
					array(
						'p'              => $cover_id,
						'post_type'      => 'post',
						'post_status'    => 'publish',
						'posts_per_page' => 1,
					)
				);
				if ( $cover->have_posts() ) {
					$cover->the_post();
					get_template_part( 'template-parts/content/feature' );
					wp_reset_postdata();
				}
			} else {
				?>
				<div class="feature feature--empty">
					<p class="kicker"><?php esc_html_e( 'Выпуск', 'strazha' ); ?></p>
					<p><?php esc_html_e( 'Обложечный материал появится, когда вы опубликуете первую запись.', 'strazha' ); ?></p>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	<?php if ( $cats ) : ?>
		<div class="shell cats" aria-label="<?php esc_attr_e( 'Рубрики', 'strazha' ); ?>">
			<?php foreach ( $cats as $cat ) : ?>
				<a href="<?php echo esc_url( get_category_link( $cat ) ); ?>">
					<?php echo esc_html( $cat->name ); ?>
					<span><?php echo esc_html( number_format_i18n( $cat->count ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
