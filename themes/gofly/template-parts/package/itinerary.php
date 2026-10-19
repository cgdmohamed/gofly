<?php
/**
 * Day-by-day programme.
 *
 * @package gofly
 */

$days = (array) gofly_field( 'itinerary', false, array() );
if ( ! $days ) {
	return;
}
gofly_package_heading( 'program', 'route', gofly_field( 'itinerary_title', false, __( 'برنامج الرحلة', 'gofly' ) ) );
?>
<div class="stack">
	<?php foreach ( $days as $i => $day ) : ?>
		<div class="card day">
			<div class="day__num"><span><?php esc_html_e( 'اليوم', 'gofly' ); ?></span><b><?php echo (int) $i + 1; ?></b></div>
			<div>
				<h3 class="day__title"><?php echo esc_html( $day['title'] ); ?></h3>
				<div class="day__text"><?php echo esc_html( $day['text'] ); ?></div>
				<?php if ( $day['overnight'] ) : ?>
					<div class="day__night"><?php echo gofly_icon( 'nights_stay' ); // phpcs:ignore ?><?php echo esc_html( $day['overnight'] ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
