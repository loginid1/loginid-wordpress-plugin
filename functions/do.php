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
function loginid_dwp_enqueue_css_js()
{
  // Main CSS
  wp_enqueue_style('loginid_dwp-main-css', LOGINID_DIRECTWEB_PLUGIN_URL . 'includes/main.css', '', LOGINID_DIRECTWEB_PLUGIN_VERSION_NUM);

  // Main JS
  // wp_enqueue_script('loginid_dwp-direct-web-js', LOGINID_DIRECTWEB_PLUGIN_URL . 'includes/loginid.direct_web.min.js', array(), false, true);
  // wp_enqueue_script('loginid_dwp-browser-js', LOGINID_DIRECTWEB_PLUGIN_URL . 'includes/loginid.browser.min.js', array(), false, true);
  wp_enqueue_script('loginid_dwp-main-js', LOGINID_DIRECTWEB_PLUGIN_URL . 'includes/main.js', array(), false, true);
}
add_action('wp_enqueue_scripts', 'loginid_dwp_enqueue_css_js');

/**
 * Adds a handle such that all my own scripts will be using ESmodules
 * this is done so I don't have to use webpack but sacrifices support for old browsers
 * This function is unused since it isn't needed anymore, babel transpile ES5 the javascript now.
 * 
 * @since 0.1.0
 */
function loginid_dwp_script_type_attribute($tag, $handle, $src)
{
  $whitelist = array('loginid_dwp-direct-web-js' => true, 'loginid_dwp-browser-js' => true, 'loginid_dwp-main-js' => true);
  if (isset($whitelist[$handle])) {
    // change the script tag by adding type="module" and return it.
    $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
    return $tag;
  }
  return $tag;
}
// commented out because this function isn't needed anymore
// add_filter('script_loader_tag', 'loginid_dwp_script_type_attribute', 10, 3);

/**
 * generates custom registration form
 * this form reacts to what is being posted and auto fills the input
 * 
 * @since 0.1.0
 */
function loginid_dwp_registration_form()
{

?>
  <form id="__loginid_register_form">
    <div>
      <label for="email">Email <strong>*</strong></label>
      <input id="__loginid_input_email" type="text" name="email" value="<?php echo (isset($_POST['email']) ? $_POST['email'] : null) ?>">
    </div>
    <div>
      <label for="username">Username <strong>*</strong></label>
      <input id="__loginid_input_username" type="text" name="username" value="<?php echo (isset($_POST['username']) ? $_POST['username'] : null) ?>">
    </div>
    <div id="__loginid_password_div" <?php echo (empty($_POST['password']) ? 'class="__loginid_hide-password"' : null) ?>>
      <label for="password">Password <strong>*</strong></label>
      <input id="__loginid_input_password" type="password" name="password" value="<?php echo (isset($_POST['password']) ? $_POST['password'] : null) ?>">
    </div>
    <input type="submit" name="submit" value="Next" />
    <input type="hidden" readonly name="baseurl" id="__loginid_input_baseurl" value="<?php echo loginid_dwp_get_settings()['base_url'] ?>">
    <input type="hidden" readonly name="apikey" id="__loginid_input_apikey" value="<?php echo loginid_dwp_get_settings()['api_key'] ?>">
  </form>
  <?php
}

/**
 * validates inputs email and username
 * 
 * @since 0.1.0
 * @param string $email string representing email
 * @param string $username string representing username
 * @return boolean true if valid, false if invalid, null if the code went wrong
 */
function loginid_dwp_email_username_validation($email, $username)
{
  $reg_errors = new WP_Error;
  if (empty($username) || empty($email)) {
    $reg_errors->add('field', 'Required form field is missing');
  }
  if (4 > strlen($username)) {
    $reg_errors->add('username_length', 'Username too short. At least 4 characters is required');
  }
  if (username_exists($username)) {

    $reg_errors->add('user_name', 'Sorry, that username already exists!');
  }

  if (!validate_username($username)) {
    $reg_errors->add('username_invalid', 'Sorry, the username you entered is not valid');
  }
  if (!is_email($email)) {
    $reg_errors->add('email_invalid', 'Email is not valid');
  }
  if (email_exists($email)) {
    $reg_errors->add('email', 'Email Already in use');
  }

  return !loginid_dwp_output_wp_error($reg_errors);
}

/**
 * validates inputs password
 * 
 * @since 0.1.0
 * @param string $password string representing password
 * @return boolean true if valid, false if invalid, null if something went extremely extremely wrong
 */
function loginid_dwp_password_validation($password)
{

  $reg_errors = new WP_Error;
  if (5 > strlen($password)) {
    $reg_errors->add('password', 'Password length must be greater than 5');
  }

  return !loginid_dwp_output_wp_error($reg_errors);
}


/**
 * Outputs WPError Objects
 * 
 * @since 0.1.0
 * @return boolean true if contains error; false if no errors; and null if input is not a wordpress error object
 */
function loginid_dwp_output_wp_error($reg_errors)
{
  if (is_wp_error($reg_errors)) {

    $contains_error = false;
    foreach ($reg_errors->get_error_messages() as $error) {
      $contains_error = true;
  ?>
      <div class="__loginid-error-style">
        <?php echo $error ?>
      </div>
<?php
    }
    return $contains_error;
  }
  return null;
}


/**
 * interfaces with wordpress to create user object
 * 
 * @since 0.1.0
 * @param string $email string representing email
 * @param string $username string representing username
 * @return Object wordpress user object
 */
function loginid_dwp_complete_registration_passwordless($email, $username)
{
  return loginid_dwp_complete_registration_array(array(
    'user_login'    =>   $username,
    'user_email'    =>   $email,
    'user_pass'     =>   wp_generate_password($length = 128, $include_standard_special_chars = true) // generates random 128 char password to fill in
  ));
}


/**
 * interfaces with wordpress to create user object with password
 * 
 * @since 0.1.0
 * @param string $email string representing email
 * @param string $username string representing username
 * @param string $password string representing password
 * @return Object wordpress user object
 */
function loginid_dwp_complete_registration_with_password($email, $username, $password)
{
  return loginid_dwp_complete_registration_array(array(
    'user_login'    =>   $username,
    'user_email'    =>   $email,
    'user_pass'     =>   $password,
  ));
}

/**
 * interfaces with wordpress to create user object
 * 
 * @since 0.1.0
 * @param array $userdata array containing userdata that could be processed by wp_insert_user();
 * @return Object wordpress user object
 */
function loginid_dwp_complete_registration_array($userdata)
{
  $user = wp_insert_user($userdata); // user object
  echo 'Registration complete. Goto <a href="' . get_site_url() . '/wp-login.php">login page</a>.';
  return $user;
}

/**
 * interfaces with 
 * 
 * @since 0.1.0
 * @return boolean true if valid, false if invalid
 */
function loginid_dwp_custom_registration()
{
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (loginid_dwp_email_username_validation($email, $username)  && loginid_dwp_password_validation($password)) {


      $email      =   sanitize_email($email);
      $username   =   sanitize_user($username);
      $password   =   esc_attr($password);

      loginid_dwp_complete_registration_with_password($email, $username, $password);
    }
  }
  loginid_dwp_registration_form();
}


/**
 * The callback function that will replace [shortcode]
 * 
 * @since 0.1.0
 */
function loginid_registration_shortcode()
{
  ob_start();
  loginid_dwp_custom_registration();
  return ob_get_clean();
}
// Register a new shortcode: [loginid_registration]
add_shortcode('loginid_registration', 'loginid_registration_shortcode');

/**
 * callback that sets redirect from wp-register and wp-login to custom login and register pages
 * 
 * @since 0.1.0
 */
function set_redirect()
{
  global $pagenow;
  if ('wp-login.php' == $pagenow) {
    // TODO: uncomment this when login page is stable
    // wp_redirect('login');
    // exit();
  }
}
add_action('init', 'set_redirect');
