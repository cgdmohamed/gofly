<?php
/**
 * Navy title banner used at the top of inner pages.
 *
 * @package gofly
 *
 * @var array $args {
 *     @type string $title    Heading.
 *     @type string $subtitle Lead text.
 *     @type string $class    Extra modifier classes.
 * }
 */

$args = wp_parse_args(
	$args,
	array(
		'title'    => get_the_title(),
		'subtitle' => gofly_page_subtitle(),
		'class'    => '',
	)
);
?>
<section class="page-hero <?php echo esc_attr( $args['class'] ); ?>">
	<?php echo gofly_breadcrumb(); // phpcs:ignore ?>
	<h1 class="page-hero__title"><?php echo esc_html( $args['title'] ); ?></h1>
	<?php if ( $args['subtitle'] ) : ?>
		<p class="page-hero__text"><?php echo esc_html( $args['subtitle'] ); ?></p>
	<?php endif; ?>
</section>
