<?php
/**
 * What the site actually does.
 *
 * @package Atjournal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$memory = get_post_type_archive_link( 'atj_hero' );
$ledger = get_post_type_archive_link( 'atj_initiative' );
$rows   = array(
	array( '01', __( 'Вести журнал', 'atjournal' ), __( 'Публиковать проверенные материалы о профилактике, праве и памяти — без паники и без оперативных подробностей.', 'atjournal' ), atj_journal_url() ),
	array( '02', __( 'Хранить память', 'atjournal' ), __( 'Держать отдельный архив героев: имя, годы, роль и биография, которую можно подтвердить.', 'atjournal' ), $memory ? $memory : home_url( '/#pamyat' ) ),
	array( '03', __( 'Собирать голоса', 'atjournal' ), __( 'Давать читателям открытую тетрадь инициатив. Редакция публикует предложения сама и может закрыть голосование.', 'atjournal' ), $ledger ? $ledger : home_url( '/#iniciativy' ) ),
	array( '04', __( 'Не подменять службы', 'atjournal' ), __( 'В случае угрозы направлять к номеру 112. Сайт не принимает доносы и не ведёт расследований.', 'atjournal' ), '#signal' ),
);
?>
<section class="atj-section atj-directions" aria-labelledby="atj-directions-title">
	<div class="atj-shell">
		<div class="atj-section__head atj-reveal">
			<div>
				<p class="atj-num"><?php esc_html_e( '05  /  Устройство', 'atjournal' ); ?></p>
				<h2 id="atj-directions-title"><?php esc_html_e( 'Как устроена работа', 'atjournal' ); ?></h2>
			</div>
			<p><?php esc_html_e( 'Четыре постоянных дела вместо разовых акций. Каждое можно открыть отдельно.', 'atjournal' ); ?></p>
		</div>
		<div class="atj-directions__list">
			<?php foreach ( $rows as $row ) : ?>
				<a class="atj-direction atj-reveal" href="<?php echo esc_url( $row[3] ); ?>">
					<span class="atj-direction__n"><?php echo esc_html( $row[0] ); ?></span>
					<span>
						<strong><?php echo esc_html( $row[1] ); ?></strong>
						<em><?php echo esc_html( $row[2] ); ?></em>
					</span>
					<span aria-hidden="true" class="atj-direction__arrow">↗</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
