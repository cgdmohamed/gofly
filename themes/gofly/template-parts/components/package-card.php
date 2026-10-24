<?php
/**
 * Package card: photo with duration badge, destination, rating, price.
 *
 * Carries data-* attributes so the packages archive can filter and sort.
 *
 * @package gofly
 *
 * @var array $args {
 *     @type int    $post_id Package ID.
 *     @type string $button  Button label.
 * }
 */

$args = wp_parse_args(
	$args,
	array(
		'post_id' => get_the_ID(),
		'button'  => __( 'التفاصيل', 'gofly' ),
	)
);
$pid = $args['post_id'];
$pkg = gofly_package( $pid );
?>
<a class="card package-card" href="<?php echo esc_url( get_permalink( $pid ) ); ?>"
	data-dest="<?php echo esc_attr( implode( ' ', $pkg['dest_slugs'] ) ); ?>"
	data-cat="<?php echo esc_attr( implode( ' ', $pkg['cat_slugs'] ) ); ?>"
	data-days="<?php echo esc_attr( $pkg['days'] ); ?>"
	data-price="<?php echo esc_attr( $pkg['price'] ); ?>">
	<div class="package-card__media media-zoom">
		<?php echo gofly_card_image( $pid ); // phpcs:ignore ?>
		<?php if ( $pkg['days'] ) : ?>
			<span class="badge badge--gold package-card__days"><?php echo gofly_icon( 'schedule' ); // phpcs:ignore ?><?php echo esc_html( gofly_duration( $pkg['days'] ) ); ?></span>
		<?php endif; ?>
		<?php if ( $pkg['discount'] ) : ?>
			<span class="badge badge--red package-card__off"><?php echo esc_html( sprintf( /* translators: %d: percent */ __( 'خصم %d%%', 'gofly' ), $pkg['discount'] ) ); ?></span>
		<?php endif; ?>
	</div>
	<div class="package-card__body">
		<div class="package-card__meta">
			<span><?php echo gofly_icon( 'location_on', 'is-gold' ); // phpcs:ignore ?><?php echo esc_html( $pkg['destination'] ); ?></span>
			<?php if ( $pkg['rating'] ) : ?>
				<span><?php echo gofly_icon( 'star', 'ms--fill is-gold' ); // phpcs:ignore ?><?php echo esc_html( number_format( (float) $pkg['rating'], 1 ) ); ?></span>
			<?php endif; ?>
		</div>
		<h3 class="package-card__title"><?php echo esc_html( get_the_title( $pid ) ); ?></h3>
		<div class="package-card__foot">
			<?php if ( $pkg['price'] ) : ?>
				<div>
					<span class="package-card__from"><?php esc_html_e( 'ابتداءً من', 'gofly' ); ?></span>
					<?php if ( $pkg['old_price'] ) : ?>
						<s class="package-card__old"><?php echo esc_html( number_format( $pkg['old_price'] ) ); ?></s>
					<?php endif; ?>
					<div class="package-card__price"><?php echo gofly_price( $pkg['price'] ); // phpcs:ignore ?></div>
				</div>
			<?php else : ?>
				<span class="package-card__from"><?php esc_html_e( 'اطلب عرض السعر', 'gofly' ); ?></span>
			<?php endif; ?>
			<span class="btn btn--navy btn--sm"><?php echo esc_html( $args['button'] ); ?></span>
		</div>
	</div>
</a>
