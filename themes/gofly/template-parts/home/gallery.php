<?php
/**
 * Photo mosaic.
 *
 * @package gofly
 */

$images = (array) gofly_field( 'gallery_images', false, array() );
if ( ! $images ) {
	return;
}
?>
<section class="section">
	<?php
	get_template_part(
		'template-parts/components/section-head',
		null,
		array(
			'eyebrow' => gofly_field( 'gallery_eyebrow' ),
			'title'   => gofly_field( 'gallery_title' ),
		)
	);
	?>
	<div class="mosaic">
		<?php foreach ( $images as $image ) : ?>
			<figure class="mosaic__item media-zoom"><?php echo gofly_image( $image, 'gofly-tall', array( 'loading' => 'lazy' ) ); // phpcs:ignore ?></figure>
		<?php endforeach; ?>
	</div>
</section>
