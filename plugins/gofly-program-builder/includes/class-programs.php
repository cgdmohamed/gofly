<?php
/**
 * Program and content-template storage.
 *
 * A program is the builder's JSON document:
 *   { cover:{…}, secs:[{t:'list'|'text'|'days'|'hotels'|'flights'|'price', …}], meta:{code,cname,cphone,exec,sales}, tpl:'wave' }
 * It is stored in post meta `_gfpb_data`; code, customer and phone are copied
 * to their own meta keys, and a normalised search string goes into
 * post_content so WP_Query's native search finds programs by any of them.
 *
 * @package gofly-pb
 */

defined( 'ABSPATH' ) || exit;

class GFPB_Programs {

	const TYPE     = 'gf_program';
	const TPL_TYPE = 'gf_program_tpl';

	/** Keys whose string values are URLs (images). */
	const URL_KEYS = array( 'img', 'logo', 'url' );

	/**
	 * Recursively sanitise a program document: every string becomes plain
	 * text; image/link fields become URLs (or the bundled `tpl:` references).
	 *
	 * @param mixed  $value Value.
	 * @param string $key   Current key.
	 * @return mixed
	 */
	public static function clean( $value, $key = '' ) {
		if ( is_array( $value ) ) {
			$out = array();
			foreach ( $value as $k => $v ) {
				$out[ is_int( $k ) ? $k : preg_replace( '/[^A-Za-z0-9_]/', '', $k ) ] = self::clean( $v, (string) $k );
			}
			return $out;
		}
		if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) {
			return $value;
		}
		$value = (string) $value;
		if ( in_array( $key, self::URL_KEYS, true ) ) {
			return 0 === strpos( $value, 'tpl:' ) ? 'tpl:' . sanitize_key( substr( $value, 4 ) ) : esc_url_raw( $value );
		}
		return sanitize_textarea_field( $value );
	}

	/** Western digits, lower case, no spaces/dashes — matches the builder's nz(). */
	public static function normalize( $text ) {
		$text = strtr( (string) $text, array_combine( array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' ), range( 0, 9 ) ) );
		return preg_replace( '/[\s\-+()]+/u', '', mb_strtolower( $text ) );
	}

	/** Next sequential code, e.g. GF-0012 or GF-RU-M5-0012. */
	public static function next_code( $prefix = '' ) {
		$n = (int) get_option( 'gfpb_seq', 0 ) + 1;
		update_option( 'gfpb_seq', $n, false );
		return 'GF-' . ( $prefix ? sanitize_text_field( $prefix ) . '-' : '' ) . str_pad( (string) $n, 4, '0', STR_PAD_LEFT );
	}

	public static function code_exists( $code, $exclude = 0 ) {
		$ids = get_posts(
			array(
				'post_type'      => self::TYPE,
				'post_status'    => 'any',
				'meta_key'       => '_gfpb_code', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => $code, // phpcs:ignore WordPress.DB.SlowDBQuery
				'post__not_in'   => $exclude ? array( $exclude ) : array(),
				'fields'         => 'ids',
				'posts_per_page' => 1,
			)
		);
		return (bool) $ids;
	}

	/**
	 * Create or update a program.
	 *
	 * @param int   $id         Program ID (0 = new).
	 * @param array $data       Builder document.
	 * @param int   $package_id Linked website package (0 = none).
	 * @return int|WP_Error Program ID.
	 */
	public static function save( $id, $data, $package_id = null ) {
		$data = self::clean( (array) $data );
		if ( empty( $data['cover'] ) || ! isset( $data['secs'] ) || ! is_array( $data['secs'] ) ) {
			return new WP_Error( 'gfpb_invalid', __( 'بيانات البرنامج غير صالحة.', 'gofly-pb' ), array( 'status' => 400 ) );
		}
		$meta = wp_parse_args( (array) ( $data['meta'] ?? array() ), array( 'code' => '', 'cname' => '', 'cphone' => '', 'exec' => '', 'sales' => '' ) );
		if ( '' === trim( $meta['code'] ) ) {
			$meta['code'] = self::next_code();
		}
		if ( self::code_exists( $meta['code'], $id ) ) {
			return new WP_Error( 'gfpb_code', __( 'كود البرنامج مستخدم في برنامج آخر، غيّره أولًا.', 'gofly-pb' ), array( 'status' => 409 ) );
		}
		$data['meta'] = $meta;
		unset( $data['id'] );

		$title  = trim( ( $data['cover']['title'] ?? '' ) . ( $meta['cname'] ? ' — ' . $meta['cname'] : '' ) );
		$postarr = array(
			'ID'           => $id,
			'post_type'    => self::TYPE,
			'post_status'  => 'private',
			'post_title'   => $title ? $title : $meta['code'],
			'post_content' => self::normalize( implode( ' ', array( $meta['code'], $meta['cname'], $meta['cphone'], $data['cover']['title'] ?? '' ) ) ),
		);
		$id = $id ? wp_update_post( wp_slash( $postarr ), true ) : wp_insert_post( wp_slash( $postarr ), true );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		update_post_meta( $id, '_gfpb_data', wp_slash( wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) ) );
		update_post_meta( $id, '_gfpb_code', $meta['code'] );
		update_post_meta( $id, '_gfpb_customer', $meta['cname'] );
		update_post_meta( $id, '_gfpb_phone', $meta['cphone'] );
		if ( null !== $package_id ) {
			update_post_meta( $id, '_gfpb_package_id', (int) $package_id );
		}
		return $id;
	}

	/** Builder document of a program or template. */
	public static function data( $id ) {
		$data = json_decode( (string) get_post_meta( $id, '_gfpb_data', true ), true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * API representation.
	 *
	 * @param WP_Post $post Program.
	 * @param bool    $full Include the document.
	 * @return array
	 */
	public static function to_array( $post, $full = false ) {
		$package = (int) get_post_meta( $post->ID, '_gfpb_package_id', true );
		$out     = array(
			'id'       => $post->ID,
			'code'     => get_post_meta( $post->ID, '_gfpb_code', true ),
			'customer' => get_post_meta( $post->ID, '_gfpb_customer', true ),
			'phone'    => get_post_meta( $post->ID, '_gfpb_phone', true ),
			'title'    => $post->post_title,
			'author'   => array(
				'id'   => (int) $post->post_author,
				'name' => get_the_author_meta( 'display_name', $post->post_author ),
			),
			'updated'  => mysql_to_rfc3339( $post->post_modified_gmt ),
			'package'  => $package && get_post( $package ) ? array(
				'id'    => $package,
				'title' => get_the_title( $package ),
				'link'  => get_edit_post_link( $package, 'raw' ),
			) : null,
		);
		if ( $full ) {
			$out['data'] = self::data( $post->ID );
		}
		return $out;
	}

	/**
	 * Save a content template (customer data is never stored in templates).
	 *
	 * @param int    $id    Template ID (0 = new).
	 * @param string $title Name shown in the picker.
	 * @param array  $data  Builder document.
	 * @param string $code  Code prefix used for programs made from it.
	 * @return int|WP_Error
	 */
	public static function save_template( $id, $title, $data, $code = '' ) {
		$data = self::clean( (array) $data );
		unset( $data['id'] );
		$data['meta'] = array( 'code' => '', 'cname' => '', 'cphone' => '', 'exec' => $data['meta']['exec'] ?? '', 'sales' => '' );
		$postarr      = array(
			'ID'          => $id,
			'post_type'   => self::TPL_TYPE,
			'post_status' => 'publish',
			'post_title'  => sanitize_text_field( $title ),
		);
		$id = $id ? wp_update_post( wp_slash( $postarr ), true ) : wp_insert_post( wp_slash( $postarr ), true );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		update_post_meta( $id, '_gfpb_data', wp_slash( wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) ) );
		update_post_meta( $id, '_gfpb_code', sanitize_text_field( $code ) );
		return $id;
	}
}
