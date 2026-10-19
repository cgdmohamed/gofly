<?php
/**
 * Suggested (halal) restaurants.
 *
 * @package gofly
 */

$places = (array) gofly_field( 'restaurants', false, array() );
if ( ! $places ) {
	return;
}
gofly_package_heading( 'food', 'restaurant', gofly_field( 'restaurants_title', false, __( 'مطاعم حلال مقترحة', 'gofly' ) ), gofly_field( 'restaurants_note' ) );
?>
<div class="grid grid--240">
	<?php foreach ( $places as $place ) : ?>
		<div class="card restaurant">
			<div><b dir="auto"><?php echo esc_html( $place['name'] ); ?></b><small><?php echo esc_html( $place['cuisine'] ); ?></small></div>
			<?php if ( $place['rating'] ) : ?>
				<span class="restaurant__rating"><?php echo gofly_icon( 'star', 'ms--fill' ); // phpcs:ignore ?><?php echo esc_html( $place['rating'] ); ?></span>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
