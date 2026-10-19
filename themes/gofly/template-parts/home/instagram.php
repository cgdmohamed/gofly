<?php
/**
 * Instagram wall: a handful of photos linking to the profile.
 *
 * @package gofly
 */

$images = (array) gofly_field( 'insta_images', false, array() );
$url    = gofly_field( 'insta_url' );
if ( ! $images ) {
	return;
}
?>
<section class="section">
	<div class="section-head">
		<div>
			<div class="eyebrow"><?php echo esc_html( gofly_field( 'insta_eyebrow' ) ); ?></div>
			<h2 class="section-title"><?php echo esc_html( gofly_field( 'insta_title' ) ); ?></h2>
		</div>
		<?php if ( $url ) : ?>
			<a class="link-more" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo gofly_brand_icon( 'instagram' ); // phpcs:ignore ?><span dir="ltr"><?php echo esc_html( gofly_field( 'insta_handle' ) ); ?></span></a>
		<?php endif; ?>
	</div>
	<div class="insta">
		<?php foreach ( $images as $image ) : ?>
			<a class="insta__item" href="<?php echo esc_url( $url ? $url : '#' ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'إنستغرام', 'gofly' ); ?>">
				<?php echo gofly_image( $image, 'gofly-square', array( 'loading' => 'lazy', 'alt' => '' ) ); // phpcs:ignore ?>
				<span class="insta__overlay"><?php echo gofly_brand_icon( 'instagram' ); // phpcs:ignore ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
