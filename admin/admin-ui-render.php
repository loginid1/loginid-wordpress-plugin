<?php

/**
 * Admin UI setup and render
 *
 * @since 0.1.0
 * @function	loginid_dwp_minimum_settings_section_callback()	Callback function for General Settings section
 * @function	loginid_dwp_general_settings_field_callback()	Callback function for General Settings field
 * @function	loginid_dwp_admin_interface_render()				Admin interface renderer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Callback function for General Settings section
 *
 * @since 0.1.0
 */
function loginid_dwp_minimum_settings_section_callback()
{
	$settings = loginid_dwp_get_settings();
	$ending = $settings['base_url'] !== '' && $settings['api_key'] !== '' ? '' : ' You may obtain them from <a href="https://usw1.loginid.io/register/get-started-a" target="_blank">LoginID</a> manually, or try out our setup wizard above.';
	echo '<p>' . __('Required configurations for the plugin to work.' . $ending, 'loginid-directweb-plugin') . '</p>';
}

/**
 * Callback function for creating text input fields
 * 
 * @since 0.1.0
 * @param Array $args contains a list of settings in the following order [$settings_id, $description]
 */
function loginid_dwp_text_input_field_callback($args)
{
	// destructure args
	[$settings_id, $description] = $args;

	// Get Settings
	$settings = loginid_dwp_get_settings(); ?>

	<fieldset>
		<!-- Text Input -->
		<input type="text" name="loginid_dwp_settings[<?php echo $settings_id ?>]" class="regular-text" value="<?php if (isset($settings[$settings_id]) && (!empty($settings[$settings_id]))) echo esc_attr($settings[$settings_id]); ?>" />
		<p class="description"><?php _e($description, 'loginid-directweb-plugin'); ?></p>
	</fieldset>
<?php
}

/**
 * Admin interface renderer
 *
 * @since 0.1.0
 */
function loginid_dwp_admin_interface_render()
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
			add_settings_error( 'loginid_dwp_settings_saved_message', 'loginid_dwp_settings_saved_message', __( 'Settings are Saved', 'loginid-directweb-plugin' ), 'updated' );
		}
	
		// Show Settings Saved Message
		settings_errors( 'loginid_dwp_settings_saved_message' ); 
	 */

	if (isset($_GET['loginid-admin-msg'])) {
		// some other loginid hood redirected here with an admin message
		add_settings_error('loginid_dwp_settings_admin_msg', 'loginid_dwp_settings_admin_msg', sanitize_text_field($_GET['loginid-admin-msg']), 'info');
		// then show the message
		settings_errors('loginid_dwp_settings_admin_msg');
	}
?>

	<div class="wrap">
		<h1>LoginID DirectWeb Plugin</h1>

		<div style="margin-top: 1em;">
			<input class="__loginid_setup-wizard-checkbox" type="checkbox" id="__loginid_setup-wizard-checkbox" />
			<label class="button button-secondary __loginid_setup-wizard-label" for="__loginid_setup-wizard-checkbox">Run Setup Wizard</label>
			<div class="__loginid_setup-wizard-dropdown">
				<h2>Setup Wizard</h2>
				<h4>What happens when I click Run Wizard?</h4>
				<p>
					When you click the <strong> Run Wizard</strong> button below, you will be redirected to <a href="https://usw1.loginid.io/">https://usw1.loginid.io/</a> to register an account and fill out some information about your website in order to generate your Base URL and API Key.
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
						<li><strong>_wpnonce</strong> <code>example: 12abcd</code> Key to authorize changes to your wordpress settings. (used for automatic saving of settings) </li>
						<li><strong>Plugin name</strong> <code>loginid-directweb-plugin</code> To make sure that the correct wizard is used. </li>
					</ol>
				</p>
				<form action="http://localhost:8080/wordpress-directweb-plugin" method="get">
					<?php
					// Output nonce, action, and option_page fields for a settings page.
					wp_nonce_field("loginid_dwp_settings_group-options");
					?>
					<input type="hidden" name="origin" id="__loginid_form_origin" />
					<script>
						document.getElementById('__loginid_form_origin').value = window.location.origin;
					</script>
					<input type="submit" name="submit" class="button button-primary" value="Run Wizard">
				</form>
			</div>
		</div>

		<form action="options.php" method="post">
			<?php
			// Output nonce, action, and option_page fields for a settings page.
			settings_fields('loginid_dwp_settings_group');

			// Prints out all settings sections added to a particular settings page. 
			do_settings_sections('loginid-directweb-plugin');	// Page slug

			// Output save settings button
			submit_button(__('Save Settings', 'loginid-directweb-plugin'));
			?>
		</form>

		<p>
			<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
				<?php
				wp_nonce_field("loginid_dwp_settings_group-options");
				?>
				<input type="hidden" name="action" value="loginid_dwp_generate_page">
				<input type="submit" name="submit" class="button button-secondary" value="Generate Login Page">
				<input type="submit" name="submit" class="button button-secondary" value="Generate Register Page">
			</form>
		</p>
		<p class="description">
			<strong>Or paste the following shortcodes on your designated login or register pages.</strong>
		</p>
		<p class="description">
			<strong>Login Form Shortcode</strong>
			<code>[<?php echo LoginID_DirectWeb::getShortCodes()[LoginID_Operation::Login] ?>]</code>
		</p>
		<p class="description">
			<strong>Register Form Shortcode</strong>
			<code>[<?php echo LoginID_DirectWeb::getShortCodes()[LoginID_Operation::Register] ?>]</code>
		</p>
	</div>
<?php
}
