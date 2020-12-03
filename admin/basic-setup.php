<?php

/**
 * Basic setup functions for the plugin
 *
 * @since 0.1.0
 * @function	loginid_dwp_activate_plugin()		Plugin activatation todo list
 * @function	loginid_dwp_load_plugin_textdomain()	Load plugin text domain
 * @function	loginid_dwp_settings_link()			Print direct link to plugin settings in plugins list in admin
 * @function	loginid_dwp_plugin_row_meta()		Add donate and other links to plugins list
 * @function	loginid_dwp_footer_text()			Admin footer text
 * @function	loginid_dwp_footer_version()			Admin footer version
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Plugin activatation todo list
 *
 * This function runs when user activates the plugin. Used in register_activation_hook in the main plugin file. 
 * @since 0.1.0
 */
function loginid_dwp_activate_plugin()
{
}

/**
 * Load plugin text domain
 * (this function was inherited from project starter, currently only support english)
 * 
 */
// function loginid_dwp_load_plugin_textdomain() {
//     load_plugin_textdomain( 'loginid-directweb-plugin', false, '/loginid-directweb-plugin/languages/' );
// }
// add_action( 'plugins_loaded', 'loginid_dwp_load_plugin_textdomain' );

/**
 * Print direct link to plugin settings in plugins list in admin
 *
 * @since 0.1.0
 */
function loginid_dwp_settings_link($links)
{
	return array_merge(
		array(
			'settings' => '<a href="' . admin_url('options-general.php?page=loginid-directweb-plugin') . '">' . __('Settings', 'loginid-directweb-plugin') . '</a>'
		),
		$links
	);
}
add_filter('plugin_action_links_' . LOGINID_DIRECTWEB_PLUGIN . '/loginid_dwp_loginid-directweb-plugin.php', 'loginid_dwp_settings_link');

/**
 * Add donate and other links to plugins list
 * (This function was inherited from project starter)
 * 
 */
// function loginid_dwp_plugin_row_meta( $links, $file ) {
// 	if ( strpos( $file, 'loginid_dwp_loginid-directweb-plugin.php' ) !== false ) {
// 		$new_links = array(
// 				'donate' 	=> '<a href="http://millionclues.com/donate/" target="_blank">Donate</a>',
// 				'kuttappi' 	=> '<a href="http://kuttappi.com/" target="_blank">My Travelogue</a>',
// 				'hireme' 	=> '<a href="http://millionclues.com/portfolio/" target="_blank">Hire Me For A Project</a>',
// 				);
// 		$links = array_merge( $links, $new_links );
// 	}
// 	return $links;
// }
// add_filter( 'plugin_row_meta', 'loginid_dwp_plugin_row_meta', 10, 2 );

/**
 * Admin footer text
 *
 * A function to add footer text to the settings page of the plugin. Footer text contains plugin rating and donation links.
 * Note: Remove the rating link if the plugin doesn't have a WordPress.org directory listing yet. (i.e. before initial approval)
 *
 * @since 0.1.0
 * @refer https://codex.wordpress.org/Function_Reference/get_current_screen
 */
function loginid_dwp_footer_text($default)
{

	// Retun default on non-plugin pages
	$screen = get_current_screen();
	if ($screen->id !== "settings_page_loginid-directweb-plugin") {
		return $default;
	}

	$loginid_dwp_footer_text = sprintf(
		__('If you like this plugin, please leave a <a href="%s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating to support continued development. Thanks a bunch!', 'loginid-directweb-plugin'),
		'https://wordpress.org/support/plugin/loginid-directweb-plugin/reviews/?rate=5#new-post',
	);

	return $loginid_dwp_footer_text;
}
add_filter('admin_footer_text', 'loginid_dwp_footer_text');

/**
 * Admin footer version
 *
 * @since 0.1.0
 */
function loginid_dwp_footer_version($default)
{

	// Retun default on non-plugin pages
	$screen = get_current_screen();
	if ($screen->id !== 'settings_page_loginid-directweb-plugin') {
		return $default;
	}

	return 'Plugin version ' . LOGINID_DIRECTWEB_PLUGIN_VERSION_NUM;
}
add_filter('update_footer', 'loginid_dwp_footer_version', 11);
