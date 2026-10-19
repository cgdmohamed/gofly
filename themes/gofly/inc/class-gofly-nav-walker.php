<?php
/**
 * Primary menu walker: top-level links with dropdown panels whose items carry
 * an icon and a subtitle (ACF fields on the menu item).
 *
 * @package gofly
 */

defined( 'ABSPATH' ) || exit;

class Gofly_Nav_Walker extends Walker_Nav_Menu {

	/** CSS classes that mean "this item is the current page or its parent". */
	const CURRENT = array( 'current-menu-item', 'current-menu-parent', 'current-menu-ancestor', 'current_page_parent' );

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<div class="nav__dd"><ul class="nav__panel">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul></div>';
	}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item       = $data_object;
		$is_current = (bool) array_intersect( self::CURRENT, (array) $item->classes );
		$has_dd     = $this->has_children && 1 !== (int) $args->depth;
		$atts       = sprintf(
			' href="%s"%s%s',
			esc_url( $item->url ),
			$item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '',
			in_array( 'current-menu-item', (array) $item->classes, true ) ? ' aria-current="page"' : ''
		);

		if ( 0 === $depth ) {
			$output .= sprintf(
				'<li class="nav__item%s%s"><a class="nav__link"%s>%s%s</a>',
				$has_dd ? ' has-dd' : '',
				$is_current ? ' is-current' : '',
				$atts,
				esc_html( $item->title ),
				$has_dd ? gofly_icon( 'expand_more', 'nav__chev' ) : ''
			);
			return;
		}

		$icon     = gofly_field( 'menu_icon', $item, 'location_on' );
		$subtitle = gofly_field( 'menu_subtitle', $item, $item->description );
		$output  .= sprintf(
			'<li><a class="nav__dd-link"%s><span class="nav__dd-icon">%s</span><span class="nav__dd-text"><b>%s</b>%s</span></a>',
			$atts,
			gofly_icon( $icon ),
			esc_html( $item->title ),
			$subtitle ? '<small>' . esc_html( $subtitle ) . '</small>' : ''
		);
	}
}
