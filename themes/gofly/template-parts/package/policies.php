<?php
/**
 * Notes and policies accordion.
 *
 * @package gofly
 */

$policies = (array) gofly_field( 'policies', false, array() );
if ( ! $policies ) {
	return;
}
gofly_package_heading( 'policies', 'gavel', __( 'ملاحظات وسياسات', 'gofly' ) );
get_template_part(
	'template-parts/components/accordion',
	null,
	array(
		'items' => array_map(
			fn( $row ) => array(
				'question' => $row['title'],
				'answer'   => $row['text'],
			),
			$policies
		),
	)
);
