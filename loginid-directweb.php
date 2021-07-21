<?php

/**
 * Plugin Name: LoginID DirectWeb
 * Plugin URI: https://github.com/loginid1/wordpress-directweb-plugin
 * Description: Provides any user the option to login without a password
 * Author: LoginID
 * Author URI: https://loginid.io
 * Version: 1.0.14
 * Text Domain: loginid-directweb
 * Domain Path: /languages
 * License: GPL v3 - https://www.gnu.org/licenses/gpl-3.0.en.html
 */

/**
 * This plugin was developed using the WordPress starter plugin template by Arun Basil Lal <arunbasillal@gmail.com>
 * Please leave this credit and the directory structure intact for future developers who might read the code.
 * @GitHub https://github.com/arunbasillal/WordPress-Starter-Plugin
 */

/**
 * ~ Directory Structure ~
 *
 * /admin/ 						        - Plugin backend stuff.
 * /functions/			          - Functions and plugin operations.
 * /includes/					        - External third party classes and libraries.
 * /languages/				        - Translation files go here. 
 * index.php					        - Dummy file.
 * (this file).php            - Main plugin file containing plugin name and other version info for WordPress.
 * uninstall.php				      - Fired when the plugin is uninstalled. 
 */

/**
 * ~ TODO ~
 *
 * - Update uninstall.php
 * 
 * - Update LOGINID_DIRECTWEB_VERSION_NUM 			in loginid-directweb.php (keep this line for future updates)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Define constants
 *
 * @since 0.1.0
 */
if (!defined('LOGINID_DIRECTWEB_VERSION_NUM'))     define('LOGINID_DIRECTWEB_VERSION_NUM', '1.0.14'); // Plugin version constant
if (!defined('LOGINID_DIRECTWEB'))    define('LOGINID_DIRECTWEB', trim(dirname(plugin_basename(__FILE__)), '/')); // Name of the plugin folder eg - 'loginid-directweb'
if (!defined('LOGINID_DIRECTWEB_DIR'))  define('LOGINID_DIRECTWEB_DIR', plugin_dir_path(__FILE__)); // Plugin directory absolute path with the trailing slash. Useful for using with includes eg - /var/www/html/wp-content/plugins/loginid-directweb/
if (!defined('LOGINID_DIRECTWEB_URL'))  define('LOGINID_DIRECTWEB_URL', plugin_dir_url(__FILE__)); // URL to the plugin folder with the trailing slash. Useful for referencing src eg - http://localhost/wp/wp-content/plugins/loginid-directweb/
if (!defined('LOGINID_DIRECTWEB_LOGINID_ORIGIN'))     define('LOGINID_DIRECTWEB_LOGINID_ORIGIN', 'https://usw1.loginid.io'); // Plugin version constant

/**
 * Add plugin version to database
 *
 * @refer https://codex.wordpress.org/Creating_Tables_with_Plugins#Adding_an_Upgrade_Function
 * @since 0.1.0
 */
update_option('abl_loginid_dw_version', LOGINID_DIRECTWEB_VERSION_NUM);  // Change this to add_option if a release needs to check installed version.

// Load everything
require_once(LOGINID_DIRECTWEB_DIR . 'loader.php');

// Register activation hook (this has to be in the main plugin file or refer bit.ly/2qMbn2O)
register_activation_hook(__FILE__, 'loginid_dw_activate_plugin');


/**
 * Settings link on plugin page
 * 
 * @since 0.1.0
 */
function loginid_dw_plugin_page_settings_link($links)
{
  // Build and escape the URL.
  $url =  admin_url('options-general.php?page=loginid-directweb');
  // Create the link.
  $settings_link = "<a href='$url'>" . __('Settings') . '</a>';
  // Adds the link to the end of the array.
  array_push(
    $links,
    $settings_link
  );
  return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'loginid_dw_plugin_page_settings_link');

function loginid_dw_plugin_support_faq_links($links_array, $plugin_file_name, $plugin_data, $status)
{

  if (strpos($plugin_file_name, basename(__FILE__))) {
    // you can still use array_unshift() to add links at the beginning
    $links_array[] = '<a target="_blank" href="https://docs.loginid.io">Docs</a>';
    $links_array[] = '<a target="_blank" href="https://loginid.io/support">Support</a>';
  }

  return $links_array;
}

add_filter('plugin_row_meta', 'loginid_dw_plugin_support_faq_links', 10, 4);



/**
 *  
 *  If woo commerce exists mount hook to alternative way to obtain template from this plugin.
 */
// if (class_exists('woocommerce')) {
  add_filter('woocommerce_locate_template', 'loginid_dw_plugin_woo_addon_plugin_template', 1, 3);
// }

/**
 * makes woo commerce check this plugin directory for templates first
 * source: https://wisdmlabs.com/blog/override-woocommerce-templates-plugin/
 */
function loginid_dw_plugin_woo_addon_plugin_template($template, $template_name, $template_path)
{
  global $woocommerce;
  $_template = $template;
  if (!$template_path)
    $template_path = $woocommerce->template_url;

  $plugin_path  = untrailingslashit(plugin_dir_path(__FILE__))  . '/template/woocommerce/';

  // Look within passed path within the theme - this is priority
  $template = locate_template(
    array(
      $template_path . $template_name,
      $template_name
    )
  );

  if (!$template && file_exists($plugin_path . $template_name))
    $template = $plugin_path . $template_name;

  if (!$template)
    $template = $_template;

  return $template;
}
