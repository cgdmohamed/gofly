<?php
/**
 * Hotel tiers (display only; the team assigns the hotel).
 *
 * @package gofly
 */

$hotels = (array) gofly_field( 'hotels', false, array() );
if ( ! $hotels ) {
	return;
}
gofly_package_heading( 'hotels', 'hotel', gofly_field( 'hotels_title', false, __( 'فنادق الإقامة', 'gofly' ) ), gofly_field( 'hotels_note' ) );
?>
<div class="grid grid--240">
	<?php foreach ( $hotels as $hotel ) : ?>
		<div class="card card--pad hotel">
			<b class="hotel__tier"><?php echo esc_html( $hotel['tier'] ); ?></b>
			<?php echo gofly_stars( $hotel['stars'] ); // phpcs:ignore ?>
			<div class="hotel__name" dir="auto"><?php echo esc_html( $hotel['name'] ); ?></div>
			<?php if ( $hotel['note'] ) : ?>
				<div class="hotel__note"><?php echo esc_html( $hotel['note'] ); ?></div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
