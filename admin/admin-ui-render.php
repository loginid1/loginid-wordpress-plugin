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
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Callback function for General Settings section
 *
 * @since 0.1.0
 */
function loginid_dwp_minimum_settings_section_callback() {
	echo '<p>' . __('Required configurations for the plugin to work', 'loginid-directweb-plugin') . '</p>';
}

/**
 * Callback function for creating text input fields
 * 
 * @since 0.1.0
 */
function loginid_dwp_text_input_field_callback($args) {
		// destructure args
		[$settings_id, $description] = $args;

		// Get Settings
		$settings = loginid_dwp_get_settings();?>
		
		<fieldset>
			<!-- Text Input -->
			<input type="text" name="loginid_dwp_settings[<?php echo $settings_id ?>]" class="regular-text" value="<?php if ( isset( $settings[$settings_id] ) && ( ! empty($settings[$settings_id]) ) ) echo esc_attr($settings[$settings_id]); ?>"/>
			<p class="description"><?php _e($description, 'loginid-directweb-plugin'); ?></p>
		</fieldset>
		<?php	
}
 
/**
 * Admin interface renderer
 *
 * @since 0.1.0
 */ 
function loginid_dwp_admin_interface_render () {
	
	if ( ! current_user_can( 'manage_options' ) ) {
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
	?> 
	
	<div class="wrap">	
		<h1>LoginID DirectWeb Plugin</h1>
		
		<form action="options.php" method="post">		
			<?php
			// Output nonce, action, and option_page fields for a settings page.
			settings_fields( 'loginid_dwp_settings_group' );
			
			// Prints out all settings sections added to a particular settings page. 
			do_settings_sections( 'loginid-directweb-plugin' );	// Page slug
			
			// Output save settings button
			submit_button( __('Save Settings', 'loginid-directweb-plugin') );
			?>
		</form>
	</div>
	<?php
}