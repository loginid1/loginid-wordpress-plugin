<?php
/**
 * Operations of the plugin are included here. 
 *
 * @since 0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Enqueues all scripts and styles required by this plugin
 * 
 * @since 0.1.0
 */
function loginid_dwp_enqueue_css_js()
{
	// Main CSS
	// wp_enqueue_style( 'loginid_dwp-main-css', LOGINID_DIRECTWEB_PLUGIN_URL . 'includes/main.css', '', LOGINID_DIRECTWEB_PLUGIN_VERSION_NUM );

	// Main JS
	wp_enqueue_script( 'loginid_dwp-main-js', LOGINID_DIRECTWEB_PLUGIN_URL . 'includes/main.js', array(), false, true );
}
add_action('wp_enqueue_scripts', 'loginid_dwp_enqueue_css_js');

