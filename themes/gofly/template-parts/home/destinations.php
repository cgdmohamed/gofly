<?php
/**
 * Featured destinations with their package count.
 *
 * @package gofly
 */

$terms = (array) gofly_field( 'dest_terms', false, array() );
if ( ! $terms ) {
	$terms = get_terms(
		array(
			'taxonomy' => 'destination',
			'number'   => 4,
			'orderby'  => 'count',
			'order'    => 'DESC',
		)
	);
}
if ( ! $terms || is_wp_error( $terms ) ) {
	return;
}
$all = gofly_template_url( 'destinations' ) ?: get_post_type_archive_link( 'package' );
?>
<section class="section">
	<?php
	get_template_part(
		'template-parts/components/section-head',
		null,
		array(
			'eyebrow' => gofly_field( 'dest_eyebrow' ),
			'title'   => gofly_field( 'dest_title' ),
		)
	);
	?>
	<div class="grid grid--220">
		<?php
		foreach ( $terms as $term ) {
			/* translators: %d: number of trips */
			$count = sprintf( _n( '%d رحلة', '%d رحلات', $term->count, 'gofly' ), $term->count );
			get_template_part(
				'template-parts/components/destination-card',
				null,
				array(
					'term' => $term,
					'meta' => $count,
				)
			);
		}
		?>
	</div>
	<?php get_template_part( 'template-parts/components/section-more', null, array( 'url' => $all, 'title' => __( 'عرض كل الوجهات', 'gofly' ) ) ); ?>
</section>
