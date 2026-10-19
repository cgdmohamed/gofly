<?php
/**
 * Home hero: headline, CTA, stats and the photo collage.
 *
 * @package gofly
 */

$title     = gofly_field( 'hero_title', false, __( 'سافر إلى حيث تحلم أن تكون', 'gofly' ) );
$highlight = gofly_field( 'hero_highlight', false, __( 'تحلم', 'gofly' ) );
$video     = gofly_field( 'hero_video' );
$images    = array(
	'arch'   => gofly_field( 'hero_image_main' ),
	'card'   => gofly_field( 'hero_image_card' ),
	'circle' => gofly_field( 'hero_image_circle' ),
);

$title_html = esc_html( $title );
if ( $highlight && false !== mb_strpos( $title, $highlight ) ) {
	$title_html = preg_replace( '/' . preg_quote( esc_html( $highlight ), '/' ) . '/u', '<span class="text-gold">$0</span>', $title_html, 1 );
}
?>
<section class="hero">
	<div class="hero__content">
		<?php if ( gofly_field( 'hero_badge' ) ) : ?>
			<div class="hero__badge"><?php echo gofly_icon( 'public' ); // phpcs:ignore ?><?php echo esc_html( gofly_field( 'hero_badge' ) ); ?></div>
		<?php endif; ?>
		<h1 class="hero__title"><?php echo $title_html; // phpcs:ignore ?></h1>
		<?php if ( gofly_field( 'hero_text' ) ) : ?>
			<p class="hero__text"><?php echo esc_html( gofly_field( 'hero_text' ) ); ?></p>
		<?php endif; ?>
		<div class="hero__actions">
			<?php echo gofly_link( gofly_field( 'hero_button' ), 'btn btn--gold btn--lg', 'explore' ); // phpcs:ignore ?>
			<?php if ( $video ) : ?>
				<button class="hero__play" type="button" data-video-open>
					<span class="hero__play-icon"><?php echo gofly_icon( 'play_arrow', 'ms--fill' ); // phpcs:ignore ?></span><?php echo esc_html( gofly_field( 'hero_video_label', false, __( 'شاهد الفيديو', 'gofly' ) ) ); ?>
				</button>
			<?php endif; ?>
		</div>
		<?php get_template_part( 'template-parts/components/stats', null, array( 'variant' => 'hero' ) ); ?>
	</div>

	<div class="hero__collage" aria-hidden="true">
		<svg class="hero__path" viewBox="0 0 400 580"><path d="M20 520 C 60 300, 260 380, 300 200 S 380 40, 390 20" /></svg>
		<?php foreach ( $images as $key => $image ) : ?>
			<?php if ( $image ) : ?>
				<div class="hero__img hero__img--<?php echo esc_attr( $key ); ?>"><?php echo gofly_image( $image, 'arch' === $key ? 'gofly-tall' : 'gofly-square', array( 'alt' => '' ) ); // phpcs:ignore ?></div>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ( gofly_field( 'hero_rating' ) ) : ?>
			<div class="hero__float hero__float--rating float-a">
				<span class="icon-tile icon-tile--cream"><?php echo gofly_icon( 'star', 'ms--fill' ); // phpcs:ignore ?></span>
				<span><b><?php echo esc_html( gofly_field( 'hero_rating' ) ); ?></b><small><?php echo esc_html( gofly_field( 'hero_rating_text' ) ); ?></small></span>
			</div>
		<?php endif; ?>
		<?php if ( gofly_field( 'hero_tag' ) ) : ?>
			<div class="hero__float hero__float--tag float-b"><?php echo gofly_icon( 'flight_takeoff' ); // phpcs:ignore ?><?php echo esc_html( gofly_field( 'hero_tag' ) ); ?></div>
		<?php endif; ?>
	</div>

	<?php if ( $video ) : ?>
		<dialog class="video-dialog" data-video-dialog>
			<button class="icon-btn icon-btn--soft video-dialog__close" type="button" data-video-close aria-label="<?php esc_attr_e( 'إغلاق', 'gofly' ); ?>"><?php echo gofly_icon( 'close' ); // phpcs:ignore ?></button>
			<template><?php echo wp_oembed_get( $video ) ?: '<video src="' . esc_url( $video ) . '" controls autoplay playsinline></video>'; // phpcs:ignore ?></template>
			<div class="video-dialog__frame"></div>
		</dialog>
	<?php endif; ?>
</section>
