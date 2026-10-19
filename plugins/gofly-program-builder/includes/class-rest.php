<?php
/**
 * REST API for the builder app: namespace gofly-pb/v1.
 *
 * Every route requires a logged-in user with the matching capability
 * (see GFPB_Capabilities) and the standard wp_rest nonce.
 *
 * @package gofly-pb
 */

defined( 'ABSPATH' ) || exit;

class GFPB_REST {

	const NS = 'gofly-pb/v1';

	public static function register() {
		$cap = fn( $c ) => fn() => current_user_can( $c );
		$id  = array( 'id' => array( 'validate_callback' => fn( $v ) => is_numeric( $v ) ) );

		register_rest_route( self::NS, '/programs', array(
			array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'list_programs' ), 'permission_callback' => $cap( 'gfpb_use' ) ),
			array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'create_program' ), 'permission_callback' => $cap( 'gfpb_use' ) ),
		) );
		register_rest_route( self::NS, '/programs/(?P<id>\d+)', array(
			array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'get_program' ), 'permission_callback' => array( __CLASS__, 'can_program' ), 'args' => $id ),
			array( 'methods' => 'PUT', 'callback' => array( __CLASS__, 'update_program' ), 'permission_callback' => array( __CLASS__, 'can_program' ), 'args' => $id ),
			array( 'methods' => 'DELETE', 'callback' => array( __CLASS__, 'delete_program' ), 'permission_callback' => array( __CLASS__, 'can_program' ), 'args' => $id ),
		) );
		register_rest_route( self::NS, '/programs/(?P<id>\d+)/duplicate', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'duplicate_program' ), 'permission_callback' => array( __CLASS__, 'can_program' ) ) );
		register_rest_route( self::NS, '/programs/(?P<id>\d+)/download', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'log_download' ), 'permission_callback' => array( __CLASS__, 'can_program' ) ) );
		register_rest_route( self::NS, '/programs/(?P<id>\d+)/package-diff', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'package_diff' ), 'permission_callback' => array( __CLASS__, 'can_push' ) ) );
		register_rest_route( self::NS, '/programs/(?P<id>\d+)/push', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'push_package' ), 'permission_callback' => array( __CLASS__, 'can_push' ) ) );

		register_rest_route( self::NS, '/templates', array(
			array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'list_templates' ), 'permission_callback' => $cap( 'gfpb_use' ) ),
			array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'save_template' ), 'permission_callback' => $cap( 'gfpb_templates' ) ),
		) );
		register_rest_route( self::NS, '/templates/(?P<id>\d+)', array(
			array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'get_template' ), 'permission_callback' => $cap( 'gfpb_use' ), 'args' => $id ),
			array( 'methods' => 'DELETE', 'callback' => array( __CLASS__, 'delete_template' ), 'permission_callback' => $cap( 'gfpb_templates' ), 'args' => $id ),
		) );

		register_rest_route( self::NS, '/packages', array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'list_packages' ), 'permission_callback' => $cap( 'gfpb_use' ) ) );
		register_rest_route( self::NS, '/packages/(?P<id>\d+)/program', array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'package_program' ), 'permission_callback' => $cap( 'gfpb_use' ), 'args' => $id ) );

		register_rest_route( self::NS, '/settings', array(
			array( 'methods' => 'GET', 'callback' => fn() => GFPB_Settings::get(), 'permission_callback' => $cap( 'gfpb_use' ) ),
			array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'save_settings' ), 'permission_callback' => $cap( 'gfpb_settings' ) ),
		) );
	}

	/* Permissions ------------------------------------------------------- */

	private static function program( $request ) {
		$post = get_post( (int) $request['id'] );
		return $post && GFPB_Programs::TYPE === $post->post_type ? $post : null;
	}

	public static function can_program( $request ) {
		$post = self::program( $request );
		if ( ! $post ) {
			return new WP_Error( 'gfpb_not_found', __( 'البرنامج غير موجود.', 'gofly-pb' ), array( 'status' => 404 ) );
		}
		return GFPB_Capabilities::can_edit( $post );
	}

	public static function can_push( $request ) {
		return current_user_can( 'gfpb_push' ) && true === self::can_program( $request );
	}

	/* Programs ---------------------------------------------------------- */

	public static function list_programs( $request ) {
		$args = array(
			'post_type'      => GFPB_Programs::TYPE,
			'post_status'    => 'private',
			'posts_per_page' => 30,
			'paged'          => max( 1, (int) $request['page'] ),
			'orderby'        => 'modified',
		);
		if ( $request['search'] ) {
			$args['s'] = GFPB_Programs::normalize( $request['search'] );
		}
		if ( ! current_user_can( 'gfpb_manage_all' ) || 'mine' === $request['scope'] ) {
			$args['author'] = get_current_user_id();
		}
		$query = new WP_Query( $args );
		return array(
			'items' => array_map( array( 'GFPB_Programs', 'to_array' ), $query->posts ),
			'total' => (int) $query->found_posts,
			'pages' => (int) $query->max_num_pages,
		);
	}

	public static function get_program( $request ) {
		return GFPB_Programs::to_array( self::program( $request ), true );
	}

	public static function create_program( $request ) {
		$data = (array) $request['data'];
		if ( empty( $data['meta']['code'] ) ) {
			$data['meta']['code'] = GFPB_Programs::next_code( (string) $request['code_prefix'] );
		}
		$package = (int) $request['package_id'];
		$id      = GFPB_Programs::save( 0, $data, $package );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$source = (string) $request['source'];
		GFPB_Activity_Log::add( 'package' === $source ? 'created_from_package' : 'created', $id, array( 'package_id' => $package, 'details' => $request['source_label'] ? array( 'source' => sanitize_text_field( $request['source_label'] ) ) : null ) );
		return GFPB_Programs::to_array( get_post( $id ), true );
	}

	public static function update_program( $request ) {
		$post = self::program( $request );
		$id   = GFPB_Programs::save( $post->ID, (array) $request['data'], isset( $request['package_id'] ) ? (int) $request['package_id'] : null );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		GFPB_Activity_Log::add( 'updated', $id );
		return GFPB_Programs::to_array( get_post( $id ), true );
	}

	public static function delete_program( $request ) {
		$post = self::program( $request );
		if ( ! current_user_can( 'gfpb_manage_all' ) ) {
			return new WP_Error( 'gfpb_forbidden', __( 'لا تملك صلاحية حذف البرامج.', 'gofly-pb' ), array( 'status' => 403 ) );
		}
		GFPB_Activity_Log::add( 'deleted', $post->ID, array( 'details' => array( 'title' => $post->post_title ) ) );
		wp_trash_post( $post->ID );
		return array( 'deleted' => true );
	}

	public static function duplicate_program( $request ) {
		$post = self::program( $request );
		$data = GFPB_Programs::data( $post->ID );
		$data['meta']['code'] = GFPB_Programs::next_code();
		$id   = GFPB_Programs::save( 0, $data, (int) get_post_meta( $post->ID, '_gfpb_package_id', true ) );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		GFPB_Activity_Log::add( 'duplicated', $id, array( 'details' => array( 'from' => get_post_meta( $post->ID, '_gfpb_code', true ) ) ) );
		return GFPB_Programs::to_array( get_post( $id ), true );
	}

	public static function log_download( $request ) {
		$post = self::program( $request );
		GFPB_Activity_Log::add( 'pdf_downloaded', $post->ID, array( 'details' => array( 'style' => sanitize_key( (string) $request['style'] ), 'pages' => (int) $request['pages'] ) ) );
		return array( 'logged' => true );
	}

	/* Package sync ------------------------------------------------------ */

	public static function package_diff( $request ) {
		$post    = self::program( $request );
		$package = isset( $request['package_id'] ) ? (int) $request['package_id'] : (int) get_post_meta( $post->ID, '_gfpb_package_id', true );
		return array(
			'package' => $package ? array( 'id' => $package, 'title' => get_the_title( $package ) ) : null,
			'rows'    => GFPB_Package_Sync::diff( GFPB_Programs::data( $post->ID ), $package ),
		);
	}

	public static function push_package( $request ) {
		$post    = self::program( $request );
		$package = isset( $request['package_id'] ) ? (int) $request['package_id'] : (int) get_post_meta( $post->ID, '_gfpb_package_id', true );
		$fields  = array_map( 'sanitize_key', (array) $request['fields'] );
		$result  = GFPB_Package_Sync::push( GFPB_Programs::data( $post->ID ), $package, $fields );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		update_post_meta( $post->ID, '_gfpb_package_id', $result );
		GFPB_Activity_Log::add( 'pushed_to_package', $post->ID, array( 'package_id' => $result, 'details' => array( 'fields' => $fields, 'created' => ! $package ) ) );
		return array(
			'package' => array(
				'id'     => $result,
				'title'  => get_the_title( $result ),
				'link'   => get_edit_post_link( $result, 'raw' ),
				'status' => get_post_status( $result ),
			),
		);
	}

	public static function list_packages( $request ) {
		$posts = get_posts(
			array(
				'post_type'      => 'package',
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				's'              => sanitize_text_field( (string) $request['search'] ),
				'posts_per_page' => 50,
			)
		);
		return array_map(
			fn( $p ) => array(
				'id'     => $p->ID,
				'title'  => $p->post_title,
				'status' => $p->post_status,
				'thumb'  => get_the_post_thumbnail_url( $p, 'thumbnail' ),
			),
			$posts
		);
	}

	public static function package_program( $request ) {
		$package = get_post( (int) $request['id'] );
		if ( ! $package || 'package' !== $package->post_type ) {
			return new WP_Error( 'gfpb_package', __( 'الباقة غير موجودة.', 'gofly-pb' ), array( 'status' => 404 ) );
		}
		return array(
			'package' => array( 'id' => $package->ID, 'title' => $package->post_title ),
			'data'    => GFPB_Package_Sync::to_program( $package->ID ),
		);
	}

	/* Templates & settings ---------------------------------------------- */

	public static function list_templates() {
		$posts = get_posts(
			array(
				'post_type'      => GFPB_Programs::TPL_TYPE,
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);
		return array_map(
			fn( $p ) => array(
				'id'     => $p->ID,
				'title'  => $p->post_title,
				'code'   => get_post_meta( $p->ID, '_gfpb_code', true ),
				'author' => get_the_author_meta( 'display_name', $p->post_author ),
			),
			$posts
		);
	}

	public static function get_template( $request ) {
		$post = get_post( (int) $request['id'] );
		if ( ! $post || GFPB_Programs::TPL_TYPE !== $post->post_type ) {
			return new WP_Error( 'gfpb_not_found', __( 'القالب غير موجود.', 'gofly-pb' ), array( 'status' => 404 ) );
		}
		return array(
			'id'    => $post->ID,
			'title' => $post->post_title,
			'code'  => get_post_meta( $post->ID, '_gfpb_code', true ),
			'data'  => GFPB_Programs::data( $post->ID ),
		);
	}

	public static function save_template( $request ) {
		$id = GFPB_Programs::save_template( (int) $request['id'], (string) $request['title'], (array) $request['data'], (string) $request['code'] );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		GFPB_Activity_Log::add( 'template_saved', $id, array( 'code' => (string) $request['code'], 'details' => array( 'title' => get_the_title( $id ) ) ) );
		return array( 'id' => $id, 'title' => get_the_title( $id ) );
	}

	public static function delete_template( $request ) {
		$post = get_post( (int) $request['id'] );
		if ( ! $post || GFPB_Programs::TPL_TYPE !== $post->post_type ) {
			return new WP_Error( 'gfpb_not_found', __( 'القالب غير موجود.', 'gofly-pb' ), array( 'status' => 404 ) );
		}
		GFPB_Activity_Log::add( 'template_deleted', $post->ID, array( 'details' => array( 'title' => $post->post_title ) ) );
		wp_delete_post( $post->ID, true );
		return array( 'deleted' => true );
	}

	public static function save_settings( $request ) {
		$settings = GFPB_Settings::update( (array) $request->get_json_params() );
		GFPB_Activity_Log::add( 'settings_saved' );
		return $settings;
	}
}
