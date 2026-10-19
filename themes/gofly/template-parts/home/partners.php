<?php
/**
 * Partner logos marquee (Site Settings → Partners).
 *
 * @package gofly
 */

$logos = (array) gofly_option( 'partners', array() );
if ( ! $logos ) {
	return;
}
?>
<section class="partners" aria-label="<?php echo esc_attr( gofly_option( 'partners_title', __( 'شركاؤنا في النجاح', 'gofly' ) ) ); ?>">
	<div class="partners__title"><?php echo esc_html( gofly_option( 'partners_title', __( 'شركاؤنا في النجاح', 'gofly' ) ) ); ?></div>
	<div class="partners__viewport">
		<div class="partners__track" dir="ltr">
			<?php foreach ( array( false, true ) as $is_copy ) : // Two copies make the loop seamless. ?>
				<?php foreach ( $logos as $logo ) : ?>
					<div class="partners__logo"<?php echo $is_copy ? ' aria-hidden="true"' : ''; ?>>
						<?php echo gofly_image( $logo, 'medium', array( 'loading' => 'lazy', 'alt' => $is_copy ? '' : ( $logo['alt'] ?: $logo['title'] ) ) ); // phpcs:ignore ?>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
