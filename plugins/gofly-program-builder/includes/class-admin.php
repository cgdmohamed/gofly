<?php
/**
 * wp-admin screens: the builder app, the activity log, and shortcuts from
 * the website packages list / edit screen.
 *
 * @package gofly-pb
 */

defined( 'ABSPATH' ) || exit;

class GFPB_Admin {

	const SLUG = 'gofly-program-builder';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'post_row_actions', array( __CLASS__, 'row_action' ), 10, 2 );
		add_action( 'add_meta_boxes_package', array( __CLASS__, 'metabox' ) );
		add_action( 'admin_post_gfpb_log_csv', array( __CLASS__, 'export_csv' ) );
		add_filter( 'admin_body_class', fn( $c ) => self::is_app() ? $c . ' gfpb-fullscreen' : $c );
	}

	private static function is_app() {
		return is_admin() && isset( $_GET['page'] ) && self::SLUG === $_GET['page']; // phpcs:ignore WordPress.Security.NonceVerification
	}

	public static function app_url( $args = array() ) {
		return add_query_arg( array_merge( array( 'page' => self::SLUG ), $args ), admin_url( 'admin.php' ) );
	}

	public static function menu() {
		add_menu_page( __( 'برامج PDF', 'gofly-pb' ), __( 'برامج PDF', 'gofly-pb' ), 'gfpb_use', self::SLUG, array( __CLASS__, 'render_app' ), 'dashicons-media-document', 6 );
		add_submenu_page( self::SLUG, __( 'أداة البرامج', 'gofly-pb' ), __( 'أداة البرامج', 'gofly-pb' ), 'gfpb_use', self::SLUG, array( __CLASS__, 'render_app' ) );
		add_submenu_page( self::SLUG, __( 'سجل النشاط', 'gofly-pb' ), __( 'سجل النشاط', 'gofly-pb' ), 'gfpb_view_log', 'gfpb-log', array( __CLASS__, 'render_log' ) );
	}

	public static function enqueue() {
		if ( ! self::is_app() ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'gfpb-fonts', 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Tajawal:wght@400;500;700;800&family=Almarai:wght@400;700;800&display=swap', array(), null );
		wp_enqueue_style( 'gfpb-app', GFPB_URL . 'dist/app.css', array(), filemtime( GFPB_DIR . 'dist/app.css' ) );
		wp_enqueue_script( 'gfpb-app', GFPB_URL . 'dist/app.js', array(), filemtime( GFPB_DIR . 'dist/app.js' ), true );

		$user = wp_get_current_user();
		$json = json_decode( (string) file_get_contents( GFPB_DIR . 'assets/presets/russia.json' ), true );
		wp_localize_script(
			'gfpb-app',
			'GFPB',
			array(
				'root'        => esc_url_raw( rest_url( GFPB_REST::NS ) ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'caps'        => GFPB_Capabilities::current(),
				'user'        => array( 'id' => $user->ID, 'name' => $user->display_name ),
				'settings'    => GFPB_Settings::get(),
				'logo'        => GFPB_URL . 'assets/logo.jpg',
				'presetUrl'   => GFPB_URL . 'assets/presets/img/',
				'presetImages'=> $json['images'] ?? new stdClass(),
				'packagesUrl' => post_type_exists( 'package' ) ? admin_url( 'edit.php?post_type=package' ) : '',
				'hasPackages' => post_type_exists( 'package' ),
				'logUrl'      => admin_url( 'admin.php?page=gfpb-log' ),
				'open'        => array(
					'program' => isset( $_GET['program'] ) ? absint( $_GET['program'] ) : 0, // phpcs:ignore WordPress.Security.NonceVerification
					'package' => isset( $_GET['from_package'] ) ? absint( $_GET['from_package'] ) : 0, // phpcs:ignore WordPress.Security.NonceVerification
				),
			)
		);
	}

	public static function render_app() {
		echo '<div id="gfpb-app" dir="rtl"><p style="padding:40px;text-align:center">' . esc_html__( 'جارٍ تحميل الأداة…', 'gofly-pb' ) . '</p></div>';
	}

	/* Website package shortcuts ---------------------------------------- */

	public static function row_action( $actions, $post ) {
		if ( 'package' === $post->post_type && current_user_can( 'gfpb_use' ) ) {
			$actions['gfpb'] = '<a href="' . esc_url( self::app_url( array( 'from_package' => $post->ID ) ) ) . '">' . esc_html__( 'إنشاء برنامج PDF', 'gofly-pb' ) . '</a>';
		}
		return $actions;
	}

	public static function metabox() {
		if ( ! current_user_can( 'gfpb_use' ) ) {
			return;
		}
		add_meta_box(
			'gfpb-package',
			__( 'برامج PDF', 'gofly-pb' ),
			function ( $post ) {
				$linked = get_posts(
					array(
						'post_type'      => GFPB_Programs::TYPE,
						'post_status'    => 'private',
						'meta_key'       => '_gfpb_package_id', // phpcs:ignore WordPress.DB.SlowDBQuery
						'meta_value'     => $post->ID, // phpcs:ignore WordPress.DB.SlowDBQuery
						'posts_per_page' => 10,
						'author'         => current_user_can( 'gfpb_manage_all' ) ? '' : get_current_user_id(),
					)
				);
				echo '<p><a class="button button-primary" href="' . esc_url( self::app_url( array( 'from_package' => $post->ID ) ) ) . '">' . esc_html__( 'إنشاء برنامج PDF من هذه الباقة', 'gofly-pb' ) . '</a></p>';
				if ( $linked ) {
					echo '<p><b>' . esc_html__( 'برامج مرتبطة:', 'gofly-pb' ) . '</b></p><ul>';
					foreach ( $linked as $program ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( self::app_url( array( 'program' => $program->ID ) ) ), esc_html( get_post_meta( $program->ID, '_gfpb_code', true ) . ' — ' . $program->post_title ) );
					}
					echo '</ul>';
				}
			},
			'package',
			'side'
		);
	}

	/* Activity log ------------------------------------------------------ */

	private static function log_filters() {
		// phpcs:disable WordPress.Security.NonceVerification
		return array(
			'user_id' => isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0,
			'action'  => isset( $_GET['log_action'] ) ? sanitize_key( $_GET['log_action'] ) : '',
			'from'    => isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '',
			'to'      => isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '',
			'page'    => isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1,
		);
		// phpcs:enable
	}

	public static function render_log() {
		if ( ! current_user_can( 'gfpb_view_log' ) ) {
			wp_die( esc_html__( 'لا تملك صلاحية عرض السجل.', 'gofly-pb' ) );
		}
		$filters = self::log_filters();
		$result  = GFPB_Activity_Log::query( $filters );
		$labels  = GFPB_Activity_Log::labels();
		$users   = get_users( array( 'capability' => 'gfpb_use', 'fields' => array( 'ID', 'display_name' ) ) );
		$pages   = (int) ceil( $result['total'] / 50 );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'سجل نشاط برامج PDF', 'gofly-pb' ); ?></h1>
			<form method="get" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end;margin:16px 0">
				<input type="hidden" name="page" value="gfpb-log">
				<label><?php esc_html_e( 'المستخدم', 'gofly-pb' ); ?><br>
					<select name="user_id"><option value=""><?php esc_html_e( 'الكل', 'gofly-pb' ); ?></option>
						<?php foreach ( $users as $u ) : ?>
							<option value="<?php echo (int) $u->ID; ?>" <?php selected( $filters['user_id'], $u->ID ); ?>><?php echo esc_html( $u->display_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label><?php esc_html_e( 'العملية', 'gofly-pb' ); ?><br>
					<select name="log_action"><option value=""><?php esc_html_e( 'الكل', 'gofly-pb' ); ?></option>
						<?php foreach ( $labels as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $filters['action'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label><?php esc_html_e( 'من', 'gofly-pb' ); ?><br><input type="date" name="from" value="<?php echo esc_attr( $filters['from'] ); ?>"></label>
				<label><?php esc_html_e( 'إلى', 'gofly-pb' ); ?><br><input type="date" name="to" value="<?php echo esc_attr( $filters['to'] ); ?>"></label>
				<button class="button"><?php esc_html_e( 'تصفية', 'gofly-pb' ); ?></button>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_merge( array( 'action' => 'gfpb_log_csv' ), array_filter( $filters ) ), admin_url( 'admin-post.php' ) ), 'gfpb_log_csv' ) ); ?>"><?php esc_html_e( 'تصدير CSV', 'gofly-pb' ); ?></a>
			</form>
			<table class="widefat striped">
				<thead><tr>
					<th><?php esc_html_e( 'الوقت', 'gofly-pb' ); ?></th>
					<th><?php esc_html_e( 'المستخدم', 'gofly-pb' ); ?></th>
					<th><?php esc_html_e( 'الدور', 'gofly-pb' ); ?></th>
					<th><?php esc_html_e( 'العملية', 'gofly-pb' ); ?></th>
					<th><?php esc_html_e( 'البرنامج', 'gofly-pb' ); ?></th>
					<th><?php esc_html_e( 'الباقة', 'gofly-pb' ); ?></th>
					<th><?php esc_html_e( 'تفاصيل', 'gofly-pb' ); ?></th>
					<th>IP</th>
				</tr></thead>
				<tbody>
				<?php if ( ! $result['items'] ) : ?>
					<tr><td colspan="8"><?php esc_html_e( 'لا توجد سجلات.', 'gofly-pb' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $result['items'] as $row ) : ?>
					<tr>
						<td><?php echo esc_html( get_date_from_gmt( $row['created_at'], 'Y-m-d H:i' ) ); ?></td>
						<td><?php echo esc_html( get_the_author_meta( 'display_name', $row['user_id'] ) ); ?></td>
						<td><?php echo esc_html( translate_user_role( ucfirst( $row['user_role'] ) ) ); ?></td>
						<td><?php echo esc_html( $labels[ $row['action'] ] ?? $row['action'] ); ?></td>
						<td>
							<?php if ( $row['object_id'] && 'gf_program' === get_post_type( $row['object_id'] ) ) : ?>
								<a href="<?php echo esc_url( self::app_url( array( 'program' => $row['object_id'] ) ) ); ?>"><?php echo esc_html( $row['code'] ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $row['code'] ); ?>
							<?php endif; ?>
						</td>
						<td><?php echo $row['package_id'] ? '<a href="' . esc_url( get_edit_post_link( $row['package_id'] ) ) . '">' . esc_html( get_the_title( $row['package_id'] ) ) . '</a>' : '—'; ?></td>
						<td><code style="font-size:11px"><?php echo esc_html( (string) $row['details'] ); ?></code></td>
						<td><?php echo esc_html( $row['ip'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php
			if ( $pages > 1 ) {
				echo '<div class="tablenav"><div class="tablenav-pages">' . paginate_links( array( 'base' => add_query_arg( 'paged', '%#%' ), 'total' => $pages, 'current' => $filters['page'] ) ) . '</div></div>'; // phpcs:ignore
			}
			?>
		</div>
		<?php
	}

	public static function export_csv() {
		if ( ! current_user_can( 'gfpb_view_log' ) || ! check_admin_referer( 'gfpb_log_csv' ) ) {
			wp_die( esc_html__( 'غير مسموح.', 'gofly-pb' ) );
		}
		$filters             = self::log_filters();
		$filters['per_page'] = 0;
		$labels              = GFPB_Activity_Log::labels();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=gofly-pb-log-' . gmdate( 'Y-m-d' ) . '.csv' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" ); // Excel-friendly UTF-8.
		fputcsv( $out, array( 'time', 'user', 'role', 'action', 'program', 'package', 'details', 'ip' ) );
		foreach ( GFPB_Activity_Log::query( $filters )['items'] as $row ) {
			fputcsv( $out, array( get_date_from_gmt( $row['created_at'], 'Y-m-d H:i' ), get_the_author_meta( 'display_name', $row['user_id'] ), $row['user_role'], $labels[ $row['action'] ] ?? $row['action'], $row['code'], $row['package_id'] ? get_the_title( $row['package_id'] ) : '', $row['details'], $row['ip'] ) );
		}
		fclose( $out ); // phpcs:ignore
		exit;
	}
}
