<?php
/**
 * Two-way mapping between a builder program and the theme's `package` CPT
 * (fields from themes/gofly/acf-json/group_gofly_package.json).
 *
 * Program → package never sends customer data, flights or per-person
 * breakdowns: only what belongs on the public package page.
 *
 * @package gofly-pb
 */

defined( 'ABSPATH' ) || exit;

class GFPB_Package_Sync {

	/** Cover defaults of the builder (CV0). */
	const COVER = array(
		'en'       => 'وجهات أحلامك',
		't1'       => 'نريد أن نكون جزءًا من',
		't2'       => 'مغامرتك القادمة التي لا تنسى',
		'badge'    => 'لنسافر معاً',
		'nights'   => '',
		'start'    => '',
		'end'      => '',
		'sub'      => '',
		'persons'  => '2 بالغين',
		'showno'   => false,
		'showcard' => false,
		'showbdg'  => true,
		'showsvc'  => true,
		'svc'      => "🧑‍✈️|سائق خاص\n🚗|سيارة خاصة\n✈️|تذاكر طيران\n📶|شرائح اتصال وانترنت\n🍽️|فطور يومي\n🏨|حجوزات فنادق",
	);

	/** Package fields that can be pushed, with their labels. */
	public static function fields() {
		return array(
			'title'           => __( 'اسم الباقة', 'gofly-pb' ),
			'subtitle'        => __( 'السطر التعريفي', 'gofly-pb' ),
			'thumbnail'       => __( 'الصورة البارزة', 'gofly-pb' ),
			'duration_days'   => __( 'عدد الأيام', 'gofly-pb' ),
			'duration_nights' => __( 'عدد الليالي', 'gofly-pb' ),
			'price'           => __( 'السعر للفرد', 'gofly-pb' ),
			'old_price'       => __( 'السعر قبل الخصم', 'gofly-pb' ),
			'includes'        => __( 'يشمل البرنامج', 'gofly-pb' ),
			'excludes'        => __( 'لا يشمل', 'gofly-pb' ),
			'itinerary'       => __( 'برنامج الأيام', 'gofly-pb' ),
			'hotels'          => __( 'الفنادق', 'gofly-pb' ),
			'policies'        => __( 'الملاحظات والسياسات', 'gofly-pb' ),
		);
	}

	/* ------------------------------------------------------------------ */

	private static function read( $post_id, $name ) {
		return function_exists( 'get_field' ) ? get_field( $name, $post_id ) : get_post_meta( $post_id, $name, true );
	}

	private static function write( $post_id, $name, $value ) {
		return function_exists( 'update_field' ) ? update_field( $name, $value, $post_id ) : update_post_meta( $post_id, $name, $value );
	}

	/** Visible lines of a textarea (the builder's V()). */
	private static function visible( $obj, $key ) {
		$off = (array) ( $obj[ 'off_' . $key ] ?? array() );
		return array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', (string) ( $obj[ $key ] ?? '' ) ) ), fn( $l ) => '' !== $l && ! in_array( $l, $off, true ) ) );
	}

	/** Parse "3,200" / "٣٢٠٠" into a number. */
	private static function number( $value ) {
		$value = strtr( (string) $value, array_combine( array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' ), range( 0, 9 ) ) );
		return (float) preg_replace( '/[^\d.]/', '', $value );
	}

	private static function shown( $items ) {
		return array_values( array_filter( (array) $items, fn( $x ) => empty( $x['hide'] ) ) );
	}

	/* ------------------------------------------------------------------ */

	/**
	 * Values a program would write to a package.
	 *
	 * @param array $doc Builder document.
	 * @return array field => value
	 */
	public static function from_program( $doc ) {
		$cover = (array) ( $doc['cover'] ?? array() );
		$secs  = self::shown( $doc['secs'] ?? array() );
		$out   = array(
			'title'     => trim( (string) ( $cover['title'] ?? '' ) ),
			'subtitle'  => (string) ( $cover['en'] ?? '' ),
			'thumbnail' => ( ! empty( $cover['img'] ) && 0 !== strpos( $cover['img'], 'tpl:' ) ) ? attachment_url_to_postid( $cover['img'] ) : 0,
			'includes'  => '',
			'excludes'  => '',
			'itinerary' => array(),
			'hotels'    => array(),
			'policies'  => array(),
		);

		foreach ( $secs as $s ) {
			switch ( $s['t'] ?? '' ) {
				case 'list':
					$key = ( $s['mark'] ?? '' ) === '✕' ? 'excludes' : 'includes';
					if ( '' === $out[ $key ] ) {
						$out[ $key ] = implode( "\n", self::visible( $s, 'body' ) );
					}
					break;
				case 'days':
					foreach ( self::shown( $s['items'] ?? array() ) as $day ) {
						$lines = self::visible( $day, 'body' );
						// A closing "مبيت …" line is the website's "overnight" field.
						$night = $lines && 0 === mb_strpos( end( $lines ), 'مبيت' ) ? array_pop( $lines ) : '';
						$out['itinerary'][] = array(
							'title'     => trim( ( $day['kind'] ?? '' ) . ( ! empty( $day['title'] ) ? ' — ' . $day['title'] : '' ) ),
							'text'      => implode( "\n", $lines ),
							'overnight' => $night,
						);
					}
					break;
				case 'text':
					$out['policies'][] = array(
						'title' => (string) ( $s['title'] ?? '' ),
						'text'  => implode( "\n", self::visible( $s, 'body' ) ),
					);
					break;
				case 'price':
					$amount = self::number( $s['pa'] ?? '' );
					if ( $amount ) {
						$out['price'] = $amount;
					}
					if ( ! empty( $s['old'] ) ) {
						$out['old_price'] = self::number( $s['old'] );
					}
					break;
			}
		}

		// Hotels: every tier, including tiers hidden in this quote — the website
		// lists all options while a quote shows only the one offered.
		foreach ( (array) ( $doc['secs'] ?? array() ) as $s ) {
			if ( 'hotels' !== ( $s['t'] ?? '' ) ) {
				continue;
			}
			foreach ( self::shown( $s['items'] ?? array() ) as $hotel ) {
				$out['hotels'][] = array(
					'tier'  => (string) ( $s['title'] ?? '' ),
					'stars' => (string) max( 3, min( 5, (int) ( $hotel['stars'] ?? 4 ) ) ),
					'name'  => (string) ( $hotel['name'] ?? '' ),
					'note'  => (string) ( $hotel['city'] ?? '' ),
				);
			}
		}

		$days = count( $out['itinerary'] );
		if ( $days ) {
			$out['duration_days'] = $days;
		}
		$nights = (int) ( $cover['nights'] ?? 0 );
		if ( ! $nights && ! empty( $cover['start'] ) && ! empty( $cover['end'] ) ) {
			$nights = (int) round( ( strtotime( $cover['end'] ) - strtotime( $cover['start'] ) ) / DAY_IN_SECONDS );
		}
		if ( ! $nights && $days > 1 ) {
			$nights = $days - 1;
		}
		if ( $nights > 0 ) {
			$out['duration_nights'] = $nights;
		}
		return $out;
	}

	/** Current values of a package in the same shape as from_program(). */
	public static function current( $package_id ) {
		$out = array();
		foreach ( array_keys( self::fields() ) as $field ) {
			if ( 'title' === $field ) {
				$out[ $field ] = get_the_title( $package_id );
			} elseif ( 'thumbnail' === $field ) {
				$out[ $field ] = (int) get_post_thumbnail_id( $package_id );
			} else {
				$value = self::read( $package_id, $field );
				$out[ $field ] = is_array( $value ) ? array_map( fn( $row ) => array_intersect_key( (array) $row, array_flip( array( 'title', 'text', 'overnight', 'tier', 'stars', 'name', 'note' ) ) ), $value ) : $value;
			}
		}
		return $out;
	}

	/** Short human summary of a field value for the diff modal. */
	private static function summary( $field, $value ) {
		if ( 'thumbnail' === $field ) {
			return $value ? (string) wp_get_attachment_image_url( $value, 'thumbnail' ) : '';
		}
		if ( is_array( $value ) ) {
			$names = array_map( fn( $row ) => $row['name'] ?? $row['title'] ?? '', $value );
			/* translators: 1: count, 2: names */
			return $value ? sprintf( __( '%1$d عناصر: %2$s', 'gofly-pb' ), count( $value ), implode( '، ', array_filter( $names ) ) ) : '';
		}
		if ( in_array( $field, array( 'price', 'old_price' ), true ) ) {
			return $value ? number_format( (float) $value ) : '';
		}
		return (string) $value;
	}

	/** Repeater rows as plain strings, so "4" and 4 compare equal. */
	private static function flat( $value ) {
		$rows = array_map(
			function ( $row ) {
				$row = array_map( fn( $v ) => trim( (string) $v ), (array) $row );
				ksort( $row );
				return $row;
			},
			array_values( (array) $value )
		);
		return wp_json_encode( $rows, JSON_UNESCAPED_UNICODE );
	}

	private static function same( $a, $b ) {
		if ( is_array( $a ) || is_array( $b ) ) {
			return self::flat( $a ) === self::flat( $b );
		}
		return trim( (string) $a ) === trim( (string) $b ) || ( is_numeric( $a ) && is_numeric( $b ) && (float) $a === (float) $b );
	}

	/**
	 * Field-by-field comparison for the "update the website package?" prompt.
	 *
	 * @param array $doc        Builder document.
	 * @param int   $package_id Package (0 = a new package would be created).
	 * @return array[] field, label, current, next, changed
	 */
	public static function diff( $doc, $package_id ) {
		$next    = self::from_program( $doc );
		$current = $package_id ? self::current( $package_id ) : array();
		$rows    = array();
		foreach ( self::fields() as $field => $label ) {
			if ( ! array_key_exists( $field, $next ) || ( 'thumbnail' === $field && ! $next[ $field ] ) ) {
				continue;
			}
			$cur    = $current[ $field ] ?? '';
			$rows[] = array(
				'field'   => $field,
				'label'   => $label,
				'current' => self::summary( $field, $cur ),
				'next'    => self::summary( $field, $next[ $field ] ),
				'changed' => ! self::same( $cur, $next[ $field ] ),
			);
		}
		return $rows;
	}

	/**
	 * Write selected fields to a package (creating a draft when $package_id is 0).
	 *
	 * @param array    $doc        Builder document.
	 * @param int      $package_id Package ID or 0.
	 * @param string[] $fields     Fields to write.
	 * @return int|WP_Error Package ID.
	 */
	public static function push( $doc, $package_id, $fields ) {
		$next   = self::from_program( $doc );
		$fields = array_intersect( (array) $fields, array_keys( $next ) );
		if ( ! $package_id ) {
			$package_id = wp_insert_post(
				array(
					'post_type'   => 'package',
					'post_status' => 'draft',
					'post_title'  => $next['title'] ? $next['title'] : __( 'باقة جديدة', 'gofly-pb' ),
				),
				true
			);
			if ( is_wp_error( $package_id ) ) {
				return $package_id;
			}
		} elseif ( 'package' !== get_post_type( $package_id ) ) {
			return new WP_Error( 'gfpb_package', __( 'الباقة غير موجودة.', 'gofly-pb' ), array( 'status' => 404 ) );
		}

		foreach ( $fields as $field ) {
			$value = $next[ $field ];
			if ( 'title' === $field ) {
				wp_update_post(
					array(
						'ID'         => $package_id,
						'post_title' => $value,
					)
				);
			} elseif ( 'thumbnail' === $field ) {
				set_post_thumbnail( $package_id, $value );
			} else {
				self::write( $package_id, $field, $value );
			}
		}
		return $package_id;
	}

	/**
	 * Build a program document pre-filled from a website package.
	 *
	 * @param int $package_id Package ID.
	 * @return array Builder document.
	 */
	public static function to_program( $package_id ) {
		$post    = get_post( $package_id );
		$gallery = array_values( array_filter( array_map( fn( $img ) => is_array( $img ) ? ( $img['url'] ?? '' ) : wp_get_attachment_url( (int) $img ), (array) self::read( $package_id, 'gallery' ) ) ) );
		$cover   = get_the_post_thumbnail_url( $package_id, 'large' );
		$price   = (float) self::read( $package_id, 'price' );
		$old     = (float) self::read( $package_id, 'old_price' );

		$days = array();
		foreach ( (array) self::read( $package_id, 'itinerary' ) as $i => $day ) {
			$days[] = array(
				'kind'  => (string) ( $day['title'] ?? '' ),
				'title' => '',
				'body'  => trim( ( $day['text'] ?? '' ) . ( ! empty( $day['overnight'] ) ? "\n" . $day['overnight'] : '' ) ),
				'img'   => $gallery[ $i + 1 ] ?? '',
			);
		}

		$secs = array(
			array( 't' => 'list', 'title' => 'البرنامج يشمل', 'mark' => '✓', 'body' => (string) self::read( $package_id, 'includes' ) ),
			array( 't' => 'list', 'title' => 'البرنامج لا يشمل', 'mark' => '✕', 'body' => (string) self::read( $package_id, 'excludes' ) ),
		);
		if ( $days ) {
			$secs[] = array( 't' => 'days', 'title' => 'تفاصيل البرنامج', 'items' => $days );
		}

		$tiers = array();
		foreach ( (array) self::read( $package_id, 'hotels' ) as $hotel ) {
			$tiers[ $hotel['tier'] ?? 'الفنادق' ][] = array(
				'name'  => (string) ( $hotel['name'] ?? '' ),
				'city'  => (string) ( $hotel['note'] ?? '' ),
				'stars' => (string) ( $hotel['stars'] ?? '4' ),
				'body'  => '',
				'img'   => '',
			);
		}
		$first = true;
		foreach ( $tiers as $tier => $items ) {
			$secs[] = array( 't' => 'hotels', 'title' => $tier, 'hide' => ! $first, 'items' => $items );
			$first  = false;
		}

		$secs[] = array( 't' => 'flights', 'title' => 'رحلات الطيران', 'adults' => '2', 'kids' => '', 'showp' => false, 'price' => '', 'cur' => 'ريال سعودي', 'items' => array( array( 'title' => 'الذهاب' ), array( 'title' => 'العودة' ) ) );
		$secs[] = array( 't' => 'price', 'title' => 'السعر', 'cur' => 'ريال سعودي', 'na' => '2', 'pa' => $price ? (string) $price : '', 'old' => $old > $price ? number_format( $old ) : '', 'showbank' => true );

		foreach ( (array) self::read( $package_id, 'policies' ) as $policy ) {
			$secs[] = array( 't' => 'text', 'title' => (string) ( $policy['title'] ?? '' ), 'body' => (string) ( $policy['text'] ?? '' ) );
		}

		return array(
			'cover' => array_merge(
				self::COVER,
				array(
					'title'  => $post ? $post->post_title : '',
					'en'     => (string) self::read( $package_id, 'subtitle' ) ? (string) self::read( $package_id, 'subtitle' ) : self::COVER['en'],
					'nights' => (string) self::read( $package_id, 'duration_nights' ),
					'img'    => $cover ? $cover : ( $gallery[0] ?? '' ),
				)
			),
			'secs'  => $secs,
			'meta'  => array( 'code' => '', 'cname' => '', 'cphone' => '', 'exec' => '', 'sales' => '' ),
			'tpl'   => 'wave',
		);
	}
}
