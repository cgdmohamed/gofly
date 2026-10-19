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

/** Flush permalinks once after the theme is activated. */
add_action( 'after_switch_theme', fn() => flush_rewrite_rules( false ) );
