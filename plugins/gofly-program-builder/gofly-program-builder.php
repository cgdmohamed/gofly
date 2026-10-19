<?php
/**
 * Plugin Name: Go Fly Program Builder
 * Description: أداة بناء برامج الرحلات بصيغة PDF داخل لوحة التحكم — مكتبة برامج مشتركة، قوالب تصميم ومحتوى، مزامنة مع باقات الموقع، وصلاحيات وسجل نشاط.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: Go Fly Travel & Tourism
 * Text Domain: gofly-pb
 * License: GPL-2.0-or-later
 *
 * @package gofly-pb
 */

defined( 'ABSPATH' ) || exit;

define( 'GFPB_VERSION', '1.0.0' );
define( 'GFPB_FILE', __FILE__ );
define( 'GFPB_DIR', plugin_dir_path( __FILE__ ) );
define( 'GFPB_URL', plugin_dir_url( __FILE__ ) );

require GFPB_DIR . 'includes/class-capabilities.php';
require GFPB_DIR . 'includes/class-cpt.php';
require GFPB_DIR . 'includes/class-programs.php';
require GFPB_DIR . 'includes/class-activity-log.php';
require GFPB_DIR . 'includes/class-settings.php';
require GFPB_DIR . 'includes/class-package-sync.php';
require GFPB_DIR . 'includes/class-rest.php';
require GFPB_DIR . 'includes/class-admin.php';

register_activation_hook( __FILE__, array( 'GFPB_Installer', 'activate' ) );

add_action( 'init', array( 'GFPB_CPT', 'register' ) );
add_action( 'rest_api_init', array( 'GFPB_REST', 'register' ) );
add_action( 'plugins_loaded', array( 'GFPB_Admin', 'init' ) );

/**
 * Activation: capabilities, log table, preset content templates.
 */
class GFPB_Installer {

	public static function activate() {
		GFPB_CPT::register();
		GFPB_Capabilities::add();
		GFPB_Activity_Log::install();
		self::seed_templates();
	}

	/** Import the bundled Russia presets once. */
	public static function seed_templates() {
		if ( get_option( 'gfpb_seeded' ) ) {
			return;
		}
		$json = json_decode( (string) file_get_contents( GFPB_DIR . 'assets/presets/russia.json' ), true );
		foreach ( (array) ( $json['templates'] ?? array() ) as $tpl ) {
			GFPB_Programs::save_template( 0, $tpl['title'], $tpl['data'], $tpl['code'] );
		}
		update_option( 'gfpb_seeded', 1 );
	}
}
