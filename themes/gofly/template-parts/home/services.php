<?php
/**
 * Services grid.
 *
 * @package gofly
 */

$items = (array) gofly_field( 'services_items', false, array() );
if ( ! $items ) {
	return;
}
?>
<section class="section">
	<?php
	get_template_part(
		'template-parts/components/section-head',
		null,
		array(
			'eyebrow' => gofly_field( 'services_eyebrow' ),
			'title'   => gofly_field( 'services_title' ),
			'intro'   => gofly_field( 'services_intro' ),
		)
	);
	?>
	<div class="grid grid--280">
		<?php
		foreach ( $items as $item ) {
			get_template_part( 'template-parts/components/feature-card', null, array( 'variant' => 'service' ) + $item );
		}
		?>
	</div>
</section>
