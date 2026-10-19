<?php
/**
 * Empty state box.
 *
 * @package gofly
 *
 * @var array $args {
 *     @type string $icon   Material Symbols name.
 *     @type string $title  Heading.
 *     @type string $text   Description.
 *     @type array  $button Optional link ['url','title'].
 *     @type string $attrs  Extra raw attributes for the wrapper (trusted).
 *     @type string $reset  Label for a filter-reset button (packages archive).
 * }
 */

$args = wp_parse_args(
	$args,
	array(
		'icon'   => 'search_off',
		'title'  => __( 'لا توجد نتائج', 'gofly' ),
		'text'   => __( 'جرّب البحث بكلمات أخرى.', 'gofly' ),
		'button' => array(),
		'attrs'  => '',
		'reset'  => '',
	)
);
?>
<div class="empty-state" <?php echo $args['attrs']; // phpcs:ignore ?>>
	<span class="icon-tile icon-tile--xl"><?php echo gofly_icon( $args['icon'] ); // phpcs:ignore ?></span>
	<h3><?php echo esc_html( $args['title'] ); ?></h3>
	<p><?php echo esc_html( $args['text'] ); ?></p>
	<?php if ( ! empty( $args['button']['url'] ) ) : ?>
		<a class="btn btn--gold" href="<?php echo esc_url( $args['button']['url'] ); ?>"><?php echo esc_html( $args['button']['title'] ); ?></a>
	<?php endif; ?>
	<?php if ( $args['reset'] ) : ?>
		<button class="btn btn--gold" type="button" data-pf-reset><?php echo esc_html( $args['reset'] ); ?></button>
	<?php endif; ?>
</div>
