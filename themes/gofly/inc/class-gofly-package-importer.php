<?php
/**
 * Package importer (الباقات → استيراد الباقات).
 *
 * Creates or updates packages from a JSON file: one object per package
 * whose keys are the package field names (see group_gofly_package.json)
 * plus title, slug, excerpt, destination, categories, and optional
 * image / gallery URLs. Packages are matched by slug, so the same file
 * can be imported again after corrections.
 *
 * The file may also carry "categories" (package categories created when
 * missing: { slug, name, icon }) and "pages" (e.g. service landing pages:
 * { title, slug, template, fields: { name: value } }).
 *
 * @package gofly
 */

defined( 'ABSPATH' ) || exit;

class Gofly_Package_Importer {

	const ACTION = 'gofly_import_packages';

	/** @var array Package ACF fields: name => field. */
	private $fields = array();

	/** @var array Page ACF fields (page, service, FAQ groups): name => field. */
	private $page_fields = array();

	/** @var array Per-package results for the report. */
	private $report = array();

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle' ) );
	}

	public static function menu() {
		add_submenu_page( 'edit.php?post_type=package', __( 'استيراد الباقات', 'gofly' ), __( 'استيراد الباقات', 'gofly' ), 'manage_options', 'gofly-import-packages', array( __CLASS__, 'render' ) );
	}

	/* Admin screen ------------------------------------------------------ */

	public static function render() {
		$report = get_transient( self::ACTION . '_' . get_current_user_id() );
		delete_transient( self::ACTION . '_' . get_current_user_id() );
		$labels = array(
			'created' => __( 'أُنشئت', 'gofly' ),
			'updated' => __( 'حُدِّثت', 'gofly' ),
			'skipped' => __( 'موجودة مسبقًا — تُركت كما هي', 'gofly' ),
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'استيراد الباقات', 'gofly' ); ?></h1>

			<?php if ( is_string( $report ) ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( $report ); ?></p></div>
			<?php elseif ( $report ) : ?>
				<div class="notice notice-success"><p>
					<?php /* translators: %d: number of items */ echo esc_html( sprintf( __( 'تمت معالجة %d عنصر.', 'gofly' ), count( $report ) ) ); ?>
				</p></div>
				<table class="widefat striped" style="max-width:960px">
					<thead><tr><th><?php esc_html_e( 'العنصر', 'gofly' ); ?></th><th><?php esc_html_e( 'النتيجة', 'gofly' ); ?></th><th><?php esc_html_e( 'للمراجعة', 'gofly' ); ?></th></tr></thead>
					<tbody>
					<?php foreach ( $report as $row ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( get_edit_post_link( $row['id'] ) ); ?>"><?php echo esc_html( get_the_title( $row['id'] ) ); ?></a></td>
							<td><?php echo esc_html( $labels[ $row['result'] ] ); ?></td>
							<td><?php echo $row['notes'] ? '<ul style="margin:0;list-style:disc;padding-inline-start:18px"><li>' . implode( '</li><li>', array_map( 'esc_html', $row['notes'] ) ) . '</li></ul>' : '—'; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<?php if ( ! function_exists( 'update_field' ) ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'فعّل إضافة Advanced Custom Fields Pro أولًا، فبيانات الباقات تُحفظ في حقولها.', 'gofly' ); ?></p></div>
			<?php endif; ?>

			<div class="card" style="max-width:760px">
				<h2><?php esc_html_e( 'ملف الباقات (JSON)', 'gofly' ); ?></h2>
				<p><?php esc_html_e( 'تُطابَق الباقات والصفحات بالرابط المختصر (slug): الموجودة تُحدَّث، والجديدة تُنشأ. تُنشأ الوجهات غير الموجودة تلقائيًا.', 'gofly' ); ?></p>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( self::ACTION ); ?>
					<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
					<p><input type="file" name="file" accept=".json,application/json" required></p>
					<p>
						<label for="gofly-status"><?php esc_html_e( 'حالة العناصر الجديدة:', 'gofly' ); ?></label>
						<select id="gofly-status" name="status">
							<option value="draft"><?php esc_html_e( 'مسودة (أراجعها وأضيف الصور ثم أنشرها)', 'gofly' ); ?></option>
							<option value="publish"><?php esc_html_e( 'منشورة مباشرة', 'gofly' ); ?></option>
						</select>
					</p>
					<p><label><input type="checkbox" name="update" value="1" checked> <?php esc_html_e( 'تحديث العناصر الموجودة بنفس الرابط المختصر', 'gofly' ); ?></label></p>
					<?php submit_button( __( 'استيراد', 'gofly' ) ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	public static function handle() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( self::ACTION ) ) {
			wp_die( esc_html__( 'غير مسموح.', 'gofly' ) );
		}
		$file = $_FILES['file']['tmp_name'] ?? ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$data = $file && is_uploaded_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
		$pages = (array) ( $data['pages'] ?? array() );
		$cats  = (array) ( $data['categories'] ?? array() );
		$data  = isset( $data[0] ) ? $data : (array) ( $data['packages'] ?? array() );

		if ( ! function_exists( 'update_field' ) ) {
			$report = __( 'إضافة ACF Pro غير مفعّلة.', 'gofly' );
		} elseif ( ! isset( $data[0]['title'] ) && ! isset( $pages[0]['title'] ) ) {
			$report = __( 'الملف ليس ملف باقات JSON صالحًا.', 'gofly' );
		} else {
			$importer = new self();
			$importer->run( $data, 'publish' === ( $_POST['status'] ?? '' ) ? 'publish' : 'draft', ! empty( $_POST['update'] ), $pages, $cats );
			$report = $importer->report;
		}
		set_transient( self::ACTION . '_' . get_current_user_id(), $report, HOUR_IN_SECONDS );
		wp_safe_redirect( admin_url( 'edit.php?post_type=package&page=gofly-import-packages' ) );
		exit;
	}

	/* Import ------------------------------------------------------------ */

	/**
	 * @param array  $packages Package objects.
	 * @param string $status   Status for new packages.
	 * @param bool   $update   Overwrite packages with the same slug.
	 * @param array  $pages    Page objects.
	 * @param array  $cats     Package categories to create when missing.
	 */
	public function run( $packages, $status = 'draft', $update = true, $pages = array(), $cats = array() ) {
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 600 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$this->fields = self::group_fields( 'package' );
		foreach ( array( 'page', 'service', 'faq' ) as $group ) {
			$this->page_fields += self::group_fields( $group );
		}

		foreach ( $cats as $cat ) {
			if ( ! empty( $cat['slug'] ) && ! get_term_by( 'slug', sanitize_title( $cat['slug'] ), 'package_category' ) ) {
				$term = wp_insert_term( $cat['name'] ?? $cat['slug'], 'package_category', array( 'slug' => sanitize_title( $cat['slug'] ) ) );
				if ( ! is_wp_error( $term ) && ! empty( $cat['icon'] ) ) {
					update_field( 'field_gofly_package_category_icon', $cat['icon'], 'term_' . $term['term_id'] );
				}
			}
		}

		foreach ( $packages as $p ) {
			if ( ! empty( $p['title'] ) ) {
				$this->import( $p, $status, $update );
			}
		}
		foreach ( $pages as $p ) {
			if ( ! empty( $p['title'] ) ) {
				$this->import_page( $p, $status, $update );
			}
		}
	}

	/** Field definitions of one acf-json group: name => field. */
	private static function group_fields( $group ) {
		$json = json_decode( (string) file_get_contents( GOFLY_DIR . '/acf-json/group_gofly_' . $group . '.json' ), true );
		return array_column( array_filter( $json['fields'], fn( $f ) => $f['name'] ), null, 'name' );
	}

	private function import_page( $p, $status, $update ) {
		$slug     = sanitize_title( $p['slug'] ?? $p['title'] );
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing && ! $update ) {
			$this->report[] = array( 'id' => $existing->ID, 'result' => 'skipped', 'notes' => array() );
			return;
		}
		$post = array(
			'post_type'  => 'page',
			'post_title' => $p['title'],
			'post_name'  => $slug,
		);
		$id   = $existing ? wp_update_post( wp_slash( $post + array( 'ID' => $existing->ID ) ) ) : wp_insert_post( wp_slash( $post + array( 'post_status' => $status ) ) );
		if ( ! $id || is_wp_error( $id ) ) {
			return;
		}
		if ( ! empty( $p['template'] ) ) {
			update_post_meta( $id, '_wp_page_template', 'page-templates/' . sanitize_file_name( $p['template'] ) . '.php' );
		}
		foreach ( (array) ( $p['fields'] ?? array() ) as $name => $value ) {
			if ( isset( $this->page_fields[ $name ] ) && null !== $value ) {
				update_field( $this->page_fields[ $name ]['key'], $value, $id );
			}
		}
		$notes = array_values( array_filter( (array) ( $p['owner_notes'] ?? array() ), 'is_string' ) );
		if ( ! empty( $p['image'] ) ) {
			$img = media_sideload_image( esc_url_raw( $p['image'] ), $id, null, 'id' );
			is_wp_error( $img ) ? $notes[] = __( 'تعذّر تحميل الصورة.', 'gofly' ) : set_post_thumbnail( $id, $img );
		}
		$this->report[] = array(
			'id'     => $id,
			'result' => $existing ? 'updated' : 'created',
			'notes'  => $notes,
		);
	}

	private function import( $p, $status, $update ) {
		$slug     = sanitize_title( $p['slug'] ?? $p['title'] );
		$existing = get_page_by_path( $slug, OBJECT, 'package' );
		$notes    = array_values( array_filter( (array) ( $p['owner_notes'] ?? array() ), 'is_string' ) );

		if ( $existing && ! $update ) {
			$this->report[] = array( 'id' => $existing->ID, 'result' => 'skipped', 'notes' => array() );
			return;
		}

		$post = array(
			'post_type'    => 'package',
			'post_title'   => $p['title'],
			'post_name'    => $slug,
			'post_excerpt' => $p['excerpt'] ?? '',
		);
		$id   = $existing ? wp_update_post( wp_slash( $post + array( 'ID' => $existing->ID ) ) ) : wp_insert_post( wp_slash( $post + array( 'post_status' => $status ) ) );
		if ( ! $id || is_wp_error( $id ) ) {
			return;
		}
		if ( ! empty( $p['source_file'] ) ) {
			update_post_meta( $id, '_gofly_import_source', sanitize_text_field( $p['source_file'] ) );
		}

		// Destination: reuse by slug, create if missing.
		if ( ! empty( $p['destination']['slug'] ) ) {
			$d    = $p['destination'];
			$term = get_term_by( 'slug', sanitize_title( $d['slug'] ), 'destination' );
			if ( ! $term ) {
				$new  = wp_insert_term( $d['name'] ?? $d['slug'], 'destination', array( 'slug' => sanitize_title( $d['slug'] ) ) );
				$term = is_wp_error( $new ) ? null : get_term( $new['term_id'], 'destination' );
				if ( $term && ! empty( $d['subtitle'] ) ) {
					update_field( 'field_gofly_destination_subtitle', $d['subtitle'], 'term_' . $term->term_id );
				}
				if ( $term ) {
					$notes[] = __( 'وجهة جديدة: أضف صورتها من الباقات ← الوجهات.', 'gofly' );
				}
			}
			if ( $term ) {
				wp_set_object_terms( $id, array( (int) $term->term_id ), 'destination' );
			}
		}

		// Categories: existing ones (including those created from the file's "categories" list).
		$cats = array();
		foreach ( (array) ( $p['categories'] ?? array() ) as $slug_cat ) {
			$term = get_term_by( 'slug', sanitize_title( $slug_cat ), 'package_category' );
			if ( $term ) {
				$cats[] = (int) $term->term_id;
			} else {
				/* translators: %s: category slug */
				$notes[] = sprintf( __( 'الفئة "%s" غير موجودة فلم تُضف.', 'gofly' ), $slug_cat );
			}
		}
		if ( $cats ) {
			wp_set_object_terms( $id, $cats, 'package_category' );
		}

		// ACF fields by key; images are handled below.
		foreach ( $this->fields as $name => $field ) {
			if ( ! array_key_exists( $name, $p ) || null === $p[ $name ] || in_array( $field['type'], array( 'image', 'gallery', 'file' ), true ) ) {
				continue;
			}
			update_field( $field['key'], $p[ $name ], $id );
		}

		// Optional photos by URL.
		if ( ! empty( $p['image'] ) ) {
			$img = media_sideload_image( esc_url_raw( $p['image'] ), $id, null, 'id' );
			is_wp_error( $img ) ? $notes[] = __( 'تعذّر تحميل الصورة البارزة.', 'gofly' ) : set_post_thumbnail( $id, $img );
		}
		if ( ! empty( $p['gallery'] ) ) {
			$ids = array();
			foreach ( (array) $p['gallery'] as $url ) {
				$img = media_sideload_image( esc_url_raw( $url ), $id, null, 'id' );
				if ( ! is_wp_error( $img ) ) {
					$ids[] = $img;
				}
			}
			update_field( $this->fields['gallery']['key'], $ids, $id );
		}
		if ( ! has_post_thumbnail( $id ) && ! get_field( 'gallery', $id ) ) {
			$notes[] = __( 'بدون صور: أضف صورة بارزة وصور المعرض.', 'gofly' );
		}
		if ( empty( $p['price'] ) && ! get_field( 'price', $id ) ) {
			$notes[] = __( 'بدون سعر: يظهر زر "اطلب عرض السعر".', 'gofly' );
		}

		$this->report[] = array(
			'id'     => $id,
			'result' => $existing ? 'updated' : 'created',
			'notes'  => array_values( array_unique( $notes ) ),
		);
	}
}

Gofly_Package_Importer::init();
