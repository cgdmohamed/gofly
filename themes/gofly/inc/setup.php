<?php
/**
 * Theme supports, menus, image sizes and content filters.
 *
 * @package gofly
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'gofly', GOFLY_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/editor.css' );

		register_nav_menus(
			array(
				'primary'      => __( 'القائمة الرئيسية', 'gofly' ),
				'footer_1'     => __( 'الفوتر — العمود الأول', 'gofly' ),
				'footer_2'     => __( 'الفوتر — العمود الثاني', 'gofly' ),
				'footer_3'     => __( 'الفوتر — العمود الثالث', 'gofly' ),
				'footer_legal' => __( 'الفوتر — الروابط القانونية', 'gofly' ),
			)
		);

		add_image_size( 'gofly-card', 800, 520, true );
		add_image_size( 'gofly-tall', 700, 900, true );
		add_image_size( 'gofly-square', 500, 500, true );
		add_image_size( 'gofly-hero', 1400, 800, true );
	}
);

$GLOBALS['content_width'] = 780;

/** Shorter excerpts suit the card layouts. */
add_filter( 'excerpt_length', fn() => 22 );
add_filter( 'excerpt_more', fn() => '…' );

/** Mark the header layout: white header (Site Settings), or transparent over the front-page hero. */
add_filter(
	'body_class',
	function ( $classes ) {
		if ( 'light' === gofly_option( 'header_style' ) ) {
			$classes[] = 'has-light-header';
		} elseif ( is_front_page() ) {
			$classes[] = 'has-overlay-header';
		}
		return $classes;
	}
);

/**
 * Give every <h2> in post content an id so the table of contents can link to it.
 */
add_filter(
	'the_content',
	function ( $content ) {
		if ( ! is_singular( 'post' ) || ! in_the_loop() ) {
			return $content;
		}
		$i = 0;
		return preg_replace_callback(
			'/<h2(?![^>]*\sid=)([^>]*)>/i',
			function ( $m ) use ( &$i ) {
				$i++;
				return '<h2 id="section-' . $i . '"' . $m[1] . '>';
			},
			$content
		);
	},
	20
);

/**
 * Headings of the current post content for the table of contents.
 *
 * @return array[] Each item: id, title.
 */
function gofly_post_toc() {
	$content = apply_filters( 'the_content', get_the_content() );
	preg_match_all( '/<h2[^>]*\sid="([^"]+)"[^>]*>(.*?)<\/h2>/is', $content, $m, PREG_SET_ORDER );
	return array_map(
		fn( $h ) => array(
			'id'    => $h[1],
			'title' => wp_strip_all_tags( $h[2] ),
		),
		$m
	);
}

/** Cards per blog page (Site Settings → المدونة); page one also shows the featured post above them. */
function gofly_blog_per_page() {
	return max( 3, (int) gofly_option( 'blog_per_page', 12 ) );
}

add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() ) {
			return;
		}
		$per   = gofly_blog_per_page();
		$paged = max( 1, (int) $query->get( 'paged' ) );
		$query->set( 'posts_per_page', 1 === $paged ? $per + 1 : $per );
		if ( $paged > 1 ) {
			$query->set( 'offset', $per + 1 + ( $paged - 2 ) * $per );
		}
	}
);

/** Total blog pages, accounting for the extra featured post on page one. */
function gofly_blog_pages() {
	global $wp_query;
	return 1 + (int) max( 0, ceil( ( (int) $wp_query->found_posts - gofly_blog_per_page() - 1 ) / gofly_blog_per_page() ) );
}

/**
 * The blog design needs a Posts page. If none is set but a page with the
 * slug "blog" exists, use it (once).
 */
add_action(
	'admin_init',
	function () {
		if ( get_option( 'page_for_posts' ) || 'page' !== get_option( 'show_on_front' ) || get_option( 'gofly_blog_page_set' ) ) {
			return;
		}
		$blog = get_page_by_path( 'blog' );
		if ( $blog && 'publish' === $blog->post_status && (int) get_option( 'page_on_front' ) !== $blog->ID ) {
			update_option( 'page_for_posts', $blog->ID );
			update_option( 'gofly_blog_page_set', 1 );
		}
	}
);
