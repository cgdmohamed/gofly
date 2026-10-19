<?php
/**
 * Quote request modal holding the Fluent Form chosen in Site Settings.
 *
 * Add a hidden field with the value {embed_post.title} to that form to
 * receive the package name with each request.
 *
 * @package gofly
 *
 * @var array $args { @type array $pkg gofly_package() data. }
 */

$pkg  = $args['pkg'];
$meta = array_filter( array( gofly_duration( $pkg['days'], $pkg['nights'], true ), $pkg['destination'] ) );
?>
<dialog class="modal" data-modal="quote" aria-labelledby="quote-title">
	<button class="icon-btn icon-btn--soft modal__close" type="button" data-modal-close aria-label="<?php esc_attr_e( 'إغلاق', 'gofly' ); ?>"><?php echo gofly_icon( 'close' ); // phpcs:ignore ?></button>
	<div class="eyebrow"><?php esc_html_e( 'طلب عرض سعر', 'gofly' ); ?></div>
	<h2 class="modal__title" id="quote-title"><?php the_title(); ?></h2>
	<div class="modal__meta">
		<?php echo esc_html( implode( ' · ', $meta ) ); ?>
		<?php if ( $pkg['price'] ) : ?>
			· <?php esc_html_e( 'من', 'gofly' ); ?> <?php echo gofly_price( $pkg['price'] ); // phpcs:ignore ?> <?php esc_html_e( 'للفرد', 'gofly' ); ?>
		<?php endif; ?>
	</div>
	<div class="modal__form form-skin"><?php echo gofly_form( gofly_option( 'quote_form_id' ) ); // phpcs:ignore ?></div>
	<div class="modal__note"><?php esc_html_e( 'سنتواصل معك عبر واتساب خلال دقائق.', 'gofly' ); ?></div>
</dialog>
