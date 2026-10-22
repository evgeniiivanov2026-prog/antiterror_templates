<?php
/**
 * Front page sections: cover, issue, memory, ethics, ballots.
 *
 * @package Rubezh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cover.
 *
 * @return string
 */
function rubezh_render_masthead() {
	$issue = trim( rubezh_setting( 'issue_no' ) );
	$label = trim( rubezh_setting( 'issue_label' ) );
	$kicker = __( 'Журнал антитеррора', 'rubezh' );
	if ( '' !== $issue ) {
		$kicker .= ' · ' . sprintf( /* translators: issue number */ __( 'выпуск %s', 'rubezh' ), $issue );
	}
	if ( '' !== $label ) {
		$kicker .= ' · ' . $label;
	}
	ob_start();
	?>
	<section class="rz-cover rz-reveal" aria-labelledby="rz-cover-title">
		<div class="rz-shell rz-cover__grid">
			<div>
				<p class="rz-kicker"><?php echo esc_html( $kicker ); ?></p>
				<h1 id="rz-cover-title"><?php esc_html_e( 'Память героев.', 'rubezh' ); ?><br><em><?php esc_html_e( 'Голос инициатив.', 'rubezh' ); ?></em></h1>
				<p class="rz-lead"><?php echo esc_html( rubezh_lead_text() ); ?></p>
				<div class="rz-actions">
					<a class="rz-button" href="#journal"><?php esc_html_e( 'Читать выпуск', 'rubezh' ); ?> <span aria-hidden="true">→</span></a>
					<a class="rz-button-text" href="#memory"><?php esc_html_e( 'Раздел памяти', 'rubezh' ); ?></a>
				</div>
			</div>
			<nav class="rz-contents" aria-label="<?php esc_attr_e( 'В этом выпуске', 'rubezh' ); ?>">
				<a href="#journal"><span>01</span><strong><?php esc_html_e( 'Журнал', 'rubezh' ); ?></strong><em><?php esc_html_e( 'хроника', 'rubezh' ); ?></em></a>
				<a href="#memory"><span>02</span><strong><?php esc_html_e( 'Память', 'rubezh' ); ?></strong><em><?php esc_html_e( 'имена', 'rubezh' ); ?></em></a>
				<a href="#initiatives"><span>03</span><strong><?php esc_html_e( 'Фонд', 'rubezh' ); ?></strong><em><?php esc_html_e( 'голоса', 'rubezh' ); ?></em></a>
				<a href="#help"><span>04</span><strong><?php esc_html_e( '112', 'rubezh' ); ?></strong><em><?php esc_html_e( 'если опасно', 'rubezh' ); ?></em></a>
			</nav>
		</div>
		<div class="rz-shell rz-cover__facts" aria-label="<?php esc_attr_e( 'Сейчас в редакции', 'rubezh' ); ?>">
			<?php
			$story_count = rubezh_published_count( 'post' );
			$hero_count  = rubezh_published_count( 'rubezh_hero' );
			$fund_count  = rubezh_published_count( 'rubezh_initiative' );
			?>
			<div><b><?php echo esc_html( rubezh_pad( $story_count ) ); ?></b><span><?php echo esc_html( rubezh_plural( $story_count, __( 'материал в журнале', 'rubezh' ), __( 'материала в журнале', 'rubezh' ), __( 'материалов в журнале', 'rubezh' ) ) ); ?></span></div>
			<div><b><?php echo esc_html( rubezh_pad( $hero_count ) ); ?></b><span><?php echo esc_html( rubezh_plural( $hero_count, __( 'профиль памяти', 'rubezh' ), __( 'профиля памяти', 'rubezh' ), __( 'профилей памяти', 'rubezh' ) ) ); ?></span></div>
			<div><b><?php echo esc_html( rubezh_pad( $fund_count ) ); ?></b><span><?php echo esc_html( rubezh_plural( $fund_count, __( 'инициатива в фонде', 'rubezh' ), __( 'инициативы в фонде', 'rubezh' ), __( 'инициатив в фонде', 'rubezh' ) ) ); ?></span></div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Magazine grid. The first post is the lead, the rest form the rail.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function rubezh_render_journal_grid( $attributes = array() ) {
	$count = isset( $attributes['count'] ) ? (int) $attributes['count'] : 5;
	$count = max( 1, min( 12, $count ) );
	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	ob_start();
	?>
	<section id="journal" class="rz-section rz-reveal" aria-labelledby="rz-journal-title">
		<div class="rz-shell">
			<header class="rz-section__head">
				<div>
					<p class="rz-index"><?php esc_html_e( '01 — Журнал', 'rubezh' ); ?></p>
					<h2 id="rz-journal-title"><?php esc_html_e( 'Свежая полоса', 'rubezh' ); ?></h2>
				</div>
				<div>
					<p><?php esc_html_e( 'Хроника, право и общественная память. Без сенсаций и без подробностей, которые могут навредить.', 'rubezh' ); ?></p>
					<a class="rz-section__link" href="<?php echo esc_url( rubezh_journal_url() ); ?>"><?php esc_html_e( 'Все материалы', 'rubezh' ); ?> →</a>
				</div>
			</header>
			<?php if ( $query->have_posts() ) : ?>
				<div class="rz-issue">
					<?php
					$index = 0;
					while ( $query->have_posts() ) :
						$query->the_post();
						$index++;
						if ( 1 === $index ) {
							rubezh_render_lead_card( get_the_ID() );
						} else {
							if ( 2 === $index ) {
								echo '<div class="rz-rail">';
							}
							rubezh_render_rail_item( get_the_ID(), $index );
						}
					endwhile;
					if ( $index > 1 ) {
						echo '</div>';
					}
					wp_reset_postdata();
					?>
				</div>
			<?php else : ?>
				<p class="rz-empty"><?php esc_html_e( 'Редакция ещё не выпустила материалов. Первый текст появится на этой полосе.', 'rubezh' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Lead story.
 *
 * @param int $post_id Post ID.
 */
function rubezh_render_lead_card( $post_id ) {
	?>
	<article class="rz-lead-card">
		<a class="rz-media-link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo rubezh_media_html( $post_id, 'rubezh-lead' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		<p class="rz-kicker"><?php echo esc_html( rubezh_kicker_label( $post_id ) ); ?> · <time datetime="<?php echo esc_attr( rubezh_date_iso( $post_id ) ); ?>"><?php echo esc_html( rubezh_date( $post_id ) ); ?></time></p>
		<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
		<p class="rz-dek"><?php echo esc_html( rubezh_excerpt( $post_id, 36 ) ); ?></p>
		<p class="rz-meta">
			<?php rubezh_sample_chip( $post_id ); ?>
			<span><?php echo esc_html( sprintf( /* translators: minutes */ _n( '%d мин', '%d мин', rubezh_reading_minutes( $post_id ), 'rubezh' ), rubezh_reading_minutes( $post_id ) ) ); ?></span>
		</p>
	</article>
	<?php
}

/**
 * Compact story in the issue rail.
 *
 * @param int $post_id Post ID.
 * @param int $index   1-based index.
 */
function rubezh_render_rail_item( $post_id, $index ) {
	?>
	<article>
		<p class="rz-rail__n"><?php echo esc_html( rubezh_pad( $index ) ); ?></p>
		<p class="rz-meta"><span><?php echo esc_html( rubezh_kicker_label( $post_id ) ); ?></span><time datetime="<?php echo esc_attr( rubezh_date_iso( $post_id ) ); ?>"><?php echo esc_html( rubezh_date( $post_id ) ); ?></time></p>
		<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
	</article>
	<?php
}

/**
 * Memory list.
 *
 * @param array<string, mixed> $attributes Attributes.
 * @return string
 */
function rubezh_render_memorial( $attributes = array() ) {
	$count   = isset( $attributes['count'] ) ? (int) $attributes['count'] : 4;
	$count   = max( 1, min( 24, $count ) );
	$archive = ! empty( $attributes['archive'] );
	$paged   = $archive ? rubezh_paged() : 1;
	$query   = new WP_Query(
		array(
			'post_type'      => 'rubezh_hero',
			'posts_per_page' => $archive ? max( $count, (int) get_option( 'posts_per_page' ) ) : $count,
			'paged'          => $paged,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => ! $archive,
		)
	);
	ob_start();
	?>
	<section id="memory" class="rz-section rz-section--paper rz-reveal" aria-labelledby="rz-memory-title">
		<div class="rz-shell">
			<header class="rz-section__head">
				<div>
					<p class="rz-index"><?php esc_html_e( '02 — Память', 'rubezh' ); ?></p>
					<h2 id="rz-memory-title"><?php echo esc_html( $archive ? __( 'Имена, которые держат смысл', 'rubezh' ) : __( 'Память героев', 'rubezh' ) ); ?></h2>
				</div>
				<div>
					<p><?php esc_html_e( 'Профиль публикуется по документу или с согласия близких. Образцы в шаблоне помечены и не являются биографиями.', 'rubezh' ); ?></p>
					<?php if ( ! $archive ) : ?>
						<a class="rz-section__link" href="<?php echo esc_url( get_post_type_archive_link( 'rubezh_hero' ) ); ?>"><?php esc_html_e( 'Весь раздел', 'rubezh' ); ?> →</a>
					<?php endif; ?>
				</div>
			</header>
			<?php if ( $query->have_posts() ) : ?>
				<div class="rz-memory-list">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						$id = get_the_ID();
						?>
						<a class="rz-memory-row" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
							<span class="rz-memory-row__years"><?php echo esc_html( (string) get_post_meta( $id, '_rubezh_years', true ) ); ?></span>
							<span>
								<h3><?php echo esc_html( get_the_title() ); ?></h3>
								<?php rubezh_sample_chip( $id ); ?>
							</span>
							<span class="rz-memory-row__role"><?php echo esc_html( (string) get_post_meta( $id, '_rubezh_role', true ) ); ?></span>
							<span class="rz-memory-row__go" aria-hidden="true">→</span>
						</a>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
				<?php
				if ( $archive ) {
					rubezh_pagination( $query, $paged );
				}
				?>
			<?php else : ?>
				<p class="rz-empty"><?php esc_html_e( 'Раздел памяти пуст. Добавьте профиль в консоли — тип записи «Герой памяти».', 'rubezh' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Editorial ethics, carried from the civic three-column principle.
 *
 * @return string
 */
function rubezh_render_ethics() {
	ob_start();
	?>
	<section id="ethics" class="rz-section rz-reveal" aria-labelledby="rz-ethics-title">
		<div class="rz-shell rz-ethics">
			<header class="rz-ethics__intro">
				<p class="rz-index"><?php esc_html_e( 'Принципы', 'rubezh' ); ?></p>
				<h2 id="rz-ethics-title"><?php esc_html_e( 'Как мы держим полосу', 'rubezh' ); ?></h2>
			</header>
			<div>
				<p class="rz-lede"><?php esc_html_e( 'Антитеррор в журнале — это ответственность перед людьми, а не громкость заголовка.', 'rubezh' ); ?></p>
				<p class="rz-dek"><?php esc_html_e( 'Мы не описываем устройство нападений, не публикуем призывы и не превращаем чужую беду в оформление.', 'rubezh' ); ?></p>
				<div class="rz-principles">
					<article><span>А</span><h3><?php esc_html_e( 'Достоинство', 'rubezh' ); ?></h3><p><?php esc_html_e( 'Память — с уважением к человеку и его близким. Имя не используется как украшение материала.', 'rubezh' ); ?></p></article>
					<article><span>Б</span><h3><?php esc_html_e( 'Законность', 'rubezh' ); ?></h3><p><?php esc_html_e( 'Только правовое поле. Никаких инструкций и никаких призывов к расправе.', 'rubezh' ); ?></p></article>
					<article><span>В</span><h3><?php esc_html_e( 'Точность', 'rubezh' ); ?></h3><p><?php esc_html_e( 'Факт отделён от версии. Демонстрационные профили помечены и не выдаются за документы.', 'rubezh' ); ?></p></article>
				</div>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Initiative fund.
 *
 * @param array<string, mixed> $attributes Attributes.
 * @return string
 */
function rubezh_render_initiatives( $attributes = array() ) {
	$count   = isset( $attributes['count'] ) ? (int) $attributes['count'] : 4;
	$count   = max( 1, min( 24, $count ) );
	$archive = ! empty( $attributes['archive'] );
	$paged   = $archive ? rubezh_paged() : 1;
	$query   = new WP_Query(
		array(
			'post_type'      => 'rubezh_initiative',
			'posts_per_page' => $archive ? max( $count, (int) get_option( 'posts_per_page' ) ) : $count,
			'paged'          => $paged,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'no_found_rows'  => ! $archive,
		)
	);
	ob_start();
	?>
	<section id="initiatives" class="rz-section rz-section--paper rz-reveal" aria-labelledby="rz-fund-title">
		<div class="rz-shell">
			<header class="rz-section__head">
				<div>
					<p class="rz-index"><?php esc_html_e( '03 — Фонд инициатив', 'rubezh' ); ?></p>
					<h2 id="rz-fund-title"><?php esc_html_e( 'Голос, который можно пересчитать', 'rubezh' ); ?></h2>
				</div>
				<div>
					<p><?php esc_html_e( 'Один человек — один голос с этого браузера или аккаунта. Это публичная поддержка, не юридический референдум. Статус выставляет редакция.', 'rubezh' ); ?></p>
					<?php if ( ! $archive ) : ?>
						<a class="rz-section__link" href="<?php echo esc_url( get_post_type_archive_link( 'rubezh_initiative' ) ); ?>"><?php esc_html_e( 'Все инициативы', 'rubezh' ); ?> →</a>
					<?php endif; ?>
				</div>
			</header>
			<?php if ( $query->have_posts() ) : ?>
				<div class="rz-ballots">
					<?php
					$index = 0;
					while ( $query->have_posts() ) :
						$query->the_post();
						$index++;
						$id     = get_the_ID();
						$track  = rubezh_track_label( $id );
						$status = rubezh_initiative_status( $id );
						?>
						<article class="rz-ballot">
							<header>
								<p class="rz-kicker"><?php echo esc_html( rubezh_pad( $index ) . ( $track ? ' · ' . $track : '' ) ); ?></p>
								<p class="rz-status rz-status--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( rubezh_status_label( $status ) ); ?></p>
							</header>
							<h3><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h3>
							<?php rubezh_sample_chip( $id ); ?>
							<p class="rz-dek"><?php echo esc_html( rubezh_excerpt( $id, 26 ) ); ?></p>
							<?php echo rubezh_vote_markup( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
				<?php
				if ( $archive ) {
					rubezh_pagination( $query, $paged );
				}
				?>
			<?php else : ?>
				<p class="rz-empty"><?php esc_html_e( 'Фонд открыт. Опубликуйте первую инициативу, чтобы собрать голоса.', 'rubezh' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}
