<?php
/**
 * Package media slider (Swiper) with thumbnails; the optional video sits
 * second, after the cover photo.
 *
 * @package gofly
 */

$images = (array) gofly_field( 'gallery', false, array() );
if ( ! $images && has_post_thumbnail() ) {
	$images = array( get_post_thumbnail_id() );
}
$slides = array_map( fn( $img ) => array( 'type' => 'image', 'image' => $img ), $images );
$video  = gofly_field( 'video' );
if ( $video ) {
	array_splice( $slides, 1, 0, array( array( 'type' => 'video', 'url' => is_array( $video ) ? $video['url'] : $video, 'poster' => gofly_field( 'video_poster' ) ) ) );
}
if ( ! $slides ) {
	return;
}
?>
<div class="pkg-gallery">
	<div class="swiper gMain" dir="rtl">
		<div class="swiper-wrapper">
			<?php foreach ( $slides as $i => $slide ) : ?>
				<div class="swiper-slide">
					<?php if ( 'video' === $slide['type'] ) : ?>
						<video controls playsinline preload="metadata" src="<?php echo esc_url( $slide['url'] ); ?>"<?php echo $slide['poster'] ? ' poster="' . esc_url( wp_get_attachment_image_url( $slide['poster']['ID'], 'large' ) ) . '"' : ''; ?>></video>
					<?php else : ?>
						<?php
						// Full size + srcset: the browser picks the sharpest file for the slider width and screen density.
						echo gofly_image( $slide['image'], 'full', array( 'sizes' => '(max-width: 1240px) 100vw, 1200px' ) + ( $i ? array( 'loading' => 'lazy' ) : array( 'fetchpriority' => 'high' ) ) ); // phpcs:ignore
						?>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( count( $slides ) > 1 ) : ?>
			<div class="swiper-button-prev" aria-label="<?php esc_attr_e( 'السابق', 'gofly' ); ?>"></div>
			<div class="swiper-button-next" aria-label="<?php esc_attr_e( 'التالي', 'gofly' ); ?>"></div>
			<div class="swiper-pagination"></div>
			<div class="gCount" dir="ltr">1 / <?php echo count( $slides ); ?></div>
		<?php endif; ?>
	</div>
	<?php if ( count( $slides ) > 1 ) : ?>
		<div class="swiper gThumbs" dir="rtl">
			<div class="swiper-wrapper">
				<?php foreach ( $slides as $slide ) : ?>
					<div class="swiper-slide">
						<?php if ( 'video' === $slide['type'] ) : ?>
							<?php echo $slide['poster'] ? gofly_image( $slide['poster'], 'medium', array( 'alt' => '', 'sizes' => '128px' ) ) : '<span class="gThumbs__blank"></span>'; // phpcs:ignore ?>
							<span class="gThumbs__play"><?php echo gofly_icon( 'play_circle', 'ms--fill' ); // phpcs:ignore ?></span>
						<?php else : ?>
							<?php echo gofly_image( $slide['image'], 'medium', array( 'loading' => 'lazy', 'sizes' => '128px' ) ); // phpcs:ignore ?>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
