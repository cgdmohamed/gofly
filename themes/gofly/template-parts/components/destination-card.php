<?php
/**
 * Destination card: full-bleed photo with name and subtitle.
 *
 * @package gofly
 *
 * @var array $args {
 *     @type WP_Term $term Destination term.
 *     @type string  $meta Line under the name (defaults to the term subtitle).
 * }
 */

$term = $args['term'] ?? null;
if ( ! $term instanceof WP_Term ) {
	return;
}
$meta = $args['meta'] ?? gofly_field( 'subtitle', $term );
?>
<a class="dest-card media-zoom" href="<?php echo esc_url( gofly_field( 'link', $term, get_term_link( $term ) ) ); ?>">
	<?php echo gofly_image( gofly_field( 'image', $term ), 'gofly-tall', array( 'loading' => 'lazy', 'alt' => $term->name ) ); // phpcs:ignore ?>
	<div class="dest-card__caption">
		<div class="dest-card__name"><?php echo gofly_icon( 'location_on' ); // phpcs:ignore ?><?php echo esc_html( $term->name ); ?></div>
		<?php if ( $meta ) : ?>
			<div class="dest-card__meta"><?php echo esc_html( $meta ); ?></div>
		<?php endif; ?>
	</div>
</a>
