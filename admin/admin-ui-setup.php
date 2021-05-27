<?php

/**
 * Admin setup for the plugin
 *
 * @since 0.1.0
 * @function	loginid_dw_add_menu_links()		Add admin menu pages
 * @function	loginid_dw_register_settings	Register Settings
 * @function	loginid_dw_validator_and_sanitizer()	Validate And Sanitize User Input Before Its Saved To Database
 * @function	loginid_dw_get_settings()		Get settings from database
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Add admin menu pages
 *
 * @since 0.1.0
 * @refer https://developer.wordpress.org/plugins/administration-menus/
 */
function loginid_dw_add_menu_links()
{
	add_options_page(
		__('LoginID DirectWeb', 'loginid-directweb'),
		__('LoginID DirectWeb', 'loginid-directweb'),
		'update_core', // super admin and administrator (single site)
		'loginid-directweb',
		'loginid_dw_admin_interface_render'
	);
}
add_action('admin_menu', 'loginid_dw_add_menu_links');

/**
 * Register Settings
 *
 * @since 0.1.0
 */
function loginid_dw_register_settings()
{

	// Register Setting
	register_setting(
		'loginid_dw_settings_group', 			// Group name
		'loginid_dw_settings', 					// Setting name = html form <input> name on settings form
		'loginid_dw_validator_and_sanitizer'	// Input sanitizer
	);

	// Register A New Section
	add_settings_section(
		'loginid_dw_minimum_settings_section',							// ID
		__('Configure the Plugin', 'loginid-directweb'),		// Title
		'loginid_dw_minimum_settings_section_callback',					// Callback Function
		'loginid-directweb'											// Page slug
	);

	// BaseURL
	add_settings_field(
		'loginid_dw_base_url_field',							// ID
		__('Base URL', 'loginid-directweb'),					// Title
		'loginid_dw_text_input_field_callback',					// Callback function
		'loginid-directweb',											// Page slug
		'loginid_dw_minimum_settings_section',							// Settings Section ID
		array('base_url', 'This plugin will use the server at this URL to authenticate users. Obtain from <a href="' . LOGINID_DIRECTWEB_LOGINID_ORIGIN . '/en/integration" target="_blank">LoginID</a>') //params to pass to callback
	);

	// BaseURL
	add_settings_field(
		'loginid_dw_api_key_field',							// ID
		__('API Key', 'loginid-directweb'),					// Title
		'loginid_dw_text_input_field_callback',					// Callback function
		'loginid-directweb',											// Page slug
		'loginid_dw_minimum_settings_section',							// Settings Section ID
		array('api_key', 'unique key obtained from <a href="' . LOGINID_DIRECTWEB_LOGINID_ORIGIN . '/en/integration" target="_blank">LoginID</a> ') //params to pass to callback
	);
}
add_action('admin_init', 'loginid_dw_register_settings');

/**
 * Validate and sanitize user input before its saved to database
 *
 * @since 0.1.0
 */
function loginid_dw_validator_and_sanitizer($settings)
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
function loginid_dw_get_settings()
{

	$defaults = array(
		'base_url' 	=> '',
		'api_key' 	=> '',
	);

	$settings = get_option('loginid_dw_settings', $defaults);

	return $settings;
}

/**
 * Enqueue Admin CSS and JS
 *
 * @since 0.1.0
 */
function loginid_dw_admin_enqueue_css_js($hook)
{

	if ($hook == "settings_page_loginid-directweb") {
		// Load only on plugin options page
		// main JS for settings page detection
		wp_enqueue_script('loginid_dw-admin-settings-js', LOGINID_DIRECTWEB_URL . 'admin/css/main.js', '', LOGINID_DIRECTWEB_VERSION_NUM);
		// Main CSS
		wp_enqueue_style('loginid_dw-admin-main-css', LOGINID_DIRECTWEB_URL . 'admin/css/main.css', '', LOGINID_DIRECTWEB_VERSION_NUM);
	}
	if ($hook === "profile.php" || $hook === "user-edit.php") {
		// main js processes loginid direct web api stuff
		wp_enqueue_script('loginid_dw-admin-main-js', LOGINID_DIRECTWEB_URL . 'includes/main.js', array(), false, true);
	}
}
add_action('admin_enqueue_scripts', 'loginid_dw_admin_enqueue_css_js');

/**
 * Hook to process generate login and register pages request
 *
 * @since 0.1.0
 */
function loginid_dw_generate_page()
{
	if (isset($_POST['_wpnonce']) && isset($_POST['submit'])) {
		$which_page = sanitize_text_field(wp_unslash($_POST['submit']));
		$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
		if (wp_verify_nonce($nonce, 'loginid_dw_settings_group-options') !== false && ($which_page === 'Generate Register Page' || $which_page === 'Generate Login Page')) {
			$isRegister = $which_page === 'Generate Register Page';
			// we want to create the login and register pages here
			// Create register post object
			$register_post = array(
				'post_title'    => wp_strip_all_tags($isRegister ? 'Register' : 'Login'),
				'post_content'  => '[' . LoginID_DirectWeb::getShortCodes()[$isRegister ? LoginID_Operation::Register : LoginID_Operation::Login] . ']',
				'post_status'   => 'publish',
				'post_type'     => 'page',
			);
			// Insert the post into the database
			$result = wp_insert_post($register_post);

			if ($result > 0) {
				exit(esc_url(wp_safe_redirect(admin_url('options-general.php?page=loginid-directweb&loginid-admin-msg=' . ($isRegister ? 'Register' : 'Login') .  ' page created.'))));
			}
			exit(esc_url(wp_safe_redirect(admin_url('options-general.php?page=loginid-directweb&loginid-admin-msg=' . 'Error while creating ' . ($isRegister ? 'Register' : 'Login') . ' page.'))));
		}
		exit(esc_url(wp_safe_redirect(admin_url('options-general.php?page=loginid-directweb&loginid-admin-msg=' . 'Error: Token Rejected'))));
	}
	exit(esc_url(wp_safe_redirect(admin_url('options-general.php?page=loginid-directweb&loginid-admin-msg=' . 'Error: something went very wrong.'))));
}
add_action('admin_post_loginid_dw_generate_page', 'loginid_dw_generate_page');

/**
 * Hooks to add extra column for user settings
 * 
 * @since 0.1.0
 */
function loginid_dw_modify_user_table($columns)
{
	$new_columns = array();
	$is_created = false;
	foreach ($columns as $name => $label) {
		$new_columns[$name] = $label;
		if ($name === 'username') {
			$new_columns['loginid'] = 'LoginID';
			$is_created = true;
		}
	}
	if ($is_created === false) {
		$new_columns['loginid'] = 'LoginID';
	}
	return $new_columns;
}
add_filter('manage_users_columns', 'loginid_dw_modify_user_table');


/**
 * fills in the data for each user for the custom LoginID user settings column
 * 
 * @since 0.1.0
 * 
 * @param string $val, current value of the column
 * @param string $column_name, current column name
 * @param string $user_id, current userid of the row
 */
function loginid_dw_modify_user_table_row($val, $column_name, $user_id)
{
	// our specific column
	if ($column_name === 'loginid') {
		return get_the_author_meta(LoginID_DB_Fields::udata_user_id, $user_id);
	} else {
		// not our column return regular value
		return $val;
	}
}
add_filter('manage_users_custom_column', 'loginid_dw_modify_user_table_row', 10, 3);

/**
 * validates nonce
 * returns true if nonce is valid
 * 
 * @param $action, the context of the nonce
 * @since 1.0.14
 */
function loginid_dw_validate_nonce($action)
{
	return isset($_REQUEST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), $action);
}

/**
 * saves loginid to profile (ajax call)
 * 
 * @since 0.1.0
 */
function loginid_dw_save_to_profile()
{
	if (isset($_REQUEST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'loginid_dw_save_to_profile_nonce')) {
		if (empty($_REQUEST['loginid']) || !isset($_REQUEST['loginid'])) {
			exit('Error: Missing required field');
		} else {

			$loginid_data = sanitize_text_field(wp_unslash($_REQUEST['loginid']));
			$loginid_directweb = new LoginID_DirectWeb();
			$loginid_directweb->manual_minimal_init(wp_get_current_user()->user_email, $loginid_data);
			if ($loginid_directweb->add_authenticator_to_user()) {
				exit('Success: authenticator added to account. Please reload this page.');
			} else {
				exit('Error: failed to add authenticator to account');
			}
		}
	} else {
		exit("No naughty business please");
	}
}

add_action('wp_ajax_loginid_save_to_profile', 'loginid_dw_save_to_profile');

/**
 * saves removes loginid from profile (ajax call)
 * 
 * @since 0.1.0
 */
function loginid_dw_remove_from_profile()
{
	if (loginid_dw_validate_nonce("loginid_dw_remove_from_profile_nonce")) {
		$loginid_directweb = new LoginID_DirectWeb();
		$loginid_directweb->manual_email_init(wp_get_current_user()->user_email);
		if ($loginid_directweb->remove_authenticator_from_user()) {
			exit('Success: authenticator removed from account. Please reload this page.');
		} else {
			exit('Error: failed to remove authenticator from account');
		}
	} else {
		exit("No naughty business please");
	}
}

add_action('wp_ajax_loginid_remove_from_profile', 'loginid_dw_remove_from_profile');

/**
 * Saves the data obtained from loginID dashboard setup wizard
 *  
 * @since 1.0.9
 */
function loginid_dw_wizard_callback()
{
	// 
	if (loginid_dw_validate_nonce('loginid_dw_nonce_wizard')) {

		exit("
<html>
<body>
<noscript>Javascript is required for this to work</noscript>
<pre id=\"output\"></pre>
<script>
const urlParams = new URLSearchParams(window.location.search);
const hash = window.location.hash;
const output = `Wizard Redirecting ...`;
document.getElementById(\"output\").innerHTML = output;

const fields = [
	{ name: '_wpnonce', value: `" . esc_textarea(wp_create_nonce("loginid_dw_settings_group-options")) . "` },
	{ name: '_wp_http_referer', value: '/wp-admin/options-general.php?page=loginid-directweb' },
	{ name: 'option_page', value: 'loginid_dw_settings_group' },
	{ name: 'submit', value: 'Save Settings' },
	{ name: 'action', value: 'update' },
]

const hiddenForm = document.createElement('form');
hiddenForm.setAttribute('action', window.location.origin+`/wp-admin/options.php`);
hiddenForm.setAttribute('method', 'post');
// hiddenForm.style.display = 'none';
for (const { name, value } of fields) {
		const element = document.createElement('input');
		element.setAttribute('type', 'hidden');
		element.setAttribute('name', name);
		element.setAttribute('value', value);
		hiddenForm.appendChild(element);
}
let element = document.createElement('input');
element.setAttribute('type', 'hidden');
element.setAttribute('name', 'loginid_dw_settings[base_url]');
element.setAttribute('value', urlParams.get(\"base_url\"));
hiddenForm.appendChild(element);

element = document.createElement('input');
element.setAttribute('type', 'hidden');
element.setAttribute('name', 'loginid_dw_settings[api_key]');
element.setAttribute('value', hash.substring(1));
hiddenForm.appendChild(element);

document.body.appendChild(hiddenForm);
document.createElement('form').submit.call(hiddenForm);
hiddenForm.remove();
</script>
</body>
</html>
");
	}
	exit("
<html>
<body>
<p id=\"output\">Something went wrong, please try again later</p>
<a href=\"/wp-admin/options-general.php?page=loginid-directweb\">Take me back</a>
<body>
</html>
");
}
add_action('wp_ajax_loginid_wizard_callback', 'loginid_dw_wizard_callback');

/**
 * NO Priv display
 *  
 * @since 1.0.9
 */
function loginid_dw_nopriv_wizard_callback()
{

	exit("
<html>
<body>
<p id=\"output\">You must be authenticated to perform this action</p>
<a href=\"/\">Go Home</a>
<body>
</html>
");
}
add_action('wp_ajax_nopriv_loginid_wizard_callback', 'loginid_dw_nopriv_wizard_callback');
