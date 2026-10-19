<?php
/**
 * Traveller testimonials.
 *
 * @package gofly
 */

$items = (array) gofly_field( 'testi_items', false, array() );
if ( ! $items ) {
	return;
}
?>
<section class="section">
	<?php
	get_template_part(
		'template-parts/components/section-head',
		null,
		array(
			'eyebrow' => gofly_field( 'testi_eyebrow' ),
			'title'   => gofly_field( 'testi_title' ),
		)
	);
	?>
	<div class="grid grid--280">
		<?php foreach ( $items as $item ) : ?>
			<figure class="card testimonial">
				<?php echo gofly_stars( $item['rating'] ?: 5 ); // phpcs:ignore ?>
				<blockquote class="testimonial__quote"><?php echo esc_html( $item['quote'] ); ?></blockquote>
				<figcaption class="testimonial__author">
					<span class="testimonial__avatar"><?php echo gofly_image( $item['avatar'], 'thumbnail', array( 'loading' => 'lazy', 'alt' => '' ) ); // phpcs:ignore ?></span>
					<span><b><?php echo esc_html( $item['name'] ); ?></b><small><?php echo esc_html( $item['trip'] ); ?></small></span>
				</figcaption>
			</figure>
		<?php endforeach; ?>
	</div>
</section>
