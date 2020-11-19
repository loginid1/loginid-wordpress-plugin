<?php

/**
 * Admin setup for the plugin
 *
 * @since 0.1.0
 * @function	loginid_dwp_add_menu_links()		Add admin menu pages
 * @function	loginid_dwp_register_settings	Register Settings
 * @function	loginid_dwp_validater_and_sanitizer()	Validate And Sanitize User Input Before Its Saved To Database
 * @function	loginid_dwp_get_settings()		Get settings from database
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Add admin menu pages
 *
 * @since 0.1.0
 * @refer https://developer.wordpress.org/plugins/administration-menus/
 */
function loginid_dwp_add_menu_links()
{
	add_options_page(__('LoginID DirectWeb Plugin', 'loginid-directweb-plugin'), __('LoginID DirectWeb Plugin', 'loginid-directweb-plugin'), 'update_core', 'loginid-directweb-plugin', 'loginid_dwp_admin_interface_render');
}
add_action('admin_menu', 'loginid_dwp_add_menu_links');

/**
 * Register Settings
 *
 * @since 0.1.0
 */
function loginid_dwp_register_settings()
{

	// Register Setting
	register_setting(
		'loginid_dwp_settings_group', 			// Group name
		'loginid_dwp_settings', 					// Setting name = html form <input> name on settings form
		'loginid_dwp_validater_and_sanitizer'	// Input sanitizer
	);

	// Register A New Section
	add_settings_section(
		'loginid_dwp_minimum_settings_section',							// ID
		__('Minimum Settings', 'loginid-directweb-plugin'),		// Title
		'loginid_dwp_minimum_settings_section_callback',					// Callback Function
		'loginid-directweb-plugin'											// Page slug
	);

	// BaseURL
	add_settings_field(
		'loginid_dwp_base_url_field',							// ID
		__('Base URL', 'loginid-directweb-plugin'),					// Title
		'loginid_dwp_text_input_field_callback',					// Callback function
		'loginid-directweb-plugin',											// Page slug
		'loginid_dwp_minimum_settings_section',							// Settings Section ID
		array('base_url', 'example: https://directweb.usw1.loginid.io') //params to pass to callback
	);

		// BaseURL
		add_settings_field(
			'loginid_dwp_api_key_field',							// ID
			__('API Key', 'loginid-directweb-plugin'),					// Title
			'loginid_dwp_text_input_field_callback',					// Callback function
			'loginid-directweb-plugin',											// Page slug
			'loginid_dwp_minimum_settings_section',							// Settings Section ID
			array('api_key', 'unique key obtained from <a href="https://usw1.loginid.io/en/integration" target="_blank">LoginID</a> ') //params to pass to callback
		);
}
add_action('admin_init', 'loginid_dwp_register_settings');

/**
 * Validate and sanitize user input before its saved to database
 *
 * @since 0.1.0
 */
function loginid_dwp_validater_and_sanitizer($settings)
{
	// list of inputs that requires sanitation
	$to_be_sanitized = array('base_url', 'api_key');

	foreach ($to_be_sanitized as $field) {
		// Sanitize each text field
		$settings[$field] = sanitize_text_field($settings[$field]);
	}

	return $settings;
}

/**
 * Get settings from database
 *
 * @return	Array	A merged array of default and settings saved in database. 
 *
 * @since 0.1.0
 */
function loginid_dwp_get_settings()
{

	$defaults = array(
		'setting_one' 	=> '1',
		'setting_two' 	=> '1',
	);

	$settings = get_option('loginid_dwp_settings', $defaults);

	return $settings;
}

/**
 * Enqueue Admin CSS and JS
 *
 * @since 0.1.0
 */
function loginid_dwp_admin_enqueue_css_js($hook)
{

	// Load only on  plugin pages
	if ($hook != "settings_page_loginid-directweb-plugin") {
		return;
	}

	// Main CSS
	// wp_enqueue_style( 'loginid_dwp-admin-main-css', LOGINID_DIRECTWEB_PLUGIN_URL . 'admin/css/main.css', '', LOGINID_DIRECTWEB_PLUGIN_VERSION_NUM );

	// Main JS
	// wp_enqueue_script( 'loginid_dwp-admin-main-js', LOGINID_DIRECTWEB_PLUGIN_URL . 'admin/js/main.js', array( 'jquery' ), false, true );
}
add_action('admin_enqueue_scripts', 'loginid_dwp_admin_enqueue_css_js');
