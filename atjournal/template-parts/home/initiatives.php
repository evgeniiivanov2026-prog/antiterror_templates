<?php
/**
 * Homepage ledger.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$query = new WP_Query(
	array(
		'post_type'      => 'atj_initiative',
		'posts_per_page' => 4,
		'meta_key'       => '_atj_vote_count',
		'orderby'        => array(
			'meta_value_num' => 'DESC',
			'date'           => 'DESC',
		),
		'no_found_rows'  => true,
	)
);
atj_prime_votes( wp_list_pluck( $query->posts, 'ID' ) );
$archive = get_post_type_archive_link( 'atj_initiative' );
?>
<section class="atj-section" id="iniciativy" aria-labelledby="atj-ledger-title">
	<div class="atj-shell">
		<div class="atj-section__head atj-reveal">
			<div>
				<p class="atj-num"><?php esc_html_e( '04  /  Фонд инициатив', 'atjournal' ); ?></p>
				<h2 id="atj-ledger-title"><?php esc_html_e( 'За что можно отдать голос', 'atjournal' ); ?></h2>
			</div>
			<p><?php esc_html_e( 'Один читатель — один голос. Повторное нажатие отзывает его. Это не референдум и не решение органа власти.', 'atjournal' ); ?></p>
			<?php if ( $archive ) : ?>
				<a class="atj-textlink" href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( 'Все инициативы', 'atjournal' ); ?> <span aria-hidden="true">→</span></a>
			<?php endif; ?>
		</div>
		<?php if ( $query->have_posts() ) : ?>
			<div class="atj-ledger">
				<?php
				$index = 0;
				while ( $query->have_posts() ) :
					$query->the_post();
					++$index;
					get_template_part( 'template-parts/content', 'initiative', array( 'index' => $index ) );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<p class="atj-fineprint">
				<?php
				if ( is_user_logged_in() ) {
					esc_html_e( 'Вы вошли в учётную запись: голос сохранится и на других устройствах, где вы вошли.', 'atjournal' );
				} else {
					esc_html_e( 'Без входа голос привязан к этому браузеру и не хранит ваше имя. Войдите, если хотите сохранить его между устройствами.', 'atjournal' );
				}
				?>
			</p>
		<?php else : ?>
			<div class="atj-empty"><p><?php esc_html_e( 'Редакция ещё не открыла ни одной инициативы.', 'atjournal' ); ?></p></div>
		<?php endif; ?>
	</div>
</section>
