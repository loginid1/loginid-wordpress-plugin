<?php

/**
 * Admin UI setup and render
 *
 * @since 0.1.0
 * @function	loginid_dw_minimum_settings_section_callback()	Callback function for General Settings section
 * @function	loginid_dw_general_settings_field_callback()	Callback function for General Settings field
 * @function	loginid_dw_admin_interface_render()				Admin interface renderer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Callback function for General Settings section
 *
 * @since 0.1.0
 */
function loginid_dw_minimum_settings_section_callback()
{
	$settings = loginid_dw_get_settings();
	$ending = $settings['base_url'] !== '' && $settings['api_key'] !== '' ? '' : ' You may obtain them from <a href="' . LOGINID_DIRECTWEB_LOGINID_ORIGIN . '/register/get-started-a" target="_blank">LoginID</a> manually, or try out our setup wizard above.';
	echo esc_html('<p>' . __('You will be able to obtain these two fields from either the setup wizard or manual setup process. The setup wizard will auto populate these two fields.' . $ending, 'loginid-directweb') . '</p>');
}

/**
 * Callback function for creating text input fields
 * 
 * @since 0.1.0
 * @param Array $args contains a list of settings in the following order [$settings_id, $description]
 */
function loginid_dw_text_input_field_callback($args)
{
	// destructure args
	[$settings_id, $description] = $args;

	// Get Settings
	$settings = loginid_dw_get_settings(); ?>

	<fieldset>
		<!-- Text Input -->
		<input type="text" name="loginid_dw_settings[<?php echo esc_attr($settings_id) ?>]" class="regular-text" value="<?php if (isset($settings[$settings_id]) && (!empty($settings[$settings_id]))) echo esc_attr($settings[$settings_id]); ?>" />
		<p class="description"><?php esc_html_e($description, 'loginid-directweb'); ?></p>
	</fieldset>
<?php
}

/**
 * Admin interface renderer
 *
 * @since 0.1.0
 */
function loginid_dw_admin_interface_render()
{

	if (!current_user_can('manage_options')) {
		return;
	}

	/**
	 * If settings are inside WP-Admin > Settings, then WordPress will automatically display Settings Saved. If not used this block
	 * @refer	https://core.trac.wordpress.org/ticket/31000
	 * If the user have submitted the settings, WordPress will add the "settings-updated" $_GET parameter to the url
	 *
		if ( isset( $_GET['settings-updated'] ) ) {
			// Add settings saved message with the class of "updated"
			add_settings_error( 'loginid_dw_settings_saved_message', 'loginid_dw_settings_saved_message', __( 'Settings are Saved', 'loginid-directweb' ), 'updated' );
		}
	
		// Show Settings Saved Message
		settings_errors( 'loginid_dw_settings_saved_message' ); 
	 */

	if (isset($_GET['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'loginid_dw_msg_nonce') && isset($_GET['loginid-admin-msg'])) {
		// some other loginid hood redirected here with an admin message
		add_settings_error('loginid_dw_settings_admin_msg', 'loginid_dw_settings_admin_msg', sanitize_text_field(wp_unslash($_GET['loginid-admin-msg'])), 'info');
		// then show the message
		settings_errors('loginid_dw_settings_admin_msg');
	}
?>

	<div class="wrap">
		<h1>LoginID DirectWeb</h1>

		<h4>Please select one of the following methods to complete setup.</h4>

		<div class="__loginid_tls_warning_banner" id="__loginid_tls_warning_banner">
			A TLS connection with a certificate signed by a trusted Certificate Authority is required for this plugin to work on production site. Check your URL and it should start with HTTPS
		</div>

		<div style="margin-top: 1em;">
			<input class="__loginid_dropdown-checkbox" type="checkbox" id="__loginid_wizard-checkbox" />
			<label class="button button-secondary __loginid_setup-wizard-label" for="__loginid_wizard-checkbox">Run Express Setup Wizard</label>
			<div class="__loginid_setup-wizard-dropdown">
				<h2>Setup Wizard</h2>
				<h4>This method will take less than 2 minutes to complete</h4>
				<h4>What happens when I click Run Wizard?</h4>
				<p>
					When you click the <strong> Run Wizard</strong> button below, you will be redirected to <a href="<?php echo esc_html(LOGINID_DIRECTWEB_LOGINID_ORIGIN) ?>"><?php echo esc_html(LOGINID_DIRECTWEB_LOGINID_ORIGIN) ?></a> to register an account and fill out some information about your website in order to generate your Base URL and API Key.
					<br />
					At the end of the process you will have the option to save data directly to this wordpress site with a click of a single button.
				</p>
				<p class="description">
					LoginID setup wizard will automate most actions during the process of obtaining <strong> Base URL </strong> and <strong> API Key </strong> below.
					<br />
					This will necessitate the collection of certain data from this site.
					<br />
					Here is a complete list of what this plugin will send to LoginID and why:
				</p>
				<p>
				<ol>
					<li><strong>Origin</strong> <code>
							<script>
								document.write(window.location.origin)
							</script>
						</code> Required field to generate your API_key, this is so that your api key is tied down to your current domain (layer of protection against hacking)</li>
					<li><strong>_wpnonce</strong> <code>example: 12abcd</code> Key to authorize changes to your wordpress settings. (used for automatic saving of settings). <b>LoginID will be using wp nonce transiently during credentials creation process and will not store it on loginID system.</b> This hash lives a maximum of 24 hours and can only be used to change BaseURL and APIKey settings for this plugin.</li>
				</ol>
				</p>
				<p>
					<i><b>By clicking on the <code>Run Wizard</code> button below you consent to sending the above data to LoginID and allow LoginID to save settings for this plugin on your behalf.
							<br />Using this wizard is >OPTIONAL< you can always visit <a href="<?php echo esc_html(LOGINID_DIRECTWEB_LOGINID_ORIGIN) ?>"><?php echo esc_html(LOGINID_DIRECTWEB_LOGINID_ORIGIN) ?></a> and manually input your site origin and obtain your API key and BaseUrl.
								<a href="https://docs.loginid.io/websdks/dw">refer to docs</a>
						</b></i>
				</p>
				<button class="button button-primary" id="__loginid_run_setup_wizard_button"> Run Wizard </button>
				<noscript><b>Setup Wizard requires javascript to function.</b></noscript>
				<script>
					(function() {
						const setupWizardButton = document.getElementById("__loginid_run_setup_wizard_button");
						setupWizardButton.addEventListener('click', () => {
							const nonce = "<?php echo esc_url(wp_create_nonce('loginid_dw_nonce_wizard')) ?>"
							const remoteLocation = "<?php echo esc_html(LOGINID_DIRECTWEB_LOGINID_ORIGIN) ?>/wordpress-directweb-plugin?origin=" + window.location.origin + "#" + nonce;
							window.location = remoteLocation;
						})
					})();
				</script>
			</div>
		</div>

		<div style="margin-top: 1em;">
			<input class="__loginid_dropdown-checkbox" type="checkbox" id="__loginid_help-checkbox" />
			<label class="button button-secondary __loginid_setup-wizard-label" for="__loginid_help-checkbox">Manual Step by Step Setup</label>
			<div class="__loginid_setup-wizard-dropdown">
				<h2>Setup Step by Step guide</h2>
				<p>
					In order for the LoginID to manage authentication on your behalf, it needs to be configured with your credentials, which you can obtain from LoginID’s dashboard by following the below steps:
				<ol>
					<li>
						Register on our dashboard using the above link <a href="<?php echo esc_url(LOGINID_DIRECTWEB_LOGINID_ORIGIN) ?>/en/integration" target="_blank">LoginID’s dashboard </a>
					</li>
					<li>
						Make sure you are on the integration page, if not use the navigation bar to select “Integration".
					</li>
					<li>
						Agree to our license on Step of the integration
					</li>
					<li>
						Choose DirectWeb Integration:
					</li>
					<li>
						In the resulting form, you must enter the following information. <b>Alternatively, you may use the Setup Wizard to automatically export this information to loginID.</b>
						<ol type="a">
							<li>
								App Name
							</li>
							<li>
								Website URL
							</li>
						</ol>
					</li>
					<li>
						Once you create a new integration you will be provided with the following on our dashboard which you can copy and paste into the below fields.
						<ol type="a">
							<li>
								The API key is used to configure LoginID’s JavaScript SDK from your web application
							</li>
							<li>
								Base URL which provides our SDK which LoginID environment the integration will be using
							</li>
						</ol>
					</li>
				</ol>
				</p>
			</div>
		</div>

		<form action="options.php" method="post">
			<?php
			// Output nonce, action, and option_page fields for a settings page.
			settings_fields('loginid_dw_settings_group');

			// Prints out all settings sections added to a particular settings page. 
			do_settings_sections('loginid-directweb');	// Page slug

			// Output save settings button
			submit_button(__('Save Settings', 'loginid-directweb'));
			?>
		</form>

		<h3>Using the Plugin</h3>
		<h4 class="description">There are 2 was to use the plugins: </h4>
		<p class="description"><strong>1. Generate Login or Registration pages.</strong></p>
		<p>
		<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
			<?php
			wp_nonce_field("loginid_dw_settings_group-options");
			?>
			<input type="hidden" name="action" value="loginid_dw_generate_page">
			<input type="submit" name="submit" class="button button-secondary" value="Generate Login Page">
			<input type="submit" name="submit" class="button button-secondary" value="Generate Register Page">
		</form>
		</p>
		<p class="description">
			<strong>
				2. Apply shortcodes to your existing Login & Registration pages. Please avoid putting this form in modals.
			</strong>
		</p>
		<p class="description">
			<strong>Login Form Shortcode</strong>
			<code>[<?php echo esc_html(LoginID_DirectWeb::getShortCodes()[LoginID_Operation::Login]) ?>]</code>
		</p>
		<p class="description">
			<strong>Register Form Shortcode</strong>
			<code>[<?php echo esc_html(LoginID_DirectWeb::getShortCodes()[LoginID_Operation::Register]) ?>]</code>
		</p>
		<p class="description">
			<strong>End User Settings Shortcode</strong>
			<code>[<?php echo esc_html(LoginID_DirectWeb::getShortCodes()['settings']) ?>]</code>
			For bests user experience for your end users. It is recommended to only use this short code on your user settings page.
		</p>
		<p class="description">
			<strong style="color: red">Note: If your site is not running on localhost, make sure to have TLS enabled.</strong>
		</p>
	</div>
<?php
}

/**
 * renders settings fields in the profile
 * 
 * @since 0.1.0
 */
function loginid_dw_attach_to_profile($user)
{
	$settings = loginid_dw_get_settings();
	$udata = get_user_meta($user->ID, LoginID_DB_Fields::udata_user_id, true);
	$sub = get_user_meta($user->ID, LoginID_DB_Fields::subject_user_id, true);
	$isEnabled = $udata !== '' && $sub !== '';
	if (!$isEnabled) {
		$udata = (new LoginID_DirectWeb())->generate_hashed_string($user->user_email);
	}

?>
	<h3 id="loginid-profile-information"><?php echo "Biometrics Login Setting" ?></h3>

	<table class="form-table">
		<tr>
			<th><label>Status</label></th>
			<td>
				<?php echo $isEnabled  ? 'Active' : 'No device added. Please add using below button' ?>
			</td>
		</tr>
		<?php if (!$isEnabled && wp_get_current_user()->ID === $user->ID) { ?>
			<tr style="display: none" id="__loginid_set_authenticator">
				<th><label>Setup Authenticator</label></th>
				<td>
					<button type="button" class="button" id="__loginid_use_an_authenticator_on_this_device">Add new device</button>
					<div id="__loginid_use_an_authenticator_on_this_device_response"></div>
					<div>
						<input type="hidden" disabled name="nonce" id="__loginid_input_nonce" value="<?php echo esc_textarea(wp_create_nonce("loginid_dw_save_to_profile_nonce")); ?>">
						<input type="hidden" disabled name="udata" id="__loginid_input_udata" value="<?php echo esc_textarea($udata) ?>">
						<input type="hidden" disabled name="baseurl" id="__loginid_input_baseurl" value="<?php echo esc_textarea($settings['base_url']) ?>">
						<input type="hidden" disabled name="apikey" id="__loginid_input_apikey" value="<?php echo esc_textarea($settings['api_key']) ?>">
					</div>
				</td>
			</tr>
		<?php } else if ($isEnabled && (in_array('administrator', (array) wp_get_current_user()->roles) || wp_get_current_user()->ID === $user->ID)) { ?>
			<tr style="display: none" id="__loginid_remove_authenticator">
				<th><label>Remove Authenticator</label></th>
				<td>
					<button type="button" class="button" id="__loginid_remove_authenticator_button">Remove device</button>
					<div id="__loginid_remove_authenticator_response">
						This action is not reversible.<br /> You will need another method of authentication to access this account. (like a password).
					</div>
					<div>
						<input type="hidden" disabled name="nonce" id="__loginid_input_nonce" value="<?php echo esc_attr(wp_create_nonce("loginid_dw_remove_from_profile_nonce")); ?>">
					</div>
				</td>
			</tr>
		<?php } ?>
	</table>
<?php }

add_action('show_user_profile', 'loginid_dw_attach_to_profile');
add_action('edit_user_profile', 'loginid_dw_attach_to_profile');
