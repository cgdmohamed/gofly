<?php
/**
 * Centered "view all" button under a section's grid.
 *
 * @package gofly
 *
 * @var array $args {
 *     @type string $url   Link.
 *     @type string $title Button label.
 * }
 */

if ( empty( $args['url'] ) ) {
	return;
}
?>
<div class="section-more">
	<a class="btn btn--outline btn--lg" href="<?php echo esc_url( $args['url'] ); ?>"><?php echo esc_html( $args['title'] ); ?><?php echo gofly_icon( 'arrow_back' ); // phpcs:ignore ?></a>
</div>
