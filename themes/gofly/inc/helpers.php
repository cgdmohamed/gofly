<?php
/**
 * Small, dependency-safe helpers used across templates.
 *
 * @package gofly
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read an ACF field without fataling when ACF is inactive.
 *
 * @param string     $name    Field name.
 * @param int|string $post_id Post ID, 'option', or term (e.g. 'term_12').
 * @param mixed      $default Returned when the field is empty.
 * @return mixed
 */
function gofly_field( $name, $post_id = false, $default = '' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$value = get_field( $name, $post_id );
	return ( null === $value || '' === $value || false === $value || array() === $value ) ? $default : $value;
}

/**
 * Read a global site option (ACF options page).
 *
 * @param string $name    Field name.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function gofly_option( $name, $default = '' ) {
	return gofly_field( $name, 'option', $default );
}

/**
 * Material Symbols Rounded icon.
 *
 * @param string $name  Icon ligature name (fonts.google.com/icons).
 * @param string $class Extra classes, e.g. 'ms--fill'.
 * @return string
 */
function gofly_icon( $name, $class = '' ) {
	if ( ! $name ) {
		return '';
	}
	return sprintf( '<span class="ms %s" aria-hidden="true">%s</span>', esc_attr( $class ), esc_html( $name ) );
}

/**
 * Font Awesome brand icon.
 *
 * @param string $name Brand slug, e.g. 'whatsapp'.
 * @return string
 */
function gofly_brand_icon( $name ) {
	return sprintf( '<i class="fa-brands fa-%s" aria-hidden="true"></i>', esc_attr( $name ) );
}

/**
 * Saudi riyal symbol (inherits the text colour).
 *
 * @return string
 */
function gofly_riyal() {
	return '<span class="riyal" role="img" aria-label="' . esc_attr__( 'ريال سعودي', 'gofly' ) . '"></span>';
}

/**
 * Formatted price with the riyal symbol, Western digits.
 *
 * @param float|int $amount Price.
 * @return string
 */
function gofly_price( $amount ) {
	return esc_html( number_format( (float) $amount ) ) . ' ' . gofly_riyal();
}

/**
 * WhatsApp chat URL for the company number.
 *
 * @param string $text Optional prefilled message.
 * @return string
 */
function gofly_whatsapp_url( $text = '' ) {
	$number = preg_replace( '/\D+/', '', (string) gofly_option( 'whatsapp', '966510131525' ) );
	$url    = 'https://wa.me/' . $number;
	return $text ? add_query_arg( 'text', rawurlencode( $text ), $url ) : $url;
}

/**
 * Render an image from an ACF image value (array or ID).
 *
 * @param array|int $image ACF image.
 * @param string    $size  Image size.
 * @param array     $attr  Extra attributes.
 * @return string
 */
function gofly_image( $image, $size = 'large', $attr = array() ) {
	$id = is_array( $image ) ? ( $image['ID'] ?? 0 ) : (int) $image;
	if ( ! $id ) {
		return '';
	}
	return wp_get_attachment_image( $id, $size, false, $attr );
}

/**
 * Render an ACF link field as an anchor.
 *
 * @param array  $link  ACF link array (url, title, target).
 * @param string $class CSS classes.
 * @param string $icon  Optional Material Symbols icon before the label.
 * @return string
 */
function gofly_link( $link, $class = 'btn btn--gold', $icon = '' ) {
	if ( empty( $link['url'] ) ) {
		return '';
	}
	return sprintf(
		'<a class="%1$s" href="%2$s"%3$s>%4$s%5$s</a>',
		esc_attr( $class ),
		esc_url( $link['url'] ),
		! empty( $link['target'] ) ? ' target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : '',
		gofly_icon( $icon ),
		esc_html( $link['title'] )
	);
}

/**
 * Star rating row: filled stars up to $rating, outlined after.
 *
 * @param int $rating Number of filled stars.
 * @param int $max    Total stars.
 * @return string
 */
function gofly_stars( $rating, $max = 5 ) {
	$rating = (int) round( (float) $rating );
	$out    = '<span class="stars" dir="ltr" role="img" aria-label="' . esc_attr( sprintf( /* translators: %d: stars */ __( '%d نجوم', 'gofly' ), $rating ) ) . '">';
	for ( $i = 1; $i <= $max; $i++ ) {
		$out .= gofly_icon( 'star', $i <= $rating ? 'ms--fill' : '' );
	}
	return $out . '</span>';
}

/**
 * Split a textarea value into trimmed, non-empty lines.
 *
 * @param string $text Raw textarea value.
 * @return string[]
 */
function gofly_lines( $text ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', (string) $text ) ) ) );
}

/**
 * Logo image URL (light logo for dark backgrounds, dark logo for white).
 *
 * @param string $variant 'light' or 'dark'.
 * @return string
 */
function gofly_logo_url( $variant = 'light' ) {
	$logo = gofly_option( 'logo_' . $variant );
	if ( ! empty( $logo['url'] ) ) {
		return $logo['url'];
	}
	return GOFLY_URI . '/assets/img/' . ( 'dark' === $variant ? 'logo_color.png' : 'logo_white.png' );
}

/**
 * Estimated reading time in minutes for a post.
 *
 * @param int|WP_Post|null $post Post.
 * @return int
 */
function gofly_reading_time( $post = null ) {
	$words = count( preg_split( '/\s+/u', wp_strip_all_tags( get_post_field( 'post_content', $post ) ), -1, PREG_SPLIT_NO_EMPTY ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Permalink of the first page using a theme page template.
 *
 * @param string $template Template slug, e.g. 'booking' for page-templates/booking.php.
 * @return string
 */
function gofly_template_url( $template ) {
	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value' => 'page-templates/' . $template . '.php', // phpcs:ignore WordPress.DB.SlowDBQuery
			'number'     => 1,
		)
	);
	return $pages ? get_permalink( $pages[0] ) : '';
}
