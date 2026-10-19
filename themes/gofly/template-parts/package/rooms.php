<?php
/**
 * Room split by number of travellers.
 *
 * @package gofly
 */

$rooms = (array) gofly_field( 'rooms', false, array() );
if ( ! $rooms ) {
	return;
}
gofly_package_heading( 'rooms', 'bed', gofly_field( 'rooms_title', false, __( 'تقسيم الغرف', 'gofly' ) ) );
?>
<div class="grid grid--220">
	<?php foreach ( $rooms as $room ) : ?>
		<div class="card room">
			<span class="icon-tile icon-tile--sm"><?php echo gofly_icon( 'person' ); // phpcs:ignore ?></span>
			<div><b><?php echo esc_html( $room['label'] ); ?></b><span><?php echo esc_html( $room['split'] ); ?></span></div>
		</div>
	<?php endforeach; ?>
</div>
<?php if ( gofly_field( 'rooms_note' ) ) : ?>
	<p class="pkg-footnote"><?php echo esc_html( gofly_field( 'rooms_note' ) ); ?></p>
<?php endif; ?>
