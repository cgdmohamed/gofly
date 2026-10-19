<?php
/**
 * ACF Pro integration: local JSON, options page and the Fluent Forms picker.
 *
 * Field groups live in /acf-json so they are versioned with the theme and
 * load without a database sync. Edit them in ACF → Field Groups as usual;
 * ACF writes changes back to the same folder.
 *
 * @package gofly
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'acf/settings/save_json', fn() => GOFLY_DIR . '/acf-json' );
add_filter(
	'acf/settings/load_json',
	function ( $paths ) {
		$paths[] = GOFLY_DIR . '/acf-json';
		return $paths;
	}
);

add_action(
	'acf/init',
	function () {
		if ( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page(
				array(
					'page_title' => __( 'إعدادات الموقع', 'gofly' ),
					'menu_title' => __( 'إعدادات الموقع', 'gofly' ),
					'menu_slug'  => 'gofly-settings',
					'icon_url'   => 'dashicons-admin-site-alt3',
					'position'   => 59,
					'redirect'   => false,
				)
			);
		}
	}
);

/**
 * Fill the form pickers with the site's Fluent Forms, so editors choose a
 * form by name instead of pasting shortcodes.
 *
 * @param array $field ACF field.
 * @return array
 */
function gofly_fluent_form_choices( $field ) {
	global $wpdb;
	$field['choices'] = array();
	$table            = $wpdb->prefix . 'fluentform_forms';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		$forms = $wpdb->get_results( "SELECT id, title FROM {$table} ORDER BY id DESC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $forms as $form ) {
			$field['choices'][ $form->id ] = $form->title;
		}
	}
	return $field;
}
add_filter( 'acf/load_field/name=form_id', 'gofly_fluent_form_choices' );
add_filter( 'acf/load_field/name=quote_form_id', 'gofly_fluent_form_choices' );

/**
 * Render a Fluent Form by ID.
 *
 * @param int|string $form_id Fluent Forms form ID.
 * @return string
 */
function gofly_form( $form_id ) {
	if ( ! $form_id ) {
		return current_user_can( 'edit_pages' )
			? '<p class="notice-inline">' . esc_html__( 'اختر نموذج Fluent Forms من إعدادات الصفحة لعرضه هنا.', 'gofly' ) . '</p>'
			: '';
	}
	return do_shortcode( '[fluentform id="' . absint( $form_id ) . '"]' );
}

/** Warn admins when ACF Pro is missing. */
add_action(
	'admin_notices',
	function () {
		if ( ! class_exists( 'ACF' ) && current_user_can( 'activate_plugins' ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'قالب جو فلاي يحتاج إضافة Advanced Custom Fields Pro لتحرير المحتوى.', 'gofly' ) . '</p></div>';
		}
	}
);
