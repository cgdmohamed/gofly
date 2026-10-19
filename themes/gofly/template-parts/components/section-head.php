<?php
/**
 * Section heading: gold eyebrow, title, optional intro and "view all" link.
 *
 * @package gofly
 *
 * @var array $args {
 *     @type string $eyebrow Small label above the title.
 *     @type string $title   Section title.
 *     @type string $intro   Optional lead paragraph.
 *     @type array  $link    Optional ['url' => '', 'title' => ''].
 *     @type string $tag     Heading tag, default h2.
 * }
 */

$args = wp_parse_args(
	$args,
	array(
		'eyebrow' => '',
		'title'   => '',
		'intro'   => '',
		'link'    => array(),
		'tag'     => 'h2',
	)
);
if ( ! $args['title'] && ! $args['eyebrow'] ) {
	return;
}
$tag = tag_escape( $args['tag'] );
?>
<div class="section-head">
	<div>
		<?php if ( $args['eyebrow'] ) : ?>
			<div class="eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></div>
		<?php endif; ?>
		<<?php echo $tag; // phpcs:ignore ?> class="section-title"><?php echo esc_html( $args['title'] ); ?></<?php echo $tag; // phpcs:ignore ?>>
		<?php if ( $args['intro'] ) : ?>
			<p class="section-intro"><?php echo esc_html( $args['intro'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( ! empty( $args['link']['url'] ) ) : ?>
		<a class="link-more" href="<?php echo esc_url( $args['link']['url'] ); ?>"><?php echo esc_html( $args['link']['title'] ); ?> <?php echo gofly_icon( 'arrow_back' ); // phpcs:ignore ?></a>
	<?php endif; ?>
</div>
