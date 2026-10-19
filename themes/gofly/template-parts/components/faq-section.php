<?php
/**
 * FAQ section (fields from the "الأسئلة الشائعة" group on the page).
 *
 * @package gofly
 *
 * @var array $args {
 *     @type string $layout 'split' (intro beside the list) | 'stacked'.
 * }
 */

$items = (array) gofly_field( 'faq_items', false, array() );
if ( ! $items ) {
	return;
}
$layout = $args['layout'] ?? 'split';
$button = gofly_field( 'faq_button' );
?>
<section class="section faq faq--<?php echo esc_attr( $layout ); ?>">
	<div class="faq__intro">
		<?php if ( gofly_field( 'faq_eyebrow' ) ) : ?>
			<div class="eyebrow"><?php echo esc_html( gofly_field( 'faq_eyebrow' ) ); ?></div>
		<?php endif; ?>
		<h2 class="section-title"><?php echo esc_html( gofly_field( 'faq_title', false, __( 'أسئلة شائعة', 'gofly' ) ) ); ?></h2>
		<?php if ( gofly_field( 'faq_text' ) ) : ?>
			<p class="section-intro"><?php echo esc_html( gofly_field( 'faq_text' ) ); ?></p>
		<?php endif; ?>
		<?php echo gofly_link( $button, 'btn btn--gold', 'chat' ); // phpcs:ignore ?>
	</div>
	<?php get_template_part( 'template-parts/components/accordion', null, array( 'items' => $items ) ); ?>
</section>
