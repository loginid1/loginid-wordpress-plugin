<?php

/**
 * Operations of the plugin are included here. 
 *
 * @since 0.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Enqueues all scripts and styles required by this plugin
 * 
 * @since 0.1.0
 */
function loginid_dw_enqueue_css_js()
{
  // Main CSS
  wp_enqueue_style('loginid_dw-main-css', LOGINID_DIRECTWEB_URL . 'includes/main.css', '', LOGINID_DIRECTWEB_VERSION_NUM);

  // Main JS
  // wp_enqueue_script('loginid_dw-direct-web-js', LOGINID_DIRECTWEB_URL . 'includes/loginid.direct_web.min.js', array(), false, true);
  // wp_enqueue_script('loginid_dw-browser-js', LOGINID_DIRECTWEB_URL . 'includes/loginid.browser.min.js', array(), false, true);
  wp_enqueue_script('loginid_dw-main-js', LOGINID_DIRECTWEB_URL . 'includes/main.js', array(), false, true);
}
add_action('wp_enqueue_scripts', 'loginid_dw_enqueue_css_js');

/**
 * Adds a handle such that all my own scripts will be using ESmodules
 * this is done so I don't have to use webpack but sacrifices support for old browsers
 * This function is unused since it isn't needed anymore, babel transpile ES5 the javascript now.
 * 
 * @since 0.1.0
 */
// function loginid_dw_script_type_attribute($tag, $handle, $src)
// {
//   $whitelist = array('loginid_dw-direct-web-js' => true, 'loginid_dw-browser-js' => true, 'loginid_dw-main-js' => true);
//   if (isset($whitelist[$handle])) {
//     // change the script tag by adding type="module" and return it.
//     $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
//     return $tag;
//   }
//   return $tag;
// }
// commented out because this function isn't needed anymore
// add_filter('script_loader_tag', 'loginid_dw_script_type_attribute', 10, 3);

