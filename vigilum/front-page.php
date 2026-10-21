<?php
/**
 * Editorial homepage — journal + memory + civic fund.
 *
 * @package Vigilum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$featured     = vigilum_query_posts( 1 );
$secondary    = vigilum_query_posts( 3, 1 );
$journal      = vigilum_query_posts( 6, 4 );
$heroes       = vigilum_query_heroes( 4 );
$initiatives  = vigilum_query_initiatives( 3, 'votes' );
$headline     = vigilum_mod( 'vigilum_headline', "Слово против\nтеррора." );
$headline_em  = vigilum_mod( 'vigilum_headline_em', __( 'Память. Инициатива.', 'vigilum' ) );
?>

<main id="primary" class="site-main home-main">
	<section class="edition-hero" aria-labelledby="edition-title">
		<div class="shell edition-hero__grid">
			<div class="edition-hero__copy">
				<p class="kicker"><?php echo esc_html( vigilum_mod( 'vigilum_kicker', __( 'Журнал антитеррора · Том 01', 'vigilum' ) ) ); ?></p>
				<h1 id="edition-title">
					<?php echo nl2br( esc_html( $headline ) ); ?>
					<?php if ( $headline_em ) : ?>
						<em><?php echo esc_html( $headline_em ); ?></em>
					<?php endif; ?>
				</h1>
				<p class="edition-hero__lead"><?php echo esc_html( vigilum_mod( 'vigilum_lead', __( 'Редакция и фонд собирают факты, хранят память героев антитеррора и дают гражданам голосовать за инициативы, которые укрепляют безопасность.', 'vigilum' ) ) ); ?></p>
				<div class="foundation-actions">
					<a class="button button--primary" href="#journal"><?php esc_html_e( 'Читать журнал', 'vigilum' ); ?> <span aria-hidden="true">→</span></a>
					<a class="button button--ghost" href="#memory"><?php esc_html_e( 'Память героев', 'vigilum' ); ?></a>
					<a class="button button--ghost" href="#initiatives"><?php esc_html_e( 'Голосовать', 'vigilum' ); ?></a>
				</div>
				<p class="confidential"><span aria-hidden="true"></span> <?php esc_html_e( 'Конфиденциально. Каждое обращение рассматривается.', 'vigilum' ); ?></p>
			</div>
			<div class="edition-hero__aside" aria-hidden="true">
				<p class="edition-hero__issue"><?php echo esc_html( vigilum_mod( 'vigilum_issue', '01 / 2026' ) ); ?></p>
				<svg class="edition-hero__mark" viewBox="0 0 360 440">
					<path d="M180 18 326 72v120c0 102-58 183-146 230C92 375 34 294 34 192V72L180 18Z" fill="none" stroke="currentColor" stroke-width="1.4"/>
					<path d="M180 62 286 101v91c0 75-40 136-106 176-66-40-106-101-106-176v-91L180 62Z" fill="none" stroke="currentColor" stroke-width="1" opacity=".35"/>
					<line x1="180" y1="118" x2="180" y2="248" stroke="currentColor" stroke-width="1.4"/>
					<circle cx="180" cy="118" r="7" fill="currentColor"/>
				</svg>
				<p class="edition-hero__note"><span>01</span><?php esc_html_e( 'Безопасность начинается с неравнодушия и точного слова.', 'vigilum' ); ?></p>
			</div>
		</div>
	</section>

	<section id="journal" class="section magazine" aria-labelledby="magazine-title">
		<div class="shell">
			<div class="section-head">
				<div>
					<p class="section-number">01 / <?php esc_html_e( 'Журнал', 'vigilum' ); ?></p>
					<h2 id="magazine-title"><?php esc_html_e( 'Свежий выпуск', 'vigilum' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Журнальная сетка: главный материал и три соседних сюжета. Дальше — полная лента.', 'vigilum' ); ?></p>
			</div>

			<?php if ( $featured->have_posts() ) : ?>
				<div class="magazine-spread">
					<?php
					$featured->the_post();
					get_template_part( 'template-parts/card', 'post', array( 'variant' => 'featured' ) );
					wp_reset_postdata();
					?>
					<div class="magazine-spread__rail">
						<?php if ( $secondary->have_posts() ) : ?>
							<?php
							while ( $secondary->have_posts() ) {
								$secondary->the_post();
								get_template_part( 'template-parts/card', 'post', array( 'variant' => 'rail' ) );
							}
							wp_reset_postdata();
							?>
						<?php else : ?>
							<aside class="empty-rail">
								<p><?php esc_html_e( 'Добавьте ещё три публикации — здесь появится журнальная колонка.', 'vigilum' ); ?></p>
							</aside>
						<?php endif; ?>
					</div>
				</div>
			<?php else : ?>
				<div class="empty-state">
					<p><?php esc_html_e( 'Журнал пуст. Создайте записи WordPress — они станут материалами выпуска.', 'vigilum' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section id="memory" class="section memory" aria-labelledby="memory-title">
		<div class="shell">
			<div class="section-head">
				<div>
					<p class="section-number">02 / <?php esc_html_e( 'Память', 'vigilum' ); ?></p>
					<h2 id="memory-title"><?php esc_html_e( 'Герои антитеррора', 'vigilum' ); ?></h2>
				</div>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'vigilum_hero' ) ); ?>"><?php esc_html_e( 'Вся память', 'vigilum' ); ?> <span aria-hidden="true">→</span></a>
			</div>
			<?php if ( $heroes->have_posts() ) : ?>
				<div class="heroes-mosaic">
					<?php
					while ( $heroes->have_posts() ) {
						$heroes->the_post();
						get_template_part( 'template-parts/card', 'hero' );
					}
					wp_reset_postdata();
					?>
				</div>
			<?php else : ?>
				<div class="empty-state empty-state--memory">
					<p><?php esc_html_e( 'Карточки памяти появятся здесь. В админке: «Память героев» → добавить карточку. Публикуйте только проверенные биографии.', 'vigilum' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section id="initiatives" class="section initiatives" aria-labelledby="initiatives-title">
		<div class="shell">
			<div class="section-head">
				<div>
					<p class="section-number">03 / <?php esc_html_e( 'Фонд', 'vigilum' ); ?></p>
					<h2 id="initiatives-title"><?php esc_html_e( 'Гражданские инициативы', 'vigilum' ); ?></h2>
				</div>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'vigilum_initiative' ) ); ?>"><?php esc_html_e( 'Все инициативы', 'vigilum' ); ?> <span aria-hidden="true">→</span></a>
			</div>
			<p class="section-lede"><?php esc_html_e( 'Голос — один на инициативу. Мы считаем поддержку, а не рейтинг людей. Предложения рассматриваются в правовом поле.', 'vigilum' ); ?></p>
			<?php if ( $initiatives->have_posts() ) : ?>
				<div class="initiative-grid">
					<?php
					while ( $initiatives->have_posts() ) {
						$initiatives->the_post();
						get_template_part( 'template-parts/card', 'initiative' );
					}
					wp_reset_postdata();
					?>
				</div>
			<?php else : ?>
				<div class="empty-state">
					<p><?php esc_html_e( 'Фонд инициатив готов. Добавьте первую инициативу в админке — читатели смогут проголосовать.', 'vigilum' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section id="mission" class="section mission" aria-labelledby="mission-title">
		<div class="shell mission__grid">
			<header class="mission__intro">
				<p class="section-number">04 / <?php esc_html_e( 'Миссия', 'vigilum' ); ?></p>
				<h2 id="mission-title"><?php esc_html_e( 'Не оставлять угрозу без ответа', 'vigilum' ); ?></h2>
			</header>
			<div class="mission__body">
				<p class="lede"><?php echo esc_html( vigilum_mod( 'vigilum_mission', __( 'Мы работаем с фактами, бережно относимся к памяти и даём обществу законный канал инициатив в сфере антитеррора.', 'vigilum' ) ) ); ?></p>
				<div class="principles">
					<article>
						<span>А</span>
						<h3><?php esc_html_e( 'Ответственность', 'vigilum' ); ?></h3>
						<p><?php esc_html_e( 'Работаем с фактами и бережно относимся к каждому сообщению.', 'vigilum' ); ?></p>
					</article>
					<article>
						<span>Б</span>
						<h3><?php esc_html_e( 'Законность', 'vigilum' ); ?></h3>
						<p><?php esc_html_e( 'Действуем в правовом поле и взаимодействуем с профильными органами.', 'vigilum' ); ?></p>
					</article>
					<article>
						<span>В</span>
						<h3><?php esc_html_e( 'Память', 'vigilum' ); ?></h3>
						<p><?php esc_html_e( 'Храним истории героев без сенсаций, домыслов и лишней драматизации.', 'vigilum' ); ?></p>
					</article>
				</div>
			</div>
		</div>
	</section>

	<?php if ( $journal->have_posts() ) : ?>
		<section class="section feed" aria-labelledby="feed-title">
			<div class="shell">
				<div class="section-head">
					<div>
						<p class="section-number">05 / <?php esc_html_e( 'Лента', 'vigilum' ); ?></p>
						<h2 id="feed-title"><?php esc_html_e( 'Ещё материалы', 'vigilum' ); ?></h2>
					</div>
					<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' ) ); ?>"><?php esc_html_e( 'Весь журнал', 'vigilum' ); ?> <span aria-hidden="true">→</span></a>
				</div>
				<div class="journal-grid journal-grid--3">
					<?php
					while ( $journal->have_posts() ) {
						$journal->the_post();
						get_template_part( 'template-parts/card', 'post', array( 'variant' => 'standard' ) );
					}
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section id="report" class="report" aria-labelledby="report-title">
		<div class="shell report__grid">
			<div>
				<p class="section-number section-number--light">06 / <?php esc_html_e( 'Обращение', 'vigilum' ); ?></p>
				<h2 id="report-title"><?php esc_html_e( 'Знаете об угрозе?', 'vigilum' ); ?></h2>
				<p><?php echo esc_html( vigilum_mod( 'vigilum_cta_note', __( 'В экстренной ситуации звоните 112. Не проводите расследование самостоятельно.', 'vigilum' ) ) ); ?></p>
			</div>
			<div class="report__action">
				<a class="button button--light" href="mailto:<?php echo esc_attr( vigilum_email() ); ?>?subject=<?php echo rawurlencode( __( 'Конфиденциальное обращение', 'vigilum' ) ); ?>">
					<?php echo esc_html( vigilum_mod( 'vigilum_cta_label', __( 'Написать редакции', 'vigilum' ) ) ); ?> <span aria-hidden="true">→</span>
				</a>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
