<?php
/**
 * Go Fly theme bootstrap.
 *
 * @package gofly
 */

defined( 'ABSPATH' ) || exit;

define( 'GOFLY_VERSION', '1.0.0' );
define( 'GOFLY_DIR', get_template_directory() );
define( 'GOFLY_URI', get_template_directory_uri() );

require GOFLY_DIR . '/inc/helpers.php';
require GOFLY_DIR . '/inc/setup.php';
require GOFLY_DIR . '/inc/enqueue.php';
require GOFLY_DIR . '/inc/post-types.php';
require GOFLY_DIR . '/inc/acf.php';
require GOFLY_DIR . '/inc/class-gofly-nav-walker.php';
require GOFLY_DIR . '/inc/template-tags.php';
