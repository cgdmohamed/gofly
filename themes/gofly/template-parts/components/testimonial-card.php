<?php
/**
 * Testimonial card: stars, quote, traveller photo, name and trip.
 *
 * @package gofly
 *
 * @var array $args {
 *     @type int $post_id Testimonial post ID.
 * }
 */

$pid = (int) ( $args['post_id'] ?? get_the_ID() );
?>
<figure class="card testimonial">
	<?php echo gofly_stars( gofly_field( 'rating', $pid ) ?: 5 ); // phpcs:ignore ?>
	<blockquote class="testimonial__quote"><?php echo esc_html( gofly_field( 'quote', $pid ) ); ?></blockquote>
	<figcaption class="testimonial__author">
		<span class="testimonial__avatar"><?php echo get_the_post_thumbnail( $pid, 'thumbnail', array( 'loading' => 'lazy', 'alt' => '' ) ); ?></span>
		<span><b><?php echo esc_html( get_the_title( $pid ) ); ?></b><small><?php echo esc_html( gofly_field( 'trip', $pid ) ); ?></small></span>
	</figcaption>
</figure>
