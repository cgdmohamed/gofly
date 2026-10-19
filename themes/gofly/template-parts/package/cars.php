<?php
/**
 * Available cars by group size.
 *
 * @package gofly
 */

$cars = (array) gofly_field( 'cars', false, array() );
if ( ! $cars ) {
	return;
}
gofly_package_heading( 'cars', 'directions_car', gofly_field( 'cars_title', false, __( 'السيارات المتاحة', 'gofly' ) ) );
?>
<div class="grid grid--240">
	<?php foreach ( $cars as $car ) : ?>
		<div class="card car">
			<div class="car__media"><?php echo gofly_image( $car['image'], 'medium_large', array( 'loading' => 'lazy' ) ); // phpcs:ignore ?></div>
			<div class="car__body">
				<b><?php echo esc_html( $car['capacity'] ); ?></b>
				<div class="car__models" dir="auto"><?php echo esc_html( $car['models'] ); ?></div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
