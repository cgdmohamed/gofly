<?php
/**
 * "How it works" steps.
 *
 * @package gofly
 */

$items = (array) gofly_field( 'steps_items', false, array() );
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
			'eyebrow' => gofly_field( 'steps_eyebrow' ),
			'title'   => gofly_field( 'steps_title' ),
		)
	);
	?>
	<div class="grid grid--230">
		<?php
		foreach ( $items as $i => $item ) {
			get_template_part(
				'template-parts/components/feature-card',
				null,
				array(
					'variant' => 'step',
					'number'  => $i + 1,
				) + $item
			);
		}
		?>
	</div>
</section>
