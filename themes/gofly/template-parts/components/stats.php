<?php
/**
 * Company stats from Site Settings → Stats.
 *
 * Numbers count up when scrolled into view (main.js); the final value is
 * always in the markup for SEO and no-JS visitors.
 *
 * @package gofly
 *
 * @var array $args {
 *     @type string $variant 'hero' (first 3, on navy) | 'band' (white card) | 'navy' (navy block).
 * }
 */

$variant = $args['variant'] ?? 'band';
$stats   = (array) gofly_option( 'stats', array() );
if ( 'hero' === $variant ) {
	$stats = array_slice( $stats, 0, 3 );
}
if ( ! $stats ) {
	return;
}
?>
<div class="stats stats--<?php echo esc_attr( $variant ); ?>">
	<?php foreach ( $stats as $stat ) : ?>
		<div class="stats__item">
			<div class="stats__value" data-count><?php echo esc_html( $stat['value'] ); ?></div>
			<div class="stats__label"><?php echo esc_html( $stat['label'] ); ?></div>
		</div>
	<?php endforeach; ?>
</div>
