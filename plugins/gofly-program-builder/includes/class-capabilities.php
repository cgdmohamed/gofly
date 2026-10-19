<?php
/**
 * Capabilities mapped onto WordPress' native roles.
 *
 * | capability        | administrator | editor | author |
 * |-------------------|:-:|:-:|:-:|
 * | gfpb_use          | ✓ | ✓ | ✓ |  create, edit and download own programs
 * | gfpb_manage_all   | ✓ | ✓ |   |  edit, download and delete anyone's programs
 * | gfpb_push         | ✓ | ✓ |   |  update / create website packages
 * | gfpb_templates    | ✓ | ✓ |   |  manage content templates
 * | gfpb_settings     | ✓ |   |   |  company identity and branding
 * | gfpb_view_log     | ✓ |   |   |  activity log
 *
 * @package gofly-pb
 */

defined( 'ABSPATH' ) || exit;

class GFPB_Capabilities {

	const MAP = array(
		'administrator' => array( 'gfpb_use', 'gfpb_manage_all', 'gfpb_push', 'gfpb_templates', 'gfpb_settings', 'gfpb_view_log' ),
		'editor'        => array( 'gfpb_use', 'gfpb_manage_all', 'gfpb_push', 'gfpb_templates' ),
		'author'        => array( 'gfpb_use' ),
	);

	public static function add() {
		foreach ( self::MAP as $role_name => $caps ) {
			$role = get_role( $role_name );
			if ( $role ) {
				foreach ( $caps as $cap ) {
					$role->add_cap( $cap );
				}
			}
		}
	}

	public static function remove() {
		foreach ( self::MAP as $role_name => $caps ) {
			$role = get_role( $role_name );
			if ( $role ) {
				foreach ( $caps as $cap ) {
					$role->remove_cap( $cap );
				}
			}
		}
	}

	/** Capabilities of the current user, for the Vue app. */
	public static function current() {
		$out = array();
		foreach ( self::MAP['administrator'] as $cap ) {
			$out[ str_replace( 'gfpb_', '', $cap ) ] = current_user_can( $cap );
		}
		return $out;
	}

	/**
	 * Whether the current user may open / change a program.
	 *
	 * @param WP_Post $post Program.
	 * @return bool
	 */
	public static function can_edit( $post ) {
		return current_user_can( 'gfpb_manage_all' ) || ( current_user_can( 'gfpb_use' ) && (int) $post->post_author === get_current_user_id() );
	}
}
