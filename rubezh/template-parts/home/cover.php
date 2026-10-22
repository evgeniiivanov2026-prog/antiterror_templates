<?php
/**
 * Кинематографическая обложка выпуска.
 *
 * @package Rubezh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$issue  = rubezh_option( 'rubezh_issue', '№ 04 / осень 2027' );
$kicker = rubezh_option( 'rubezh_kicker', 'Антитеррор · Память · Гражданское действие' );
$title  = rubezh_option( 'rubezh_title', 'Память сильнее страха' );
$lead   = rubezh_option( 'rubezh_lead', 'Журнал фиксирует факты, хранит имена героев антитеррора и собирает гражданские инициативы, которые делают среду безопаснее.' );

$latest = rubezh_query_posts( array( 'posts_per_page' => 3 ) );
$top    = rubezh_query_initiatives( 1 );
?>
<section class="cover" aria-labelledby="cover-title">
	<div class="shell cover__grid">
		<div class="cover__copy reveal">
			<p class="kicker kicker--on-dark"><?php echo esc_html( $kicker ); ?></p>
			<p class="cover__issue"><?php echo esc_html( $issue ); ?></p>
			<h1 id="cover-title"><?php echo esc_html( $title ); ?><em><?php esc_html_e( 'Действуем открыто.', 'rubezh' ); ?></em></h1>
			<p class="cover__lead"><?php echo esc_html( $lead ); ?></p>
			<div class="cover__actions">
				<a class="btn btn--burgundy" href="#journal"><?php esc_html_e( 'Читать выпуск', 'rubezh' ); ?> <span aria-hidden="true">→</span></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( rubezh_archive_link( 'hero' ) ); ?>"><?php esc_html_e( 'Стена памяти', 'rubezh' ); ?></a>
				<a class="btn btn--text" href="<?php echo esc_url( rubezh_archive_link( 'initiative' ) ); ?>"><?php esc_html_e( 'Фонд инициатив', 'rubezh' ); ?> <span aria-hidden="true">↘</span></a>
			</div>
			<p class="cover__note"><span class="pulse" aria-hidden="true"></span><?php esc_html_e( 'Без сенсаций. Без оперативных деталей. В угрозе — 112.', 'rubezh' ); ?></p>
		</div>
		<aside class="cover__rail reveal" aria-label="<?php esc_attr_e( 'Сейчас в выпуске', 'rubezh' ); ?>">
			<p class="rail-label"><?php esc_html_e( 'Сейчас в выпуске', 'rubezh' ); ?></p>
			<?php if ( $latest->have_posts() ) : ?>
				<ol class="cover-feed">
					<?php
					$i = 1;
					while ( $latest->have_posts() ) :
						$latest->the_post();
						?>
						<li>
							<span><?php echo esc_html( str_pad( (string) $i, 2, '0', STR_PAD_LEFT ) ); ?></span>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</li>
						<?php
						$i++;
					endwhile;
					wp_reset_postdata();
					?>
				</ol>
			<?php endif; ?>
			<?php if ( $top->have_posts() ) : $top->the_post(); ?>
				<div class="cover-ballot">
					<p class="rail-label"><?php esc_html_e( 'Лидер фонда', 'rubezh' ); ?></p>
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					<p><?php echo esc_html( (int) get_post_meta( get_the_ID(), '_rubezh_votes', true ) ); ?> <?php esc_html_e( 'голосов', 'rubezh' ); ?></p>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php endif; ?>
		</aside>
	</div>
</section>
