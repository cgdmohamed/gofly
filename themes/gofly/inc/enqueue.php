<?php
/**
 * Styles and scripts.
 *
 * @package gofly
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	function () {
		$ver = GOFLY_VERSION;

		wp_enqueue_style( 'gofly-material-symbols', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0..1,0&display=block', array(), null );
		wp_enqueue_style( 'gofly-fa-core', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/fontawesome.min.css', array(), null );
		wp_enqueue_style( 'gofly-fa-brands', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/brands.min.css', array( 'gofly-fa-core' ), null );
		wp_enqueue_style( 'gofly', GOFLY_URI . '/assets/css/main.css', array(), $ver );

		wp_enqueue_script( 'gofly', GOFLY_URI . '/assets/js/main.js', array(), $ver, array( 'strategy' => 'defer' ) );

		if ( is_post_type_archive( 'package' ) ) {
			wp_enqueue_script( 'gofly-packages', GOFLY_URI . '/assets/js/packages.js', array(), $ver, array( 'strategy' => 'defer' ) );
		}

		if ( is_singular( 'package' ) ) {
			wp_enqueue_style( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11.1.14/swiper-bundle.min.css', array(), null );
			wp_enqueue_script( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11.1.14/swiper-bundle.min.js', array(), null, array( 'strategy' => 'defer' ) );
			wp_enqueue_script( 'gofly-package', GOFLY_URI . '/assets/js/package.js', array( 'swiper' ), $ver, array( 'strategy' => 'defer' ) );
		}

		if ( is_page_template( 'page-templates/booking.php' ) ) {
			wp_enqueue_script( 'gofly-booking', GOFLY_URI . '/assets/js/booking.js', array(), $ver, array( 'strategy' => 'defer' ) );
			wp_localize_script(
				'gofly-booking',
				'goflyBooking',
				array(
					'whatsapp' => preg_replace( '/\D+/', '', (string) gofly_option( 'whatsapp', '966510131525' ) ),
					'brand'    => apply_filters( 'gofly_booking_brand', 'GO FLY' ),
				)
			);
		}
	}
);

/** Warm up the font/CDN connections. */
add_filter(
	'wp_resource_hints',
	function ( $urls, $relation ) {
		if ( 'preconnect' === $relation ) {
			$urls[] = array(
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => 'anonymous',
			);
			$urls[] = 'https://cdnjs.cloudflare.com';
		}
		return $urls;
	},
	10,
	2
);

/** Preload the two Bukra weights used above the fold. */
add_action(
	'wp_head',
	function () {
		foreach ( array( 'regular', 'bold' ) as $weight ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/ttf" crossorigin>' . "\n", esc_url( GOFLY_URI . '/assets/fonts/29ltbukra' . $weight . '.ttf' ) );
		}
	},
	1
);

/** Theme fonts and colours in the block editor. */
add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_enqueue_style( 'gofly-material-symbols', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0..1,0&display=block', array(), null );
	}
);
