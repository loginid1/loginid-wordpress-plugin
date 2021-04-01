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
