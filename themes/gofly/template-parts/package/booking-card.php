<?php
/**
 * Sticky price / request card beside the programme.
 *
 * @package gofly
 *
 * @var array $args { @type array $pkg gofly_package() data. }
 */

$pkg     = $args['pkg'];
$summary = array(
	__( 'الوجهة', 'gofly' )      => $pkg['destination'],
	__( 'المدة', 'gofly' )       => gofly_duration( $pkg['days'], $pkg['nights'], true ),
	__( 'فئة الفندق', 'gofly' )  => gofly_field( 'hotel_category', false, __( 'حسب التوفر', 'gofly' ) ),
	__( 'عدد الأفراد', 'gofly' ) => gofly_field( 'group_size' ),
);
?>
<aside class="card booking-card">
	<?php if ( $pkg['price'] ) : ?>
		<span class="booking-card__from"><?php esc_html_e( 'ابتداءً من', 'gofly' ); ?></span>
		<div class="booking-card__price"><b><?php echo esc_html( number_format( $pkg['price'] ) ); ?></b> <?php echo gofly_riyal(); // phpcs:ignore ?> <span><?php esc_html_e( 'للفرد', 'gofly' ); ?></span></div>
		<?php if ( $pkg['old_price'] ) : ?>
			<div class="booking-card__deal">
				<s><?php echo gofly_price( $pkg['old_price'] ); // phpcs:ignore ?></s>
				<span class="badge badge--red"><?php echo esc_html( sprintf( /* translators: %d: percent */ __( 'خصم %d%%', 'gofly' ), $pkg['discount'] ) ); ?></span>
				<span class="booking-card__save"><?php esc_html_e( 'وفّر', 'gofly' ); ?> <?php echo gofly_price( $pkg['old_price'] - $pkg['price'] ); // phpcs:ignore ?></span>
			</div>
		<?php endif; ?>
		<hr class="divider">
	<?php endif; ?>

	<b class="booking-card__title"><?php esc_html_e( 'احجز هذا البرنامج', 'gofly' ); ?></b>
	<div class="booking-card__hint"><?php echo esc_html( gofly_option( 'quote_hint', __( 'السعر النهائي حسب فئة الفندق وعدد الأفراد، شامل الضرائب.', 'gofly' ) ) ); ?></div>

	<dl class="booking-card__summary">
		<?php foreach ( array_filter( $summary ) as $label => $value ) : ?>
			<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div>
		<?php endforeach; ?>
	</dl>

	<?php if ( gofly_field( 'cancellation_note' ) ) : ?>
		<div class="notice-box"><?php echo gofly_icon( 'info' ); // phpcs:ignore ?><span><b><?php esc_html_e( 'سياسة الإلغاء:', 'gofly' ); ?></b> <?php echo esc_html( gofly_field( 'cancellation_note' ) ); ?></span></div>
	<?php endif; ?>

	<button class="btn btn--gold btn--block btn--lg" type="button" data-modal-open="quote"><?php echo gofly_icon( 'request_quote' ); // phpcs:ignore ?><?php esc_html_e( 'اطلب عرض السعر', 'gofly' ); ?></button>
	<div class="booking-card__support"><?php esc_html_e( 'دعم بالعربية على مدار الساعة', 'gofly' ); ?></div>
</aside>
