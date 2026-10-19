<?php
/**
 * Icon card used for services, values and "how it works" steps.
 *
 * @package gofly
 *
 * @var array $args {
 *     @type string $variant 'service' | 'value' | 'step'.
 *     @type string $icon    Material Symbols name.
 *     @type string $title   Title.
 *     @type string $text    Short description.
 *     @type array  $link    Optional ACF link (services).
 *     @type int    $number  Step number (steps).
 * }
 */

$args = wp_parse_args(
	$args,
	array(
		'variant' => 'value',
		'icon'    => '',
		'title'   => '',
		'text'    => '',
		'link'    => array(),
		'number'  => 0,
	)
);
$is_link = ! empty( $args['link']['url'] );
$tag     = $is_link ? 'a' : 'div';
?>
<<?php echo $tag; // phpcs:ignore ?> class="feature feature--<?php echo esc_attr( $args['variant'] ); ?>"<?php echo $is_link ? ' href="' . esc_url( $args['link']['url'] ) . '"' : ''; ?>>
	<?php if ( $args['number'] ) : ?>
		<span class="feature__num" aria-hidden="true"><?php echo (int) $args['number']; ?></span>
	<?php endif; ?>
	<span class="icon-tile"><?php echo gofly_icon( $args['icon'] ); // phpcs:ignore ?></span>
	<h3 class="feature__title"><?php echo esc_html( $args['title'] ); ?></h3>
	<?php if ( $args['text'] ) : ?>
		<p class="feature__text"><?php echo esc_html( $args['text'] ); ?></p>
	<?php endif; ?>
	<?php if ( $is_link ) : ?>
		<span class="feature__more"><?php echo esc_html( $args['link']['title'] ? $args['link']['title'] : __( 'المزيد', 'gofly' ) ); ?> <?php echo gofly_icon( 'arrow_back' ); // phpcs:ignore ?></span>
	<?php endif; ?>
</<?php echo $tag; // phpcs:ignore ?>>
