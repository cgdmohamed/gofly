<?php
/**
 * One-click demo content importer (Appearance → استيراد المحتوى التجريبي).
 *
 * Creates the design's content: pages with their templates, home page
 * sections, destinations, package categories, packages (with the full
 * Russia programme), blog posts, menus and Site Settings. Everything it
 * creates is recorded so "حذف المحتوى التجريبي" removes exactly that.
 *
 * ACF values are written through the field keys from /acf-json, so
 * repeaters, galleries and links are stored exactly as ACF expects.
 *
 * @package gofly
 */

defined( 'ABSPATH' ) || exit;

class Gofly_Demo_Importer {

	const LOG    = 'gofly_demo_import';
	const ACTION = 'gofly_demo';

	/** @var array Created items: posts, terms, menus, options. */
	private $log = array( 'posts' => array(), 'terms' => array(), 'menus' => array(), 'options' => array() );

	/** @var array Image cache: reference => attachment ID. */
	private $images = array();

	/** @var array Created IDs by demo key: pages, packages, dest, cat. */
	private $ids = array( 'page' => array(), 'package' => array(), 'dest' => array(), 'cat' => array() );

	/** @var array ACF field map: group => name => field. */
	private $fields = array();

	/** @var bool Download Unsplash photos (otherwise placeholders). */
	private $remote = true;

	/** @var int Images that fell back to placeholders. */
	private $fallbacks = 0;

	/** @var int[] Attachment IDs that are generated placeholders. */
	private $placeholders = array();

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle' ) );
	}

	public static function menu() {
		add_theme_page( __( 'استيراد المحتوى التجريبي', 'gofly' ), __( 'استيراد المحتوى التجريبي', 'gofly' ), 'manage_options', 'gofly-demo', array( __CLASS__, 'render' ) );
	}

	/* Admin screen ------------------------------------------------------ */

	public static function render() {
		$log      = get_option( self::LOG );
		$has_acf  = function_exists( 'update_field' );
		$notice   = isset( $_GET['gofly_demo'] ) ? sanitize_key( $_GET['gofly_demo'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$messages = array(
			'imported' => __( 'تم استيراد المحتوى التجريبي بنجاح.', 'gofly' ),
			'removed'  => __( 'تم حذف المحتوى التجريبي.', 'gofly' ),
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'استيراد المحتوى التجريبي', 'gofly' ); ?></h1>

			<?php if ( isset( $messages[ $notice ] ) ) : ?>
				<div class="notice notice-success"><p><?php echo esc_html( $messages[ $notice ] ); ?>
					<?php if ( 'imported' === $notice && ! empty( $log['fallbacks'] ) ) : ?>
						<?php /* translators: %d: number of images */ echo esc_html( sprintf( __( '(%d صورة لم تُحمَّل من الإنترنت فاستُبدلت بصور مؤقتة.)', 'gofly' ), $log['fallbacks'] ) ); ?>
					<?php endif; ?>
				</p></div>
			<?php endif; ?>

			<?php if ( ! $has_acf ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'فعّل إضافة Advanced Custom Fields Pro أولًا، فالمحتوى التجريبي يُحفظ في حقولها.', 'gofly' ); ?></p></div>
			<?php endif; ?>

			<div class="card" style="max-width:760px">
				<?php if ( $log ) : ?>
					<h2><?php esc_html_e( 'المحتوى التجريبي مستورد', 'gofly' ); ?></h2>
					<p>
						<?php
						/* translators: 1: date, 2: posts, 3: terms, 4: menus */
						echo esc_html( sprintf( __( 'استُورد في %1$s: %2$d عنصر (صفحات، باقات، مقالات، صور)، %3$d تصنيف، %4$d قوائم.', 'gofly' ), wp_date( 'Y-m-d H:i', $log['time'] ), count( $log['posts'] ), count( $log['terms'] ), count( $log['menus'] ) ) );
						?>
					</p>
					<p><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'عرض الموقع', 'gofly' ); ?></a></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'سيُحذف كل ما أنشأه الاستيراد التجريبي، بما فيه أي تعديلات أجريتها عليه. متأكد؟', 'gofly' ) ); ?>')">
						<?php wp_nonce_field( self::ACTION ); ?>
						<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
						<input type="hidden" name="task" value="remove">
						<?php submit_button( __( 'حذف المحتوى التجريبي', 'gofly' ), 'delete' ); ?>
					</form>
				<?php else : ?>
					<h2><?php esc_html_e( 'ماذا سيُستورد؟', 'gofly' ); ?></h2>
					<ul style="list-style:disc;padding-inline-start:20px">
						<li><?php esc_html_e( 'الصفحات: الرئيسية، المدونة، الوجهات، احجز الآن، تواصل معنا، من نحن — مع قوالبها وضبط الصفحة الرئيسية وصفحة المقالات.', 'gofly' ); ?></li>
						<li><?php esc_html_e( 'كل أقسام الصفحة الرئيسية: الواجهة، الخدمات، الوجهات، الباقات، الخطوات، المعرض، إنستغرام، الآراء، الأسئلة الشائعة.', 'gofly' ); ?></li>
						<li><?php esc_html_e( '8 وجهات و5 فئات و9 باقات، منها برنامج روسيا الكامل (الأيام، الفنادق، السيارات، الغرف، المطاعم، السياسات).', 'gofly' ); ?></li>
						<li><?php esc_html_e( '6 مقالات في المدونة، والقائمة الرئيسية بقوائمها المنسدلة، وقوائم الفوتر.', 'gofly' ); ?></li>
						<li><?php esc_html_e( 'إعدادات الموقع: الأرقام، الشركاء، حسابات التواصل.', 'gofly' ); ?></li>
					</ul>
					<p><?php esc_html_e( 'لا يُحذف أو يُعدَّل أي محتوى موجود. يمكنك حذف المحتوى التجريبي لاحقًا بضغطة واحدة.', 'gofly' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( self::ACTION ); ?>
						<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
						<input type="hidden" name="task" value="import">
						<p><label><input type="checkbox" name="remote" value="1" checked> <?php esc_html_e( 'تحميل صور الوجهات من Unsplash (تحتاج اتصالًا بالإنترنت، وتستغرق دقيقة تقريبًا)', 'gofly' ); ?></label></p>
						<?php submit_button( __( 'استيراد المحتوى التجريبي', 'gofly' ), 'primary', 'submit', true, $has_acf ? array() : array( 'disabled' => 'disabled' ) ); ?>
					</form>
				<?php endif; ?>
			</div>

			<div class="card" style="max-width:760px">
				<h2><?php esc_html_e( 'بعد الاستيراد', 'gofly' ); ?></h2>
				<ol style="padding-inline-start:20px">
					<li><?php esc_html_e( 'أنشئ نموذجي التواصل وطلب عرض السعر في Fluent Forms، ثم اخترهما من صفحة "تواصل معنا" ومن إعدادات الموقع ← الباقات.', 'gofly' ); ?></li>
					<li><?php esc_html_e( 'استبدل الصور والأسعار والنصوص التجريبية بمحتواك الحقيقي.', 'gofly' ); ?></li>
				</ol>
			</div>
		</div>
		<?php
	}

	public static function handle() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( self::ACTION ) ) {
			wp_die( esc_html__( 'غير مسموح.', 'gofly' ) );
		}
		$task = isset( $_POST['task'] ) ? sanitize_key( $_POST['task'] ) : '';
		if ( 'remove' === $task ) {
			self::remove();
			$result = 'removed';
		} else {
			if ( get_option( self::LOG ) || ! function_exists( 'update_field' ) ) {
				wp_safe_redirect( admin_url( 'themes.php?page=gofly-demo' ) );
				exit;
			}
			$importer         = new self();
			$importer->remote = ! empty( $_POST['remote'] );
			$importer->run();
			$result = 'imported';
		}
		wp_safe_redirect( admin_url( 'themes.php?page=gofly-demo&gofly_demo=' . $result ) );
		exit;
	}

	/* Import ------------------------------------------------------------ */

	public function run() {
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 600 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$this->fields = self::field_map();
		$data         = require GOFLY_DIR . '/inc/demo/data.php';

		// Service landing pages share their content with the package importer file.
		foreach ( json_decode( (string) file_get_contents( GOFLY_DIR . '/inc/demo/services.json' ), true ) as $service ) {
			$fields = $service['fields'];
			unset( $fields['page_subtitle'] );
			$data['pages'][ $service['slug'] ] = array( $service['title'], $service['template'], $service['fields']['page_subtitle'], $fields );
		}

		if ( ! get_option( 'permalink_structure' ) ) {
			update_option( 'permalink_structure', '/%postname%/' );
		}

		$this->import_terms( $data );
		$this->import_pages( $data['pages'] );
		$this->import_packages( $data['packages'] );
		$this->import_posts( $data['posts'] );
		$this->import_testimonials( $data['testimonials'] );
		$this->import_home( $data['home'] );
		$this->import_options( $data['options'] );
		$this->import_menus( $data['menus'] );

		flush_rewrite_rules( false );
		update_option(
			self::LOG,
			$this->log + array(
				'time'      => time(),
				'fallbacks' => $this->fallbacks,
				'reading'   => array( get_option( 'show_on_front' ), get_option( 'page_on_front' ), get_option( 'page_for_posts' ) ),
			),
			false
		);
	}

	private function import_terms( $data ) {
		foreach ( $data['destinations'] as $slug => list( $name, $subtitle, $image ) ) {
			$id = $this->term( $name, $slug, 'destination' );
			$this->ids['dest'][ $slug ] = $id;
			$this->set( 'destination', 'subtitle', $subtitle, 'term_' . $id );
			$this->set( 'destination', 'image', $image, 'term_' . $id );
		}
		foreach ( $data['categories'] as $slug => list( $name, $icon ) ) {
			$id = $this->term( $name, $slug, 'package_category' );
			$this->ids['cat'][ $slug ] = $id;
			$this->set( 'package_category', 'icon', $icon, 'term_' . $id );
		}
	}

	private function import_pages( $pages ) {
		foreach ( $pages as $key => list( $title, $template, $subtitle ) ) {
			$id = $this->post(
				array(
					'post_type'   => 'page',
					'post_title'  => $title,
					'post_name'   => 'home' === $key ? 'home' : $key,
					'page_template' => $template ? 'page-templates/' . $template . '.php' : '',
				)
			);
			$this->ids['page'][ $key ] = $id;
		}
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $this->ids['page']['home'] );
		update_option( 'page_for_posts', $this->ids['page']['blog'] );

		// Fields after the template / reading settings are in place (ACF locations depend on them).
		foreach ( $pages as $key => $page ) {
			$id = $this->ids['page'][ $key ];
			if ( $page[2] ) {
				$this->set( 'page', 'page_subtitle', $page[2], $id );
			}
			foreach ( $page[3] as $name => $value ) {
				$this->set( $this->group_of( $name, array( 'faq', 'about', 'contact', 'service' ) ), $name, $value, $id );
			}
		}
	}

	private function import_packages( $packages ) {
		$order = 0;
		foreach ( $packages as $key => list( $title, $dest, $days, $nights, $price, $old, $rating, $cats, $image, $extra ) ) {
			$id = $this->post(
				array(
					'post_type'    => 'package',
					'post_title'   => $title,
					'post_excerpt' => 'باقة سياحية متكاملة بأسعار شفافة وخدمة عربية.',
					'menu_order'   => $order++,
				)
			);
			$this->ids['package'][ $key ] = $id;
			$this->thumbnail( $id, $image );
			wp_set_object_terms( $id, array( (int) $this->ids['dest'][ $dest ] ), 'destination' );
			wp_set_object_terms( $id, array_map( fn( $c ) => (int) $this->ids['cat'][ $c ], $cats ), 'package_category' );
			$fields = array( 'duration_days' => $days, 'duration_nights' => $nights, 'price' => $price, 'rating' => $rating ) + ( $old ? array( 'old_price' => $old ) : array() ) + $extra;
			foreach ( $fields as $name => $value ) {
				$this->set( 'package', $name, $value, $id );
			}
		}
	}

	private function import_posts( $posts ) {
		$author = get_current_user_id();
		foreach ( $posts as $p ) {
			$category = term_exists( $p[1], 'category' );
			if ( ! $category ) {
				$category = wp_insert_term( $p[1], 'category' );
				$this->log['terms'][] = array( (int) $category['term_id'], 'category' );
			}
			$id = $this->post(
				array(
					'post_type'     => 'post',
					'post_title'    => $p[0],
					'post_excerpt'  => $p[2],
					'post_content'  => $p[5] ?? '<!-- wp:paragraph --><p>' . $p[2] . '</p><!-- /wp:paragraph -->',
					'post_date'     => $p[4] . ' 10:00:00',
					'post_author'   => $author,
					'post_category' => array( (int) $category['term_id'] ),
					'tags_input'    => $p[6] ?? array(),
				)
			);
			$this->thumbnail( $id, $p[3] );
			if ( ! empty( $p[5] ) ) {
				$this->set( 'post', 'related_package', array( 'package' => 'russia' ), $id );
			}
		}
	}

	private function import_testimonials( $testimonials ) {
		foreach ( $testimonials as $order => $t ) {
			$id = $this->post(
				array(
					'post_type'  => 'testimonial',
					'post_title' => $t['name'],
					'menu_order' => $order,
				)
			);
			$this->thumbnail( $id, $t['avatar'] );
			foreach ( array( 'quote', 'trip', 'rating' ) as $name ) {
				$this->set( 'testimonial', $name, $t[ $name ], $id );
			}
		}
	}

	private function import_home( $fields ) {
		$id = $this->ids['page']['home'];
		foreach ( $fields as $name => $value ) {
			$this->set( $this->group_of( $name, array( 'home', 'faq' ) ), $name, $value, $id );
		}
	}

	private function import_options( $options ) {
		foreach ( $options as $name => $value ) {
			$this->set( 'settings', $name, $value, 'option' );
			$this->log['options'][] = $name;
		}
	}

	private function import_menus( $menus ) {
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		foreach ( $menus as $location => $menu ) {
			$name = $menu['name'];
			$i    = 2;
			while ( wp_get_nav_menu_object( $name ) ) {
				$name = $menu['name'] . ' ' . $i++;
			}
			$menu_id               = wp_create_nav_menu( $name );
			$this->log['menus'][]  = $menu_id;
			$locations[ $location ] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations ); // Children's ACF location (primary) needs this first.
			foreach ( $menu['items'] as $item ) {
				$parent = $this->menu_item( $menu_id, $item[0], $item[1], 0 );
				foreach ( $item[2] ?? array() as $child ) {
					$child_id = $this->menu_item( $menu_id, $child[0], $child[1], $parent );
					$this->set( 'menu_item', 'menu_icon', $child[2], $child_id );
					if ( isset( $child[3] ) ) {
						$this->set( 'menu_item', 'menu_subtitle', $child[3], $child_id );
					}
				}
			}
		}
	}

	/* Building blocks --------------------------------------------------- */

	private function post( $args ) {
		$template = $args['page_template'] ?? '';
		unset( $args['page_template'] );
		$id = wp_insert_post( wp_slash( $args + array( 'post_status' => 'publish' ) ) );
		if ( $template ) {
			update_post_meta( $id, '_wp_page_template', $template );
		}
		update_post_meta( $id, '_gofly_demo', 1 );
		$this->log['posts'][] = $id;
		return $id;
	}

	private function term( $name, $slug, $taxonomy ) {
		$existing = get_term_by( 'slug', $slug, $taxonomy );
		if ( $existing ) {
			return (int) $existing->term_id; // Reuse, and do not delete it on removal.
		}
		$term                 = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
		$this->log['terms'][] = array( (int) $term['term_id'], $taxonomy );
		return (int) $term['term_id'];
	}

	private function menu_item( $menu_id, $title, $link, $parent ) {
		return wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $title,
				'menu-item-url'       => $this->url( $link ),
				'menu-item-type'      => 'custom',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent,
			)
		);
	}

	private function thumbnail( $post_id, $ref ) {
		$id = $this->image( $ref );
		if ( $id ) {
			set_post_thumbnail( $post_id, $id );
		}
	}

	/** Resolve a demo link reference to a URL. */
	private function url( $link ) {
		if ( isset( $link['url'] ) ) {
			return $link['url'];
		}
		if ( isset( $link['home'] ) ) {
			return home_url( '/' );
		}
		if ( isset( $link['privacy'] ) ) {
			return get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/' );
		}
		if ( isset( $link['page'] ) ) {
			return get_permalink( $this->ids['page'][ $link['page'] ] );
		}
		if ( isset( $link['package'] ) ) {
			return get_permalink( $this->ids['package'][ $link['package'] ] );
		}
		if ( isset( $link['archive'] ) ) {
			return get_post_type_archive_link( $link['archive'] );
		}
		if ( isset( $link['dest'] ) ) {
			return get_term_link( (int) $this->ids['dest'][ $link['dest'] ], 'destination' );
		}
		if ( isset( $link['cat'] ) ) {
			return get_term_link( (int) $this->ids['cat'][ $link['cat'] ], 'package_category' );
		}
		return home_url( '/' );
	}

	/** Pick which field group defines a field name. */
	private function group_of( $name, $groups ) {
		foreach ( $groups as $group ) {
			if ( isset( $this->fields[ $group ][ $name ] ) ) {
				return $group;
			}
		}
		return $groups[0];
	}

	/** ACF field definitions from /acf-json: group slug => name => field. */
	private static function field_map() {
		$map = array();
		foreach ( glob( GOFLY_DIR . '/acf-json/group_gofly_*.json' ) as $file ) {
			$group = json_decode( (string) file_get_contents( $file ), true );
			$slug  = substr( $group['key'], strlen( 'group_gofly_' ) );
			foreach ( $group['fields'] as $field ) {
				if ( $field['name'] ) {
					$map[ $slug ][ $field['name'] ] = $field;
				}
			}
		}
		return $map;
	}

	/**
	 * Write one ACF field, converting demo references to what ACF stores.
	 *
	 * @param string     $group  Field group slug (e.g. 'package').
	 * @param string     $name   Field name.
	 * @param mixed      $value  Demo value.
	 * @param int|string $target Post ID, 'option' or 'term_{id}'.
	 */
	private function set( $group, $name, $value, $target ) {
		$field = $this->fields[ $group ][ $name ] ?? null;
		if ( ! $field ) {
			return;
		}
		update_field( $field['key'], $this->value( $field, $value ), $target );
	}

	private function value( $field, $value ) {
		switch ( $field['type'] ) {
			case 'image':
			case 'file':
				return $this->image( $value );
			case 'gallery':
				return array_values( array_filter( array_map( array( $this, 'image' ), (array) $value ) ) );
			case 'link':
				return array(
					'url'    => $this->url( $value ),
					'title'  => $value['title'] ?? '',
					'target' => '',
				);
			case 'taxonomy':
				return array_map( fn( $slug ) => (int) $this->ids['dest'][ $slug ], (array) $value );
			case 'relationship':
				return array_map( fn( $key ) => (int) $this->ids['package'][ $key ], (array) $value );
			case 'post_object':
				return (int) $this->ids['package'][ $value['package'] ];
			case 'repeater':
				$subs = array_column( $field['sub_fields'], null, 'name' );
				return array_map(
					function ( $row ) use ( $subs ) {
						foreach ( $row as $k => $v ) {
							if ( isset( $subs[ $k ] ) ) {
								$row[ $k ] = $this->value( $subs[ $k ], $v );
							}
						}
						return $row;
					},
					(array) $value
				);
			default:
				return $value;
		}
	}

	/**
	 * Attachment ID for an image reference ('u:<unsplash id>' or 'f:<file>').
	 *
	 * @param string $ref         Reference.
	 * @param bool   $placeholder Generate a placeholder when the image cannot be fetched.
	 * @return int
	 */
	private function image( $ref, $placeholder = true ) {
		if ( ! $ref || ! is_string( $ref ) ) {
			return 0;
		}
		if ( isset( $this->images[ $ref ] ) ) {
			return $this->images[ $ref ];
		}
		if ( false !== strpos( $ref, '|' ) ) {
			// Alternatives, e.g. 'u:<id>|f:<file>': the first that can be fetched wins;
			// only the last one may fall back to a placeholder.
			$alts = explode( '|', $ref );
			foreach ( $alts as $i => $alt ) {
				$id = $this->image( $alt, count( $alts ) - 1 === $i );
				if ( $id && ! in_array( $id, $this->placeholders, true ) ) {
					break;
				}
			}
			return $this->images[ $ref ] = $id;
		}
		$tmp  = '';
		$name = '';
		if ( 0 === strpos( $ref, 'f:' ) ) {
			$src  = GOFLY_DIR . '/assets/demo/' . substr( $ref, 2 );
			$name = 'gofly-demo-' . basename( $src );
			$tmp  = wp_tempnam( $name );
			copy( $src, $tmp );
		} elseif ( 0 === strpos( $ref, 'u:' ) && $this->remote ) {
			$name = 'gofly-demo-' . substr( $ref, 2 ) . '.jpg';
			$tmp  = download_url( 'https://images.unsplash.com/photo-' . substr( $ref, 2 ) . '?auto=format&fit=crop&w=1400&q=75', 30 );
			if ( is_wp_error( $tmp ) ) {
				$tmp = '';
			}
		}
		if ( ! $tmp && ! $placeholder ) {
			return 0;
		}
		if ( ! $tmp ) {
			$this->fallbacks++;
			$name = 'gofly-demo-' . md5( $ref ) . '.jpg';
			$tmp  = $this->placeholder( $ref );
		}
		if ( ! $tmp ) {
			return $this->images[ $ref ] = 0;
		}
		$id = media_handle_sideload( array( 'name' => $name, 'tmp_name' => $tmp ), 0 );
		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );
			return $this->images[ $ref ] = 0;
		}
		update_post_meta( $id, '_gofly_demo', 1 );
		$this->log['posts'][] = $id;
		if ( 0 === strpos( $name, 'gofly-demo-' . md5( $ref ) ) ) {
			$this->placeholders[] = $id;
		}
		return $this->images[ $ref ] = $id;
	}

	/** Brand-coloured gradient image when a photo cannot be downloaded. */
	private function placeholder( $ref ) {
		if ( ! function_exists( 'imagecreatetruecolor' ) ) {
			return '';
		}
		$palette = array( array( array( 4, 56, 103 ), array( 201, 154, 46 ) ), array( array( 10, 90, 163 ), array( 160, 210, 230 ) ), array( array( 3, 42, 80 ), array( 232, 197, 100 ) ) );
		list( $a, $b ) = $palette[ hexdec( substr( md5( $ref ), 0, 2 ) ) % count( $palette ) ];
		$w  = 1200;
		$h  = 800;
		$im = imagecreatetruecolor( $w, $h );
		for ( $y = 0; $y < $h; $y++ ) {
			$t = $y / $h;
			imageline( $im, 0, $y, $w, $y, imagecolorallocate( $im, (int) ( $a[0] + ( $b[0] - $a[0] ) * $t ), (int) ( $a[1] + ( $b[1] - $a[1] ) * $t ), (int) ( $a[2] + ( $b[2] - $a[2] ) * $t ) ) );
		}
		$file = wp_tempnam( 'gofly-demo' );
		imagejpeg( $im, $file, 82 );
		imagedestroy( $im );
		return $file;
	}

	/* Removal ----------------------------------------------------------- */

	public static function remove() {
		$log = get_option( self::LOG );
		if ( ! $log ) {
			return;
		}
		foreach ( array_reverse( $log['posts'] ) as $id ) {
			if ( get_post_meta( $id, '_gofly_demo', true ) ) {
				'attachment' === get_post_type( $id ) ? wp_delete_attachment( $id, true ) : wp_delete_post( $id, true );
			}
		}
		foreach ( $log['terms'] as list( $id, $taxonomy ) ) {
			wp_delete_term( $id, $taxonomy );
		}
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		foreach ( $log['menus'] as $menu_id ) {
			wp_delete_nav_menu( $menu_id );
			$locations = array_filter( $locations, fn( $id ) => (int) $id !== (int) $menu_id );
		}
		set_theme_mod( 'nav_menu_locations', $locations );
		foreach ( $log['options'] as $name ) {
			function_exists( 'delete_field' ) ? delete_field( $name, 'option' ) : delete_option( 'options_' . $name );
		}
		if ( ! get_post( (int) get_option( 'page_on_front' ) ) ) {
			update_option( 'show_on_front', 'posts' );
			update_option( 'page_on_front', 0 );
			update_option( 'page_for_posts', 0 );
		}
		delete_option( self::LOG );
	}
}

Gofly_Demo_Importer::init();
