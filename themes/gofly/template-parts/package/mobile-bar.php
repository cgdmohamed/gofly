<?php
/**
 * Fixed bottom bar on phones and tablets.
 *
 * @package gofly
 *
 * @var array $args { @type array $pkg gofly_package() data. }
 */

$pkg = $args['pkg'];
?>
<div class="mobile-bar">
	<div class="mobile-bar__info">
		<small><?php echo esc_html( implode( ' · ', array_filter( array( $pkg['destination'], gofly_duration( $pkg['days'], $pkg['nights'], true ) ) ) ) ); ?></small>
		<?php if ( $pkg['price'] ) : ?>
			<div>
				<?php if ( $pkg['old_price'] ) : ?>
					<s><?php echo esc_html( number_format( $pkg['old_price'] ) ); ?></s>
				<?php endif; ?>
				<b><?php esc_html_e( 'من', 'gofly' ); ?> <?php echo gofly_price( $pkg['price'] ); // phpcs:ignore ?></b>
			</div>
		<?php endif; ?>
	</div>
	<button class="btn btn--gold" type="button" data-modal-open="quote"><?php echo gofly_icon( 'request_quote' ); // phpcs:ignore ?><?php esc_html_e( 'طلب الباقة', 'gofly' ); ?></button>
</div>
