<?php
/**
 * Accordion list (FAQ, policies) built on native <details>.
 *
 * @package gofly
 *
 * @var array $args {
 *     @type array  $items Rows with 'question' and 'answer'.
 *     @type string $class Extra classes.
 * }
 */

$items = $args['items'] ?? array();
if ( ! $items ) {
	return;
}
?>
<div class="accordion <?php echo esc_attr( $args['class'] ?? '' ); ?>">
	<?php foreach ( $items as $item ) : ?>
		<details class="accordion__item">
			<summary><?php echo esc_html( $item['question'] ); ?><span class="accordion__chev"><?php echo gofly_icon( 'expand_more' ); // phpcs:ignore ?></span></summary>
			<div class="accordion__body"><?php echo wp_kses_post( wpautop( $item['answer'] ) ); ?></div>
		</details>
	<?php endforeach; ?>
</div>
