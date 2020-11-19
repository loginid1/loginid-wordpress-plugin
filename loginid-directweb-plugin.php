<?php
/**
 * Plugin Name: LoginID DirectWeb Plugin
 * Plugin URI: https://github.com/loginid1/wordpress-directweb-plugin
 * Description: Provides any user the option to login without a password
 * Author: LoginID
 * Author URI: https://loginid.io
 * Version: 0.1.0 Alpha
 * Text Domain: loginid-directweb-plugin
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
 * /public/						        - Front end files and functions that matter on the front end go here.
 * index.php					        - Dummy file.
 * (this file).php            - Main plugin file containing plugin name and other version info for WordPress.
 * uninstall.php				      - Fired when the plugin is uninstalled. 
 */
 
/**
 * ~ TODO ~
 *
 * - Update loginid_dwp_settings_link() 		in \admin\basic-setup.php
 * - Update loginid_dwp_footer_text()		in \admin\basic-setup.php
 * - Update loginid_dwp_add_menu_links() 	in \admin\admin-ui-setup.php
 * - Update loginid_dwp_register_settings() 	in \admin\admin-ui-setup.php
 * - Update UI format and settings		in \admin\admin-ui-render.php
 * - Update uninstall.php
 * 
 * - Update LOGINID_DIRECTWEB_PLUGIN_VERSION_NUM 			in loginid-directweb-plugin.php (keep this line for future updates)
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Define constants
 *
 * @since 0.1.0
 */
if ( ! defined( 'LOGINID_DIRECTWEB_PLUGIN_VERSION_NUM' ) ) 		define( 'LOGINID_DIRECTWEB_PLUGIN_VERSION_NUM'		, '0.1.0 Alpha' ); // Plugin version constant
if ( ! defined( 'LOGINID_DIRECTWEB_PLUGIN' ) )		define( 'LOGINID_DIRECTWEB_PLUGIN'		, trim( dirname( plugin_basename( __FILE__ ) ), '/' ) ); // Name of the plugin folder eg - 'loginid-directweb-plugin'
if ( ! defined( 'LOGINID_DIRECTWEB_PLUGIN_DIR' ) )	define( 'LOGINID_DIRECTWEB_PLUGIN_DIR'	, plugin_dir_path( __FILE__ ) ); // Plugin directory absolute path with the trailing slash. Useful for using with includes eg - /var/www/html/wp-content/plugins/loginid-directweb-plugin/
if ( ! defined( 'LOGINID_DIRECTWEB_PLUGIN_URL' ) )	define( 'LOGINID_DIRECTWEB_PLUGIN_URL'	, plugin_dir_url( __FILE__ ) ); // URL to the plugin folder with the trailing slash. Useful for referencing src eg - http://localhost/wp/wp-content/plugins/loginid-directweb-plugin/

/**
 * Add plugin version to database
 *
 * @refer https://codex.wordpress.org/Creating_Tables_with_Plugins#Adding_an_Upgrade_Function
 * @since 0.1.0
 */
update_option( 'abl_loginid_dwp_version', LOGINID_DIRECTWEB_PLUGIN_VERSION_NUM );	// Change this to add_option if a release needs to check installed version.

// Load everything
require_once( LOGINID_DIRECTWEB_PLUGIN_DIR . 'loader.php' );

// Register activation hook (this has to be in the main plugin file or refer bit.ly/2qMbn2O)
register_activation_hook( __FILE__, 'loginid_dwp_activate_plugin' );