<?php
/**
 * Destination facts.
 *
 * @package gofly
 */

$facts = (array) gofly_field( 'facts', false, array() );
if ( ! $facts ) {
	return;
}
gofly_package_heading( 'info', 'info', gofly_field( 'facts_title', false, __( 'معلومات عن الوجهة', 'gofly' ) ) );
?>
<div class="grid grid--180">
	<?php foreach ( $facts as $fact ) : ?>
		<div class="card fact"><small><?php echo esc_html( $fact['label'] ); ?></small><b><?php echo esc_html( $fact['value'] ); ?></b></div>
	<?php endforeach; ?>
</div>
