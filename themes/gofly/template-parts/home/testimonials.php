<?php
/**
 * Traveller testimonials: the picked ones, or the latest six.
 *
 * @package gofly
 */

$ids = (array) gofly_field( 'testi_posts', false, array() );
if ( ! $ids ) {
	$ids = get_posts(
		array(
			'post_type'      => 'testimonial',
			'posts_per_page' => 6,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'fields'         => 'ids',
		)
	);
}
if ( ! $ids ) {
	return;
}
?>
<section class="section">
	<?php
	get_template_part(
		'template-parts/components/section-head',
		null,
		array(
			'eyebrow' => gofly_field( 'testi_eyebrow' ),
			'title'   => gofly_field( 'testi_title' ),
		)
	);
	?>
	<div class="grid grid--280">
		<?php
		foreach ( $ids as $id ) {
			get_template_part( 'template-parts/components/testimonial-card', null, array( 'post_id' => $id ) );
		}
		?>
	</div>
</section>
