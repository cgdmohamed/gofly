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
