<?php
/**
 * Featured packages (chosen on the home page, or the latest three).
 *
 * @package gofly
 */

$ids = (array) gofly_field( 'pkgs_items', false, array() );
if ( ! $ids ) {
	$ids = get_posts(
		array(
			'post_type'      => 'package',
			'posts_per_page' => 3,
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
			'eyebrow' => gofly_field( 'pkgs_eyebrow' ),
			'title'   => gofly_field( 'pkgs_title' ),
			'link'    => array(
				'url'   => get_post_type_archive_link( 'package' ),
				'title' => __( 'كل الباقات', 'gofly' ),
			),
		)
	);
	?>
	<div class="grid grid--280">
		<?php
		foreach ( $ids as $pid ) {
			get_template_part(
				'template-parts/components/package-card',
				null,
				array(
					'post_id' => $pid,
					'button'  => __( 'احجز', 'gofly' ),
				)
			);
		}
		?>
	</div>
</section>
