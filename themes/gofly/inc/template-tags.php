<?php
/**
 * Template tags shared by several templates.
 *
 * @package gofly
 */

defined( 'ABSPATH' ) || exit;

/**
 * Normalised data for a package card or page.
 *
 * @param int|null $post_id Package ID.
 * @return array
 */
function gofly_package( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$price   = (float) gofly_field( 'price', $post_id, 0 );
	$old     = (float) gofly_field( 'old_price', $post_id, 0 );
	$dests   = get_the_terms( $post_id, 'destination' );
	$cats    = get_the_terms( $post_id, 'package_category' );

	return array(
		'days'        => (int) gofly_field( 'duration_days', $post_id, 0 ),
		'nights'      => (int) gofly_field( 'duration_nights', $post_id, 0 ),
		'price'       => $price,
		'old_price'   => $old > $price ? $old : 0,
		'discount'    => ( $old > $price && $price > 0 ) ? (int) round( ( 1 - $price / $old ) * 100 ) : 0,
		'rating'      => gofly_field( 'rating', $post_id, '' ),
		'destination' => $dests && ! is_wp_error( $dests ) ? $dests[0]->name : '',
		'dest_slugs'  => $dests && ! is_wp_error( $dests ) ? wp_list_pluck( $dests, 'slug' ) : array(),
		'cat_slugs'   => $cats && ! is_wp_error( $cats ) ? wp_list_pluck( $cats, 'slug' ) : array(),
	);
}

/**
 * "5 أيام" / "5 أيام / 4 ليالٍ".
 *
 * @param int  $days        Days.
 * @param int  $nights      Nights.
 * @param bool $with_nights Include nights.
 * @return string
 */
function gofly_duration( $days, $nights = 0, $with_nights = false ) {
	if ( ! $days ) {
		return '';
	}
	/* translators: %d: number of days */
	$label = sprintf( _n( '%d يوم', '%d أيام', $days, 'gofly' ), $days );
	if ( $with_nights && $nights ) {
		/* translators: %d: number of nights */
		$label .= ' / ' . sprintf( _n( '%d ليلة', '%d ليالٍ', $nights, 'gofly' ), $nights );
	}
	return $label;
}

/**
 * Social links from the options page.
 *
 * @param string $class Anchor class.
 */
function gofly_social_links( $class = 'social__link' ) {
	foreach ( (array) gofly_option( 'social', array() ) as $row ) {
		if ( empty( $row['url'] ) ) {
			continue;
		}
		printf(
			'<a class="%s" href="%s" target="_blank" rel="noopener" aria-label="%s">%s</a>',
			esc_attr( $class ),
			esc_url( $row['url'] ),
			esc_attr( $row['network'] ),
			gofly_brand_icon( $row['network'] ) // phpcs:ignore WordPress.Security.EscapeOutput
		);
	}
}

/**
 * Breadcrumb trail for the page hero.
 *
 * @return string
 */
function gofly_breadcrumb() {
	$items = array( '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'الرئيسية', 'gofly' ) . '</a>' );

	if ( is_singular( 'package' ) ) {
		$items[] = '<a href="' . esc_url( get_post_type_archive_link( 'package' ) ) . '">' . esc_html__( 'الباقات', 'gofly' ) . '</a>';
		$pkg     = gofly_package();
		if ( $pkg['destination'] ) {
			$items[] = esc_html( $pkg['destination'] );
		}
	} elseif ( is_singular( 'post' ) ) {
		$blog = get_option( 'page_for_posts' );
		if ( $blog ) {
			$items[] = '<a href="' . esc_url( get_permalink( $blog ) ) . '">' . esc_html( get_the_title( $blog ) ) . '</a>';
		}
		$cat = get_the_category();
		if ( $cat ) {
			$items[] = esc_html( $cat[0]->name );
		}
	} elseif ( is_post_type_archive( 'package' ) ) {
		$items[] = esc_html( gofly_option( 'packages_title', __( 'باقات الرحلات', 'gofly' ) ) );
	} elseif ( is_home() ) {
		$items[] = esc_html( get_the_title( get_option( 'page_for_posts' ) ) );
	} elseif ( is_archive() ) {
		$items[] = esc_html( wp_strip_all_tags( get_the_archive_title() ) );
	} elseif ( is_search() ) {
		$items[] = esc_html__( 'نتائج البحث', 'gofly' );
	} elseif ( is_singular() ) {
		$items[] = esc_html( get_the_title() );
	}

	return '<nav class="breadcrumb" aria-label="' . esc_attr__( 'مسار التنقل', 'gofly' ) . '">' . implode( ' / ', $items ) . '</nav>';
}

/**
 * Numbered pagination for the main query (or a custom total).
 *
 * @param int $total Total pages; defaults to the main query.
 */
function gofly_pagination( $total = 0 ) {
	global $wp_query;
	$total = $total ? $total : (int) $wp_query->max_num_pages;
	if ( $total < 2 ) {
		return;
	}
	$links = paginate_links(
		array(
			'total'     => $total,
			'current'   => max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) ),
			'type'      => 'array',
			'mid_size'  => 1,
			'prev_text' => gofly_icon( 'chevron_right' ) . '<span class="screen-reader-text">' . esc_html__( 'الصفحة السابقة', 'gofly' ) . '</span>',
			'next_text' => gofly_icon( 'chevron_left' ) . '<span class="screen-reader-text">' . esc_html__( 'الصفحة التالية', 'gofly' ) . '</span>',
		)
	);
	echo '<nav class="pagination" aria-label="' . esc_attr__( 'التنقل بين الصفحات', 'gofly' ) . '">' . implode( '', $links ) . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Subtitle shown in the page hero for the current view.
 *
 * @return string
 */
function gofly_page_subtitle() {
	if ( is_post_type_archive( 'package' ) ) {
		return gofly_option( 'packages_subtitle' );
	}
	if ( is_home() ) {
		return gofly_field( 'page_subtitle', get_option( 'page_for_posts' ) );
	}
	if ( is_category() || is_tag() ) {
		return wp_strip_all_tags( term_description() );
	}
	return is_singular() ? gofly_field( 'page_subtitle', get_the_ID() ) : '';
}

/**
 * Heading for a section on the package page (icon tile + title + note).
 *
 * @param string $id    Anchor id (used by the sticky section bar).
 * @param string $icon  Material Symbols name.
 * @param string $title Heading text.
 * @param string $note  Optional line under the heading.
 */
function gofly_package_heading( $id, $icon, $title, $note = '' ) {
	printf(
		'<h2 class="pkg-heading" id="%s"><span class="icon-tile">%s</span>%s</h2>%s',
		esc_attr( $id ),
		gofly_icon( $icon ), // phpcs:ignore
		esc_html( $title ),
		$note ? '<p class="pkg-note">' . esc_html( $note ) . '</p>' : ''
	);
}
