<?php
/**
 * Custom post types and taxonomies.
 *
 * Packages (باقات) are the bookable programmes. Destinations and package
 * categories are taxonomies used for the cards and the archive filters.
 *
 * @package gofly
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'package',
			array(
				'labels'        => array(
					'name'          => __( 'الباقات', 'gofly' ),
					'singular_name' => __( 'باقة', 'gofly' ),
					'add_new'       => __( 'إضافة باقة', 'gofly' ),
					'add_new_item'  => __( 'إضافة باقة جديدة', 'gofly' ),
					'edit_item'     => __( 'تعديل الباقة', 'gofly' ),
					'all_items'     => __( 'كل الباقات', 'gofly' ),
					'search_items'  => __( 'بحث في الباقات', 'gofly' ),
				),
				'public'        => true,
				'has_archive'   => 'packages',
				'rewrite'       => array(
					'slug'       => 'packages',
					'with_front' => false,
				),
				'menu_icon'     => 'dashicons-airplane',
				'menu_position' => 5,
				'supports'      => array( 'title', 'thumbnail', 'excerpt', 'page-attributes' ),
				'show_in_rest'  => true,
			)
		);

		// Traveller reviews: the title is the traveller's name, the featured image their photo.
		register_post_type(
			'testimonial',
			array(
				'labels'        => array(
					'name'                  => __( 'آراء المسافرين', 'gofly' ),
					'singular_name'         => __( 'رأي', 'gofly' ),
					'add_new'               => __( 'إضافة رأي', 'gofly' ),
					'add_new_item'          => __( 'إضافة رأي جديد', 'gofly' ),
					'edit_item'             => __( 'تعديل الرأي', 'gofly' ),
					'all_items'             => __( 'كل الآراء', 'gofly' ),
					'search_items'          => __( 'بحث في الآراء', 'gofly' ),
					'featured_image'        => __( 'صورة المسافر', 'gofly' ),
					'set_featured_image'    => __( 'تعيين صورة المسافر', 'gofly' ),
					'remove_featured_image' => __( 'إزالة الصورة', 'gofly' ),
				),
				'public'        => false,
				'show_ui'       => true,
				'menu_icon'     => 'dashicons-format-quote',
				'menu_position' => 6,
				'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
			)
		);

		$taxonomy_args = array(
			'hierarchical'       => true,
			'public'             => true,
			'publicly_queryable' => false, // Term links point to the filtered packages archive instead.
			'rewrite'            => false,
			'show_admin_column'  => true,
			'show_in_rest'       => true,
		);

		register_taxonomy(
			'destination',
			'package',
			array_merge(
				$taxonomy_args,
				array(
					'labels' => array(
						'name'          => __( 'الوجهات', 'gofly' ),
						'singular_name' => __( 'وجهة', 'gofly' ),
						'add_new_item'  => __( 'إضافة وجهة', 'gofly' ),
						'edit_item'     => __( 'تعديل الوجهة', 'gofly' ),
					),
				)
			)
		);

		register_taxonomy(
			'package_category',
			'package',
			array_merge(
				$taxonomy_args,
				array(
					'labels' => array(
						'name'          => __( 'فئات الباقات', 'gofly' ),
						'singular_name' => __( 'فئة', 'gofly' ),
						'add_new_item'  => __( 'إضافة فئة', 'gofly' ),
						'edit_item'     => __( 'تعديل الفئة', 'gofly' ),
					),
				)
			)
		);
	}
);

/**
 * Destination / category links open the packages archive pre-filtered,
 * e.g. /packages/?dest=paris.
 */
add_filter(
	'term_link',
	function ( $url, $term, $taxonomy ) {
		$params = array(
			'destination'      => 'dest',
			'package_category' => 'cat',
		);
		if ( isset( $params[ $taxonomy ] ) ) {
			return add_query_arg( $params[ $taxonomy ], $term->slug, get_post_type_archive_link( 'package' ) );
		}
		return $url;
	},
	10,
	3
);

/**
 * The archive filters and sorts client-side, so it loads every package.
 */
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! is_admin() && $query->is_main_query() && $query->is_post_type_archive( 'package' ) ) {
			$query->set( 'posts_per_page', -1 );
			$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		}
	}
);

add_filter( 'enter_title_here', fn( $text, $post ) => 'testimonial' === $post->post_type ? __( 'اسم المسافر', 'gofly' ) : $text, 10, 2 );

/** Testimonials list: show the quote and rating next to the name. */
add_filter(
	'manage_testimonial_posts_columns',
	fn( $columns ) => array_slice( $columns, 0, 2 ) + array(
		'quote'  => __( 'الرأي', 'gofly' ),
		'rating' => __( 'التقييم', 'gofly' ),
	) + $columns
);
add_action(
	'manage_testimonial_posts_custom_column',
	function ( $column, $post_id ) {
		if ( 'quote' === $column ) {
			echo esc_html( wp_trim_words( (string) gofly_field( 'quote', $post_id ), 14 ) );
		} elseif ( 'rating' === $column ) {
			echo esc_html( str_repeat( '★', (int) ( gofly_field( 'rating', $post_id ) ?: 5 ) ) );
		}
	},
	10,
	2
);

/**
 * One-time move of reviews typed into the old home-page list
 * (before testimonials were a post type) into testimonial posts.
 */
add_action(
	'admin_init',
	function () {
		if ( get_option( 'gofly_testimonials_migrated' ) ) {
			return;
		}
		update_option( 'gofly_testimonials_migrated', 1 );
		$home  = (int) get_option( 'page_on_front' );
		$count = $home ? (int) get_post_meta( $home, 'testi_items', true ) : 0;
		for ( $i = 0; $i < $count; $i++ ) {
			$row = fn( $key ) => get_post_meta( $home, "testi_items_{$i}_{$key}", true );
			if ( ! $row( 'quote' ) ) {
				continue;
			}
			$id = wp_insert_post(
				wp_slash(
					array(
						'post_type'   => 'testimonial',
						'post_status' => 'publish',
						'post_title'  => $row( 'name' ),
						'menu_order'  => $i,
					)
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, 'quote', $row( 'quote' ) );
				update_post_meta( $id, '_quote', 'field_gofly_testimonial_quote' );
				update_post_meta( $id, 'trip', $row( 'trip' ) );
				update_post_meta( $id, '_trip', 'field_gofly_testimonial_trip' );
				update_post_meta( $id, 'rating', $row( 'rating' ) );
				update_post_meta( $id, '_rating', 'field_gofly_testimonial_rating' );
				if ( $row( 'avatar' ) ) {
					set_post_thumbnail( $id, (int) $row( 'avatar' ) );
				}
			}
		}
	}
);

/** Flush permalinks once after the theme is activated. */
add_action( 'after_switch_theme', fn() => flush_rewrite_rules( false ) );
