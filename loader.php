<?php
/**
 * Loads the plugin files
 *
 * @since 0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// load composer dependancies
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
  require __DIR__ . '/vendor/autoload.php';
}

// Load basic setup. Plugin list links, text domain, footer links etc. 
require_once( LOGINID_DIRECTWEB_DIR . 'admin/basic-setup.php' );

// Load admin setup. Register menus and settings
require_once( LOGINID_DIRECTWEB_DIR . 'admin/admin-ui-setup.php' );

// Render Admin UI
require_once( LOGINID_DIRECTWEB_DIR . 'admin/admin-ui-render.php' );

// Perform main plugin function 
require_once( LOGINID_DIRECTWEB_DIR . 'functions/directweb.php' );
LoginID_DirectWeb::bootstrap();

// Do Other plugin operations
require_once( LOGINID_DIRECTWEB_DIR . 'functions/do.php' );
