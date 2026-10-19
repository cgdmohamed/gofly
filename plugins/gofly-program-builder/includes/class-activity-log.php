<?php
/**
 * Activity log: who did what to which program, and when.
 *
 * Actions: created, updated, duplicated, deleted, pdf_downloaded,
 * created_from_package, pushed_to_package, template_saved, template_deleted,
 * settings_saved.
 *
 * @package gofly-pb
 */

defined( 'ABSPATH' ) || exit;

class GFPB_Activity_Log {

	const DB_VERSION = '1';

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'gofly_pb_log';
	}

	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta(
			'CREATE TABLE ' . self::table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				created_at datetime NOT NULL,
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				user_role varchar(60) NOT NULL DEFAULT '',
				action varchar(40) NOT NULL,
				object_id bigint(20) unsigned NOT NULL DEFAULT 0,
				code varchar(80) NOT NULL DEFAULT '',
				package_id bigint(20) unsigned NOT NULL DEFAULT 0,
				details text NULL,
				ip varchar(45) NOT NULL DEFAULT '',
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY action (action),
				KEY object_id (object_id)
			) {$wpdb->get_charset_collate()};"
		);
		update_option( 'gfpb_db_version', self::DB_VERSION );
	}

	/**
	 * Record an event for the current user.
	 *
	 * @param string $action     Action slug.
	 * @param int    $object_id  Program / template ID.
	 * @param array  $extra      code, package_id, details.
	 */
	public static function add( $action, $object_id = 0, $extra = array() ) {
		global $wpdb;
		$user = wp_get_current_user();
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			self::table(),
			array(
				'created_at' => current_time( 'mysql', true ),
				'user_id'    => $user->ID,
				'user_role'  => $user->roles ? reset( $user->roles ) : '',
				'action'     => $action,
				'object_id'  => (int) $object_id,
				'code'       => (string) ( $extra['code'] ?? ( $object_id ? get_post_meta( $object_id, '_gfpb_code', true ) : '' ) ),
				'package_id' => (int) ( $extra['package_id'] ?? 0 ),
				'details'    => isset( $extra['details'] ) ? wp_json_encode( $extra['details'], JSON_UNESCAPED_UNICODE ) : null,
				'ip'         => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			)
		);
	}

	/**
	 * Query the log.
	 *
	 * @param array $args user_id, action, from, to (Y-m-d), object_id, page, per_page.
	 * @return array { items, total }
	 */
	public static function query( $args = array() ) {
		global $wpdb;
		$args  = wp_parse_args( $args, array( 'page' => 1, 'per_page' => 50 ) );
		$where = array( '1=1' );
		$vals  = array();
		foreach ( array( 'user_id' => '%d', 'action' => '%s', 'object_id' => '%d' ) as $col => $fmt ) {
			if ( ! empty( $args[ $col ] ) ) {
				$where[] = "$col = $fmt";
				$vals[]  = $args[ $col ];
			}
		}
		if ( ! empty( $args['from'] ) ) {
			$where[] = 'created_at >= %s';
			$vals[]  = $args['from'] . ' 00:00:00';
		}
		if ( ! empty( $args['to'] ) ) {
			$where[] = 'created_at <= %s';
			$vals[]  = $args['to'] . ' 23:59:59';
		}
		$sql   = implode( ' AND ', $where );
		$table = self::table();
		$limit = $args['per_page'] > 0 ? $wpdb->prepare( ' LIMIT %d OFFSET %d', $args['per_page'], ( max( 1, $args['page'] ) - 1 ) * $args['per_page'] ) : '';
		// phpcs:disable WordPress.DB
		$total = (int) $wpdb->get_var( $vals ? $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE $sql", $vals ) : "SELECT COUNT(*) FROM $table WHERE $sql" );
		$items = $wpdb->get_results( ( $vals ? $wpdb->prepare( "SELECT * FROM $table WHERE $sql ORDER BY id DESC", $vals ) : "SELECT * FROM $table WHERE $sql ORDER BY id DESC" ) . $limit, ARRAY_A );
		// phpcs:enable
		return array(
			'items' => $items,
			'total' => $total,
		);
	}

	/** Arabic labels for the log screen. */
	public static function labels() {
		return array(
			'created'              => __( 'إنشاء برنامج', 'gofly-pb' ),
			'updated'              => __( 'تعديل برنامج', 'gofly-pb' ),
			'duplicated'           => __( 'نسخ برنامج', 'gofly-pb' ),
			'deleted'              => __( 'حذف برنامج', 'gofly-pb' ),
			'pdf_downloaded'       => __( 'تحميل PDF', 'gofly-pb' ),
			'created_from_package' => __( 'إنشاء من باقة الموقع', 'gofly-pb' ),
			'pushed_to_package'    => __( 'تحديث باقة الموقع', 'gofly-pb' ),
			'template_saved'       => __( 'حفظ قالب', 'gofly-pb' ),
			'template_deleted'     => __( 'حذف قالب', 'gofly-pb' ),
			'settings_saved'       => __( 'تعديل بيانات الشركة', 'gofly-pb' ),
		);
	}
}
