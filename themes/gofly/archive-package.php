<?php
/**
 * Packages archive with sidebar filters (off-canvas on mobile).
 *
 * All packages are rendered server-side; assets/js/packages.js filters,
 * sorts and paginates them instantly. Links such as ?dest=paris or
 * ?cat=honeymoon open the page pre-filtered.
 *
 * @package gofly
 */

get_header();

$gofly_cats   = get_terms( array( 'taxonomy' => 'package_category', 'hide_empty' => true ) );
$gofly_dests  = get_terms( array( 'taxonomy' => 'destination', 'hide_empty' => true ) );
$gofly_prices = array_filter( array_map( fn( $p ) => (float) gofly_field( 'price', $p->ID, 0 ), $wp_query->posts ) );
$gofly_max    = $gofly_prices ? (int) ( ceil( max( $gofly_prices ) / 1000 ) * 1000 ) : 10000;
$gofly_min    = $gofly_prices ? (int) ( floor( min( $gofly_prices ) / 1000 ) * 1000 ) : 0;
$gofly_total  = (int) $wp_query->found_posts;
$gofly_durs   = array(
	'all' => __( 'أي مدة', 'gofly' ),
	'3-5' => __( '3 – 5 أيام', 'gofly' ),
	'6-7' => __( '6 – 7 أيام', 'gofly' ),
	'8-'  => __( '8 أيام فأكثر', 'gofly' ),
);
$gofly_sorts  = array(
	'new'  => __( 'الأحدث', 'gofly' ),
	'low'  => __( 'الأقل سعرًا', 'gofly' ),
	'high' => __( 'الأعلى سعرًا', 'gofly' ),
	'dur'  => __( 'الأقصر مدة', 'gofly' ),
);

get_template_part( 'template-parts/components/page-hero', null, array( 'title' => gofly_option( 'packages_title', __( 'باقات الرحلات', 'gofly' ) ) ) );
?>
<section class="section section--archive" data-packages data-per-page="<?php echo esc_attr( gofly_option( 'packages_per_page', 9 ) ); ?>">
	<button class="pf-open" type="button" data-pf-open>
		<?php echo gofly_icon( 'tune' ); // phpcs:ignore ?><?php esc_html_e( 'تصفية الباقات', 'gofly' ); ?><span class="pf-open__count" data-pf-count><?php echo (int) $gofly_total; ?></span>
	</button>

	<div class="pf-layout">
		<div class="pf-overlay" data-pf-close></div>
		<aside class="card pf-side" aria-label="<?php esc_attr_e( 'تصفية الباقات', 'gofly' ); ?>">
			<div class="pf-side__head">
				<b><?php echo gofly_icon( 'tune', 'is-gold' ); // phpcs:ignore ?><?php esc_html_e( 'تصفية الباقات', 'gofly' ); ?></b>
				<button class="icon-btn icon-btn--soft pf-only-mobile" type="button" data-pf-close aria-label="<?php esc_attr_e( 'إغلاق', 'gofly' ); ?>"><?php echo gofly_icon( 'close' ); // phpcs:ignore ?></button>
			</div>

			<?php if ( $gofly_cats && ! is_wp_error( $gofly_cats ) ) : ?>
				<div class="pf-group">
					<div class="pf-group__title"><?php echo gofly_icon( 'category', 'is-gold' ); // phpcs:ignore ?><?php esc_html_e( 'الفئة', 'gofly' ); ?></div>
					<div class="chips" data-filter="cat">
						<button class="chip-btn is-active" type="button" data-value="all" aria-pressed="true"><?php echo gofly_icon( 'apps' ); // phpcs:ignore ?><?php esc_html_e( 'الكل', 'gofly' ); ?></button>
						<?php foreach ( $gofly_cats as $gofly_term ) : ?>
							<button class="chip-btn" type="button" data-value="<?php echo esc_attr( $gofly_term->slug ); ?>" aria-pressed="false"><?php echo gofly_icon( gofly_field( 'icon', $gofly_term ) ); // phpcs:ignore ?><?php echo esc_html( $gofly_term->name ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $gofly_dests && ! is_wp_error( $gofly_dests ) ) : ?>
				<div class="pf-group">
					<div class="pf-group__title"><?php echo gofly_icon( 'place', 'is-gold' ); // phpcs:ignore ?><?php esc_html_e( 'الوجهة', 'gofly' ); ?></div>
					<div class="radio-list" data-filter="dest" role="radiogroup">
						<button class="radio-btn is-active" type="button" role="radio" data-value="all" aria-checked="true"><span class="radio-btn__dot"></span><?php esc_html_e( 'كل الوجهات', 'gofly' ); ?></button>
						<?php foreach ( $gofly_dests as $gofly_term ) : ?>
							<button class="radio-btn" type="button" role="radio" data-value="<?php echo esc_attr( $gofly_term->slug ); ?>" aria-checked="false"><span class="radio-btn__dot"></span><?php echo esc_html( $gofly_term->name ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="pf-group">
				<div class="pf-group__title"><?php echo gofly_icon( 'schedule', 'is-gold' ); // phpcs:ignore ?><?php esc_html_e( 'المدة', 'gofly' ); ?></div>
				<div class="chips" data-filter="dur">
					<?php foreach ( $gofly_durs as $gofly_value => $gofly_label ) : ?>
						<button class="chip-btn<?php echo 'all' === $gofly_value ? ' is-active' : ''; ?>" type="button" data-value="<?php echo esc_attr( $gofly_value ); ?>" aria-pressed="<?php echo 'all' === $gofly_value ? 'true' : 'false'; ?>"><?php echo esc_html( $gofly_label ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $gofly_prices ) : ?>
				<div class="pf-group">
					<label class="pf-group__title" for="pf-price"><?php echo gofly_icon( 'payments', 'is-gold' ); // phpcs:ignore ?><?php esc_html_e( 'السعر', 'gofly' ); ?></label>
					<b class="pf-price"><span data-pf-max><?php echo esc_html( number_format( $gofly_max ) ); ?></span> <?php echo gofly_riyal(); // phpcs:ignore ?></b>
					<input id="pf-price" class="pf-range" type="range" min="<?php echo (int) $gofly_min; ?>" max="<?php echo (int) $gofly_max; ?>" step="100" value="<?php echo (int) $gofly_max; ?>" data-filter-price>
				</div>
			<?php endif; ?>

			<div class="pf-group pf-group--actions">
				<button class="btn btn--gold btn--block pf-only-mobile" type="button" data-pf-close><?php esc_html_e( 'عرض', 'gofly' ); ?> <span data-pf-count><?php echo (int) $gofly_total; ?></span> <?php esc_html_e( 'باقة', 'gofly' ); ?></button>
				<button class="btn btn--outline btn--block" type="button" data-pf-reset><?php echo gofly_icon( 'restart_alt' ); // phpcs:ignore ?><?php esc_html_e( 'إعادة ضبط', 'gofly' ); ?></button>
			</div>
		</aside>

		<div class="pf-main">
			<div class="pf-bar">
				<span aria-live="polite"><?php esc_html_e( 'عرض', 'gofly' ); ?> <b data-pf-count><?php echo (int) $gofly_total; ?></b> <?php esc_html_e( 'باقة', 'gofly' ); ?></span>
				<div class="pf-sort" role="group" aria-label="<?php esc_attr_e( 'الترتيب', 'gofly' ); ?>">
					<span class="pf-sort__label"><?php esc_html_e( 'الترتيب', 'gofly' ); ?></span>
					<div class="segmented" data-sort>
						<?php foreach ( $gofly_sorts as $gofly_value => $gofly_label ) : ?>
							<button type="button" class="<?php echo 'new' === $gofly_value ? 'is-active' : ''; ?>" data-value="<?php echo esc_attr( $gofly_value ); ?>" aria-pressed="<?php echo 'new' === $gofly_value ? 'true' : 'false'; ?>"><?php echo esc_html( $gofly_label ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="grid grid--fill-280" data-pf-items>
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/components/package-card' );
				endwhile;
				?>
			</div>

			<?php
			get_template_part(
				'template-parts/components/empty-state',
				null,
				array(
					'title' => __( 'لا توجد باقات مطابقة', 'gofly' ),
					'text'  => __( 'جرّب تغيير الفلاتر أو توسيع نطاق السعر.', 'gofly' ),
					'attrs' => $gofly_total ? 'data-pf-empty hidden' : 'data-pf-empty',
					'reset' => $gofly_total ? __( 'إعادة ضبط الفلاتر', 'gofly' ) : '',
				)
			);
			?>
			<nav class="pagination" data-pf-pages aria-label="<?php esc_attr_e( 'التنقل بين الصفحات', 'gofly' ); ?>" hidden></nav>
		</div>
	</div>
</section>
<?php
get_footer();
