<?php
/**
 * Company identity printed on every PDF page (the builder's "CO" object).
 * Defaults are taken from the Go Fly theme's Site Settings when available.
 *
 * @package gofly-pb
 */

defined( 'ABSPATH' ) || exit;

class GFPB_Settings {

	const OPTION = 'gfpb_settings';
	const FONTS  = array( 'Cairo', 'Tajawal', 'Almarai' );

	public static function defaults() {
		$theme = function ( $key, $fallback ) {
			return function_exists( 'gofly_option' ) ? gofly_option( $key, $fallback ) : $fallback;
		};
		return array(
			'ar'    => get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'جو فلاي للسفر والسياحة',
			'cr'    => '1010576472',
			'phone' => '',
			'wa'    => $theme( 'phone_display', '0510131525' ),
			'web'   => wp_parse_url( home_url(), PHP_URL_HOST ),
			'addr'  => $theme( 'address_full', '' ),
			'note'  => 'شريككم الموثوق في كل رحلة',
			'c1'    => '#0a3366',
			'c2'    => '#b8901f',
			'logo'  => '',
			'font'  => 'Cairo',
			'bank'  => '',
		);
	}

	public static function get() {
		return wp_parse_args( (array) get_option( self::OPTION, array() ), self::defaults() );
	}

	public static function update( $input ) {
		$clean = array();
		foreach ( self::defaults() as $key => $default ) {
			$value = $input[ $key ] ?? $default;
			switch ( $key ) {
				case 'c1':
				case 'c2':
					$clean[ $key ] = sanitize_hex_color( $value ) ? sanitize_hex_color( $value ) : $default;
					break;
				case 'logo':
					$clean[ $key ] = esc_url_raw( $value );
					break;
				case 'font':
					$clean[ $key ] = in_array( $value, self::FONTS, true ) ? $value : 'Cairo';
					break;
				case 'bank':
					$clean[ $key ] = sanitize_textarea_field( $value );
					break;
				default:
					$clean[ $key ] = sanitize_text_field( $value );
			}
		}
		update_option( self::OPTION, $clean );
		return $clean;
	}
}
