<?php
/**
 * Storage post types (no admin UI; the Vue app manages them).
 *
 * gf_program      a program / customer quote. post_author = owner.
 * gf_program_tpl  a reusable content template.
 *
 * @package gofly-pb
 */

defined( 'ABSPATH' ) || exit;

class GFPB_CPT {

	public static function register() {
		$args = array(
			'public'       => false,
			'show_ui'      => false,
			'show_in_rest' => false,
			'supports'     => array( 'title', 'author', 'revisions' ),
			'rewrite'      => false,
			'query_var'    => false,
		);
		register_post_type( 'gf_program', $args + array( 'label' => __( 'برامج PDF', 'gofly-pb' ) ) );
		register_post_type( 'gf_program_tpl', $args + array( 'label' => __( 'قوالب البرامج', 'gofly-pb' ) ) );
	}
}
