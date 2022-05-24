<?php
// Exit if accessed directly

if (!defined('ABSPATH')) exit;

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

/**
 * this class serves basically as an enum
 * removing stuff here will cause all error messages to break
 */
abstract class LoginID_Error
{
  public const Code = 'code';
  public const Message = 'message';
}

/**
 * this class serves basically as an enum
 * removing stuff here will cause some or all error messages to not display correctly
 */
abstract class LoginID_Errors
{
  public const Sample = array(LoginID_Error::Code => "", LoginID_Error::Message => "");

  public const VersionMismatch = array(LoginID_Error::Code => "version_mismatch", LoginID_Error::Message => "this page was not setup correctly, please contact an admin");
  public const CriticalError = array(LoginID_Error::Code => "critical_error", LoginID_Error::Message => "this page has suffered a critical error, please contact an admin");

  public const UsernameMissing = array(LoginID_Error::Code => "username_missing", LoginID_Error::Message => "Required field username is missing");
  public const UsernameLength = array(LoginID_Error::Code => "username_length", LoginID_Error::Message => "Username too short. At least 4 characters is required");
  public const UsernameUnavailable = array(LoginID_Error::Code => "username_unavailable", LoginID_Error::Message => "Sorry, that username is unavailable");
  public const UsernameInvalid = array(LoginID_Error::Code => "username_invalid", LoginID_Error::Message => "Sorry, the username you entered is not valid");

  public const EmailMissing = array(LoginID_Error::Code => "email_missing", LoginID_Error::Message => "Required field email is missing");
  public const EmailInvalid = array(LoginID_Error::Code => "email_invalid", LoginID_Error::Message => "Email is not valid");
  public const EmailUnavailable = array(LoginID_Error::Code => "email_unavailable", LoginID_Error::Message => "Sorry, that email is already in use.");
  public const EmailNotFound = array(LoginID_Error::Code => "email_not_found", LoginID_Error::Message => "Email not found");

  public const PasswordMissing = array(LoginID_Error::Code => "password_missing", LoginID_Error::Message => "Required field password is missing");
  public const PasswordLength = array(LoginID_Error::Code => "password_length", LoginID_Error::Message => "Password length must be greater than 8");

  public const LoginFailed = array(LoginID_Error::Code => "login_failed", LoginID_Error::Message => "Incorrect email/password combination");

  public const PluginError = array(LoginID_Error::Code => "loginid_error", LoginID_Error::Message => "LoginID Directweb Error");

  public const LoginIDCannotVerify = array(LoginID_Error::Code => "loginid_cannot_verify", LoginID_Error::Message => "Your identity could not be verified");
  public const LoginTokenCannotVerify = array(LoginID_Error::Code => "login_token_cannot_verify", LoginID_Error::Message => "Your identity token could not be verified");
  public const LoginTokenInvalidClaim = array(LoginID_Error::Code => "login_token_corrupted", LoginID_Error::Message => "Your identity token made invalid claim");
  public const LoginIDServerError = array(LoginID_Error::Code => "loginid_server_error", LoginID_Error::Message => "LoginID Server Error, please use password login for now.");

  public const DatabaseError = array(LoginID_Error::Code => "database_error", LoginID_Error::Message => "A critical database error has occured.");

  public const Example = array(LoginID_Error::Code => "", LoginID_Error::Message => "");
  // usage add(LoginID_Errors::Example[LoginID_Error::Code], LoginID_Errors::Example[LoginID_Error::Message]);
}

/**
 * this class serves basically as an enum
 * do not remove stuff from this class, or it will break the plugin
 */
abstract class LoginID_Operation
{
  public const Login = 'login';
  public const Register = 'register';
  public const Next = 'next';
}

/**
 * this class serves basically as an enum
 * do not remove stuff from this class, or it will break the plugin
 * 
 * 
 */
abstract class LoginID_FIDO2
{
  public const Supported = 'supported';
}

abstract class LoginID_DB_Fields
{
  public const subject_user_id = '__loginid_subject_user_id';
  public const udata_user_id = '__loginid_udata_user_id';
}

abstract class LoginID_Strategy
{
  public const Password = "password";
  public const Passwordless = "passwordless";
}


/**
 * Library class of this plugin
 *
 * @since 0.1.0
 */
class LoginID_DirectWeb
{
  // strings that contains the shortcode values [loginid_registration] and [loginid_login]
  protected const ShortCodes = array(LoginID_Operation::Register => "loginid_registration", LoginID_Operation::Login => "loginid_login", "settings" => "loginid_settings");
  /**
   * Getter function of a protected resource short codes, returns a copy of that array
   * 
   * @since 0.1.0
   * @return array of array('register' -> shortcode, 'login' -> shortcode)
   */
  public static function getShortCodes()
  {
    return self::ShortCodes;
  }

  /**
   * Instantiate this object and object specific hooks into wordpress
   * 
   * @since 0.1.0
   */
  public static function bootstrap()
  {

    $self = new self();
    add_action('init', array($self, 'on_init'));
    add_action('template_redirect', array($self, 'redirect_if_applicable'));
    add_action("wp_body_open", array($self, 'render_banner'));
    // add short codes
    add_shortcode(self::ShortCodes[LoginID_Operation::Register], array($self, 'registration_shortcode'));
    add_shortcode(self::ShortCodes[LoginID_Operation::Login], array($self, 'login_shortcode'));
    add_shortcode(self::ShortCodes['settings'], array($self, 'settings_shortcode'));

    $self->add_woo_hook();
  }

  protected $release_the_fido; //whether or not to release loginid direct web information for directweb login
  protected $email;
  protected $username;
  protected $password;
  protected $wp_errors;
  protected $javascript_unsupported;
  protected $manually_display_password;
  protected $loginid;
  protected $user_id;
  private $validated_jwt_body;
  private $login_user_udata;
  private $current_auth_type; // flag what is currently being processed

  /**
   * constructor, basically sets default flags
   * 
   * @since 0.1.0
   */
  public function __construct()
  {
    $this->release_the_fido = false;
    $this->email = null;
    $this->username = null;
    $this->password = null;
    $this->optin = null;
    $this->redirect_message = '';

    $this->loginid = null;
    $this->user_id = null;

    $this->wp_errors = new WP_Error;
    $this->javascript_unsupported = false;
    $this->manually_display_password = false;
    // note that login_user_udata will only be available during login and after fido has been approved
    // it is an optimization to read database less.
    // only ever use this within a if($this->release_the_fido) block
    $this->login_user_udata = '';

    $this->validated_jwt_body = null;
  }


  /**
   *  If settings enabled, then add woo commerce template override from this plugin
   * 
   * @since 1.0.15
   */
  public function add_woo_hook()
  {
    $settings = loginid_dw_get_settings();
    if ($settings['enable_woo_integration']) {
      add_filter('woocommerce_locate_template', 'loginid_dw_plugin_woo_addon_plugin_template', 1, 3);
    }
  }

  /**
   * manually initialize this class, useful for cases where user needs to add authenticator to an existing account
   * 
   * @param string $email string of email
   * @param string $loginid JSON string of the loginid object returned from loginid servers
   */
  public function manual_minimal_init(string $email, string $loginid)
  {
    $this->loginid = json_decode(stripslashes($loginid));
    $this->email = $email;
  }

  /**
   * manually initialize this class, useful for cases where user needs to remove an authenticator from an existing account
   * 
   * @param string $email string of email
   */
  public function manual_email_init(string $email)
  {
    $this->email = $email;
  }
  /**
   * validates the <input name="loginid"> from the front end to verify page integrity
   * 
   * @since 0.1.0
   * @param string $input string from the post request 
   * @return string|boolean false if invalid, key string (should be LoginID_Operation::Login or LoginID_Operation::Register) if valid;
   */
  protected function validate_loginid_field($input)
  {
    return array_search($input, self::ShortCodes);
  }

  /**
   * validates input username
   * should only use this for register
   * 
   * @since 0.1.0
   * @return WP_Error list of errors, could be empty list too use count($reg_errors>$errors)
   */
  protected function validate_username()
  {
    $username = $this->username;

    $reg_errors = new WP_Error;
    if (empty($username)) {
      $reg_errors->add(LoginID_Errors::UsernameMissing[LoginID_Error::Code], LoginID_Errors::UsernameMissing[LoginID_Error::Message]);
    }
    if (4 > strlen($username)) {
      $reg_errors->add(LoginID_Errors::UsernameLength[LoginID_Error::Code], LoginID_Errors::UsernameLength[LoginID_Error::Message]);
    }
    if (username_exists($username)) {

      $reg_errors->add(LoginID_Errors::UsernameUnavailable[LoginID_Error::Code], LoginID_Errors::UsernameUnavailable[LoginID_Error::Message]);
    }
    if (!validate_username($username)) {
      $reg_errors->add(LoginID_Errors::UsernameInvalid[LoginID_Error::Code], LoginID_Errors::UsernameInvalid[LoginID_Error::Message]);
    }

    return $reg_errors;
  }
  /**
   * validates input email
   * works for both login and register
   * 
   * @since 0.1.0
   * @param string $login_type basically if 'login' or 'register', checks different stuff depending
   * @return WP_Error list of errors, could be empty list too use count($reg_errors>$errors)
   */
  protected function validate_email($login_type)
  {
    $email = $this->email;

    $reg_errors = new WP_Error;
    if (empty($email)) {
      $reg_errors->add(LoginID_Errors::EmailMissing[LoginID_Error::Code], LoginID_Errors::EmailMissing[LoginID_Error::Message]);
    }
    if (!is_email($email)) {
      $reg_errors->add(LoginID_Errors::EmailInvalid[LoginID_Error::Code], LoginID_Errors::EmailInvalid[LoginID_Error::Message]);
    }
    if ($login_type === LoginID_Operation::Register) {
      // checks only for register goes here
      if (email_exists($email)) {
        $reg_errors->add(LoginID_Errors::EmailUnavailable[LoginID_Error::Code], LoginID_Errors::EmailUnavailable[LoginID_Error::Message]);
      }
    }
    if ($login_type === LoginID_Operation::Login) {
      // checks only for login goes here
      if (!email_exists($email)) {
        $reg_errors->add(LoginID_Errors::EmailNotFound[LoginID_Error::Code], LoginID_Errors::EmailNotFound[LoginID_Error::Message]);
      }
    }

    return $reg_errors;
  }

  /**
   * validates input password
   * 
   * @since 0.1.0
   * @param string $login_type basically if 'login' or 'register', checks different stuff depending
   * @return boolean true if valid, false if invalid, null if something went extremely extremely wrong
   */
  function validate_password($login_type)
  {
    $password = $this->password;

    $reg_errors = new WP_Error;
    if (empty($password)) {
      $reg_errors->add(LoginID_Errors::PasswordMissing[LoginID_Error::Code], LoginID_Errors::PasswordMissing[LoginID_Error::Message]);
    }
    if ($login_type === LoginID_Operation::Register) {
      // checks only for register goes here
      if (8 > strlen($password)) {
        $reg_errors->add(LoginID_Errors::PasswordLength[LoginID_Error::Code], LoginID_Errors::PasswordLength[LoginID_Error::Message]);
      }
    }

    return $reg_errors;
  }

  /**
   * sends GET request with kid to loginid servers to retrieve public key
   * this is a pure function
   * 
   * @return string|false raw output from loginid server, false if failed
   */
  protected function get_jwt_public_key($kid)
  {
    $settings = loginid_dw_get_settings();
    $url = $settings['base_url'] . '/certs';

    $fields = array(
      'kid' => $kid,
    );

    $data = http_build_query($fields);


    $response = wp_remote_get($url . "?" . $data);
    $result = wp_remote_retrieve_body($response);
    return $result;
  }

  /**
   * Expands JWT header into an object
   * this is a pure function
   * 
   * @since 0.1.0
   * @param string $jwt token
   * @return Object|false if successfully returns Object(head), if failed returns false
   */
  protected function decode_jwt_head($jwt)
  {
    [$jwt_header,,] = explode('.', $jwt);
    $jwt_header = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', $jwt_header))));
    if ($jwt_header === false) {
      return false;
    } else {
      return $jwt_header;
    }
  }

  /**
   * validates jwt contained in $this->loginid against public key obtained from loginid
   *
   * I expect stuff to be sanitized before calling this function
   * $this->loginid to be parsed into an object already
   * 
   * if successful this method will set $this->$validated_jwt_body = $jwt_body
   * 
   * @since 0.1.0
   * @return bool true if valid; false if invalid
   */
  protected function validate_loginid()
  {
    if (isset($this->loginid)) {
      $jwt = $this->loginid->{'jwt'};
      if (isset($jwt) && is_string($jwt)) {
        // split jwt into header and decode it
        $jwt_header = $this->decode_jwt_head($jwt);
        // ensure decode was successful
        if ($jwt_header !== false) {

          // ensure this is a jwt
          if (is_object($jwt_header))
            // need to then verify that jwt_header is correctly formed
            if (isset($jwt_header->{'alg'}) && isset($jwt_header->{'typ'}) && $jwt_header->typ === "JWT" && isset($jwt_header->{'kid'})) {
              // GET public key from loginid.io servers
              $public_key = $this->get_jwt_public_key($jwt_header->kid); // returns false if failed, string if successful
              if ($public_key !== false) {
                // encode previously decoded objects back into strings

                // verify using openssl_verify(original string, signature, public key, algorithm)
                try {
                  $result = JWT::decode($jwt, new Key($public_key, $jwt_header->alg));
                  // verification successful; now we know the jwt is legit at this point
                  $this->validated_jwt_body = $result;
                  return true;
                } catch (Exception $e) {
                }
              } else {
                // server error
                $this->wp_errors->add(LoginID_Errors::LoginIDServerError[LoginID_Error::Code], LoginID_Errors::LoginIDServerError[LoginID_Error::Message]);
              }
            }
        }
      }
    }
    $this->wp_errors->add(LoginID_Errors::LoginTokenCannotVerify[LoginID_Error::Code], LoginID_Errors::LoginTokenCannotVerify[LoginID_Error::Message]);
    // this runs, it means: malformed payload;
    return false;
  }

  /**
   * Only run this after JWT in $this->loginid has been validated see validate_loginid()
   * Checks JWT body information against database information, to match identities.
   *
   * I expect stuff to be sanitized before calling this function
   * $this->email
   * $this->validated_jwt_body = Object(jwt_body)
   * 
   * for login will also perform additional step of matching sub (subject, basically userid on loginid's side) to data in database;
   * 
   * @since 0.1.0
   * @param string $type "login" or "register"
   * @param string $user_id wordpress id of user to be verified (only needed if $type is login)
   * @return bool true if valid; false if invalid
   */
  protected function verify_claims($type = LoginID_Operation::Login, $user_id = null)
  {
    // get the stuff
    $jwt_body = $this->validated_jwt_body;
    // final step is to compare the jwt udata say vs what we think is logging in. 
    // as well as only accepting a JWT issued in the last 30s
    if ($jwt_body !== null && time() - intval($jwt_body->iat) < 30) {

      if ($type === LoginID_Operation::Register) {
        // register, just return, we good
        return isset($this->email) && $this->validate_hashed_string($this->email, $jwt_body->udata);
      } else {
        // login, we need more checks
        $loginid_id = get_user_meta($user_id, LoginID_DB_Fields::subject_user_id, true);
        if (isset($loginid_id) && $loginid_id === $jwt_body->sub)
          return true;
      }
    }
    $this->wp_errors->add(LoginID_Errors::LoginTokenInvalidClaim[LoginID_Error::Code], LoginID_Errors::LoginTokenInvalidClaim[LoginID_Error::Message]);
    // this runs, it means verification failed
    return false;
  }

  /**
   * Only run this after JWT in $this->loginid has been validated see validate_loginid()
   * Attaches loginid userid from jwt->body->sub to wordpress user's metadata
   * 
   * Only run this for login
   * 
   * I expect stuff to be sanitized before calling this function
   * $this->validated_jwt_body = Object(body)
   * 
   * @since 0.0.1
   * @param int $user_id, whom to add this metadata to
   * @return bool true if success, false if failed;
   */
  protected function attach_loginid_to($user_id)
  {
    if ($this->validated_jwt_body !== null) {
      return !!update_user_meta($user_id, LoginID_DB_Fields::subject_user_id, $this->validated_jwt_body->sub) && !!update_user_meta($user_id, LoginID_DB_Fields::udata_user_id, $this->validated_jwt_body->udata);
    }
    return false;
  }

  /**
   * Validates loginid then adds required information to existing user meta
   * 
   * @since 0.1.0
   * @return boolean true|false, true for success, false for fail
   */
  public function add_authenticator_to_user()
  {
    $user = get_user_by('email', $this->email);
    if ($user !== false && $this->validate_loginid() && $this->verify_claims(LoginID_Operation::Register)) {
      if (get_user_meta($user->ID, LoginID_DB_Fields::subject_user_id, true) === '' && get_user_meta($user->ID, LoginID_DB_Fields::udata_user_id, true) === '') {
        return $this->attach_loginid_to($user->ID);
      }
    }
    return false;
  }

  /**
   * basically sets the loginID database fields to blank
   * 
   * @return boolean true|false true if successful false if failed.
   */
  public function remove_authenticator_from_user()
  {
    $user = get_user_by('email', $this->email);
    if ($user !== false) {
      return !!update_user_meta($user->ID, LoginID_DB_Fields::subject_user_id, '') && !!update_user_meta($user->ID, LoginID_DB_Fields::udata_user_id, '');
    }
    return false;
  }


  /**
   * logs in the user without a password
   * I expect stuff to be sanitized before calling this function
   * $this->email
   * 
   * @since 0.1.0
   * @return int|WP_Error wordpress user id or a WP_Error object on failure
   */
  protected function login_passwordless()
  {
    $user = get_user_by('email', $this->email);
    if ($user !== false && $this->validate_loginid() && $this->verify_claims(LoginID_Operation::Login, $user->ID)) {
      // successfully obtained userid and jwt is validated
      return $user->ID;
    } else {
      // either user not found or jwt is invalid
      $error = new WP_Error();
      $error->add(LoginID_Errors::LoginIDCannotVerify[LoginID_Error::Code], LoginID_Errors::LoginIDCannotVerify[LoginID_Error::Message]);
      return $error;
    }
  }

  /**
   * registers the user without a password
   * I expect stuff to be sanitized before calling this function
   * $this->email
   * $this->username
   * $this->password, i know this is ironic, but we need this for now
   * 
   * @since 0.1.0
   * @return int|WP_Error wordpress user id or a WP_Error object on failure
   */
  protected function register_passwordless()
  {
    // immediately register the user
    $user_id = $this->register_password();

    if (!is_wp_error($user_id)) {

      if (isset($this->loginid) && isset($this->loginid->{'error'})) {
        $this->redirect_message = 'failure';
      }
      if ($this->validate_loginid() && $this->verify_claims(LoginID_Operation::Register)) {
        if (!is_wp_error($user_id)) {
          // we got a proper user_id here so we need to make sure to attach loginid stuff to the user as metadata
          if (!$this->attach_loginid_to($user_id)) {
            $this->redirect_message = 'failure';
          }
        }
        // $this->redirect_message = 'success';
      } else {
        $this->redirect_message = 'failure';
      }
    }

    return $user_id;
  }

  /**
   * logs in the user with a password
   * I expect stuff to be sanitized before calling this function
   * $this->email
   * $this->password
   * 
   * @since 0.1.0
   * @return int|WP_Error wordpress user id or a WP_Error object on failure
   */
  protected function login_password()
  {
    $user = get_user_by('email', $this->email);

    if ($user === false || !wp_check_password($this->password, $user->user_pass, $user->ID)) {
      // failed to get user or password doesn't match
      $error = new WP_Error();
      $error->add(LoginID_Errors::LoginFailed[LoginID_Error::Code], LoginID_Errors::LoginFailed[LoginID_Error::Message]);
      return $error;
    } else {
      // successfully obtained userid and user password matches
      return $user->ID;
    }
  }

  /**
   * registers the user with a password
   * I expect stuff to be sanitized before calling this function
   * $this->email
   * $this->username
   * $this->password
   * 
   * @since 0.1.0
   * @return int|WP_Error wordpress user id or a WP_Error object on failure
   */
  protected function register_password()
  {
    return $this->register(array(
      'user_login'    =>   $this->username,
      'user_email'    =>   $this->email,
      'user_pass'     =>   $this->password,
    ));
  }

  /**
   * interfaces with wordpress to create user object
   * 
   * @since 0.1.0
   * @param array $userdata array containing userdata that could be processed by wp_insert_user();
   * @return int|WP_Error wordpress user id or a WP_Error object on failure
   */
  protected function register($userdata)
  {
    $user_id = wp_insert_user($userdata); // create user
    return $user_id;
  }

  /**
   * triggers register_password() register_passwordless() login_password() login_passwordless() 
   * depending on the login mode, and then process result
   * 
   * @since 0.1.0
   * @param string $login_type LoginID_Operation::Login or LoginID_Operation::Register
   * @param string $strategy LoginID_Strategy::Password or LoginID_Strategy::Passwordless
   * @return int|WP_Error wordpress user id or a WP_Error object on failure
   */
  protected function authenticate($login_type, $strategy)
  {

    $result = $this->{"{$login_type}_{$strategy}"}(); // result could be a userid if successful or a WP_Error if failed

    // check if error has happened
    if (is_wp_error($result)) {
      // error happened, bad
      $this->wp_errors = $this->wp_error_merge($this->wp_errors, $result);
    } else {
      // no error; time to set user up to be redirected later;
      $this->user_id = $result;
    }
  }


  /**
   * this function is basically the "real" constructor, obtains data from post
   * this is also a big input validation method when it triggers
   * 
   * @since 0.1.0
   */
  public function on_init()
  {
    // check for the 2 keys which is unique to this plugin's forms (also check to make sure user isn't logged in)
    if (wp_verify_nonce(isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '', 'loginid_dw_auth_field') && isset($_POST['submit']) && isset($_POST['shortcode']) && !is_user_logged_in()) {
      // this method gotta be efficient, cuz its loaded on every page, avoid doing unnecessary work
      $submit = sanitize_text_field(wp_unslash($_POST['submit'])); // immediately sanitized
      $shortcode = sanitize_text_field(wp_unslash($_POST['shortcode'])); // immediately sanitized
      $login_type = $this->validate_loginid_field($shortcode); // validate input

      if ($login_type !== false) {
        $this->email = sanitize_email(wp_unslash(isset($_POST['email']) ? $_POST['email'] : ''));
        $this->username = sanitize_text_field(wp_unslash(isset($_POST['username']) ? $_POST['username'] : ''));
        $this->optin = sanitize_text_field(wp_unslash(isset($_POST['opt-in']) ? $_POST['opt-in'] : ''));

        // we have login type as register or login
        if ($submit === $login_type) {
          // the submit should return the correct submission type or else something went wrong on the front end;
          // never trust the front end xd

          $this->current_auth_type = $login_type;

          // from this point on we are good to go
          $this->password = isset($_POST['password']) ? esc_attr(sanitize_text_field(wp_unslash($_POST['password']))) : null;

          // this generates errors if input is invalid, also merges them into this->wp_error
          $this->wp_errors = $this->wp_error_merge($this->wp_errors, $this->validate_email($login_type), $login_type === LoginID_Operation::Register ? $this->validate_username() : null);
          // check for errors

          if ($login_type === LoginID_Operation::Register) {
            // validate password too for registration now, because it is now required for registration purposes
            $this->wp_errors = $this->wp_error_merge($this->wp_errors, $this->validate_password($this->password));
          }

          if ($this->contains_no_errors()) {
            // this means that username and email validation just passed
            $fido2_support = isset($_POST['fido2']) ? sanitize_text_field(wp_unslash($_POST['fido2'])) : null;
            $loginid_data = isset($_POST['loginid']) ? sanitize_text_field(wp_unslash($_POST['loginid'])) : null;

            // now we need to figure out if we doing loginid login, password login or awaiting loginid direct web.
            if (isset($loginid_data)) {
              // do loginid login
              $loginid = json_decode(stripslashes($loginid_data)); // strip slashes is important :/
              $this->loginid = $loginid;

              if (isset($loginid->{'error'}) && $login_type === LoginID_Operation::Login) {
                $this->handle_loginid_errors($login_type, $loginid->error);
              } else {
                // create user then log them in
                $this->authenticate($login_type, LoginID_Strategy::Passwordless);
              }
            } else if (isset($this->password) && $this->optin !== 'true') {
              // do password login
              $this->wp_errors = $this->wp_error_merge($this->wp_errors, $this->validate_password($login_type));
              if ($this->contains_no_errors()) {
                // create user then log them in
                $this->authenticate($login_type, LoginID_Strategy::Password);
              }
            } else if ($fido2_support === LoginID_FIDO2::Supported) {
              // for login specific flow we need to check for user meta
              if ($login_type === LoginID_Operation::Login) {

                $user = get_user_by('email', $this->email);
                $result = '';
                if ($user !== false) {
                  $result = get_user_meta($user->ID, LoginID_DB_Fields::udata_user_id, true);
                }
                if ($result !== '') {
                  // this means that a user fido2 meta exists
                  $this->login_user_udata = $result;
                  $this->release_the_fido = true;
                } else {
                  // user doesn't have fido 2 meta password user, show password
                  $this->manually_display_password = true;
                }
              } else {
                // register, default to fido2 if user opted in
                if ($this->optin === 'true') {
                  $this->release_the_fido = true;
                }
              }
            }
            // fido 2 is not supported in this case but they have opt in.
            else if ($login_type === LoginID_Operation::Register && $this->optin === 'true') {
              $this->authenticate(LoginID_Operation::Register, LoginID_Strategy::Password);
              $this->redirect_message = 'unsupported';
            } else {
              $this->wp_errors->add(LoginID_Errors::CriticalError[LoginID_Error::Code], LoginID_Errors::VersionMismatch[LoginID_Error::Message]);
            }
          } else {
            // do nothing, display errors during render
          }
        } else if ($submit === LoginID_Operation::Next) {
          // we assume in here that the user doesn't have javascript, because front end js should update this field.
          $this->javascript_unsupported = true;
        } else {
          // front end and backend is out of sync
          $this->wp_errors->add(LoginID_Errors::VersionMismatch[LoginID_Error::Code], LoginID_Errors::VersionMismatch[LoginID_Error::Message]);
        }
      } else {
        // front end and backend is out of sync
        $this->wp_errors->add(LoginID_Errors::VersionMismatch[LoginID_Error::Code], LoginID_Errors::VersionMismatch[LoginID_Error::Message]);
      }
    }
  }

  /**
   * this method handles errors and toggles certain flags depending on the error
   * @since 0.1.0
   * @param string $login_type LoginID_Operation::Login or LoginID_Operation::Register
   * @param Object $error, error object from the frontend it should contain the following structure {name: string, code: string|undefined, message: string}
   */
  protected function handle_loginid_errors($login_type, $error)
  {
    $user_not_found = 'user_not_found';
    $syntax_error = 'SyntaxError';
    if ($login_type === LoginID_Operation::Login && isset($error->code) && $error->code === $user_not_found) {
      // in the case of login and loginid api returned user not found, it means they didn't register with loginid
      $this->manually_display_password = true;
    } else if ($error->name === $syntax_error) {
      $this->wp_errors->add(LoginID_Errors::PluginError[LoginID_Error::Code], LoginID_Errors::PluginError[LoginID_Error::Message]);
    } else {
      $this->wp_errors->add($error->name,  isset($error->code) ? 'LOGINID_SERVER_ERROR::' . $error->code : $error->message . '; Please try biometrics authentication again Or use a password instead');
    }
  }


  /**
   * handles redirects if applicable
   * 
   * @since 0.1.0
   */
  public function redirect_if_applicable()
  {
    if (isset($this->user_id)) {
      $this->login_user($this->user_id); // login and redirects, then exit();
    }
  }

  /**
   * Generates the unique id to be sent to loginid direct web in the udata field
   * Im technically using the password storage method BCRYPT to generate a hashed string that I could validate later internally
   * but at the same time LoginID could not use it to identify it to a given user
   * the string is then base64_encoded and then reversed for further obfuscation. making it impossible to get useful information out of this.
   * 
   * @since 0.1.0
   * @param string $input the string input used to generate the udata
   * @return string encoded hashed string 
   */
  public function generate_hashed_string(string $input)
  {
    $result = '';
    do {

      $salted = password_hash($input, PASSWORD_BCRYPT, array('cost' => 4));
      $substr = substr($salted, 7);
      $result = str_replace('/', '-', $substr);
      // keep going until first and last characters are not . or -
    } while ($result[strlen($result) - 1] === '.' || $result[strlen($result) - 1] === '-' || $result[0] === '.' || $result[0] === '-');
    return $result;
  }
  /**
   * Validates the string created 
   * 
   * @since 0.1.0
   * @param string $input the string input used to generate the udata in generate_hashed_string(), same as that
   * @param string $encoded_hashed_string whatever generate_hashed_string() spitted out as the return value gets put here.
   * @return bool true if the $encoded_hashed_string is generated using the input, false if it isn't generated using the input. 
   */
  protected function validate_hashed_string(string $input, string $encoded_hashed_string)
  {
    $decoded = str_replace('-', '/', $encoded_hashed_string);
    $fullstr = '$2y$04$' . $decoded;
    return password_verify($input, $fullstr);
  }

  /**
   * generates custom form
   * this form reacts to what is being posted and auto fills the input
   * 
   * @since 0.1.0
   * @param string $type, basically 'login' or 'register'
   */
  protected function render_form($type = LoginID_Operation::Login, $attrs = [], $tag = '')
  {
    // normalize attribute keys, lowercase
    $attrs = array_change_key_case((array) $attrs, CASE_LOWER);

    $parsed_attrs = shortcode_atts(
      array(
        'hidden' => "false",
      ),
      $attrs,
      $tag
    )

?>
    <form id="<?php echo esc_attr("__loginid_{$type}_form") ?>" method="POST" class="loginid-auth-form <?php echo $type === LoginID_Operation::Register ? 'register' : 'login' ?> <?php echo isset($this->email) ? 'active' : ''?>" <?php echo $parsed_attrs['hidden'] === 'true' ? 'hidden="true"' : '' ?>>
      <div class="loginid-auth-form-row">
        <label class="loginid-auth-form-label" for="email">Email <strong>*</strong></label>
        <input class="input-text loginid-auth-form-input" id="__loginid_input_email_<?php echo esc_attr($type) ?>" type="text" name="email" value="<?php echo esc_attr($this->email) ?>">
      </div>
      <?php
      if ($type === LoginID_Operation::Register) {
      ?>
        <div class="loginid-auth-form-row">
          <label class="loginid-auth-form-label" for="username">Username <strong>*</strong></label>
          <input class="input-text loginid-auth-form-input" id="__loginid_input_username_<?php echo esc_attr($type) ?>" type="text" name="username" value="<?php echo esc_attr($this->username) ?>">
        </div>
      <?php
      }
      if ($type === LoginID_Operation::Login && (!$this->manually_display_password) && (!$this->javascript_unsupported) && empty($this->password)) {
        ?>
          <div id="__loginid_login-passwordless-message" class="loginid-auth-form-row">
            <p>Please input email associated with your passwordless account.</p>
          </div>
        <?php
        }
      ?>
      <div id="__loginid_password_div" <?php echo ((!$this->manually_display_password) && (!$this->javascript_unsupported) && empty($this->password) && ($type === LoginID_Operation::Login) ? 'class="__loginid_hide-password loginid-auth-form-row"' : 'class="loginid-auth-form-row"') ?>>
        <label class="loginid-auth-form-label" for="password">Password <strong>*</strong></label>
        <input class="input-text loginid-auth-form-input" id="__loginid_input_password_<?php echo esc_attr($type) ?>" type="password" name="password" value="<?php echo esc_attr($this->password) ?>">
      </div>
      <?php
      if ($type === LoginID_Operation::Register) {
      ?>
        <div id="__loginid_register-passwordless-opt-in-div" class="__loginid_register-passwordless-opt-in-div" style="display: flex; align-items: center;">
          <label for="__loginid_register-passwordless-opt-in" style=" display: flex; align-items: center; gap:5px; margin: 5px; cursor: pointer;">
            <div style="width: 30px; height: 38px; display: inline-block; margin-right: 10px;">
              <svg width="100%" height="100%" viewBox="0 0 99 125" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="
                  fill-rule: evenodd;
                  clip-rule: evenodd;
                  stroke-linejoin: round;
                  stroke-miterlimit: 2;
                ">
                <path d="M32.196,32.615c-4.531,4.716 -7.12,10.866 -7.259,17.338c0,0.648 0.232,1.295 0.694,1.804c0.462,0.462 1.063,0.739 1.711,0.739c1.387,0 2.496,-1.109 2.543,-2.45c0.231,-10.727 9.062,-19.835 19.326,-19.835c7.166,0 13.963,4.439 17.292,11.328c0.277,0.601 0.786,1.017 1.387,1.248c0.647,0.231 1.294,0.185 1.895,-0.139c1.249,-0.601 1.757,-2.08 1.156,-3.329c-4.161,-8.553 -12.714,-14.101 -21.73,-14.101c-6.381,-0 -12.391,2.635 -17.015,7.397Z" style="fill: currentColor; fill-rule: nonzero" />
                <path d="M68.549,52.667l0,22.296c0,3.643 -1.02,7.237 -2.914,10.346l-0.097,0.097c-3.546,5.683 -9.666,9.083 -16.321,9.083c-10.686,0 -19.332,-8.792 -19.332,-19.526l-0,-12.387c-0,-1.36 -1.117,-2.477 -2.477,-2.477c-1.36,0 -2.478,1.117 -2.478,2.477l0,12.387c0,13.503 10.881,24.529 24.287,24.529c8.403,0 16.078,-4.323 20.595,-11.512c2.429,-3.886 3.692,-8.403 3.692,-13.017l-0,-22.296c-0,-1.408 -1.117,-2.525 -2.478,-2.525c-1.36,-0 -2.477,1.117 -2.477,2.525Z" style="fill: currentColor; fill-rule: nonzero" />
                <path d="M13.959,43.321c-0.543,0.408 -0.951,1.019 -1.019,1.63c-0.339,1.969 -0.475,4.006 -0.475,5.975l0,24.036c0,20.438 16.5,37.141 36.733,37.141c12.426,-0 23.969,-6.315 30.759,-16.771c0.746,-1.155 0.407,-2.716 -0.679,-3.463c-0.544,-0.408 -1.223,-0.543 -1.902,-0.408c-0.611,0.136 -1.154,0.544 -1.561,1.087c-5.907,9.098 -15.821,14.53 -26.617,14.53c-17.517,0 -31.776,-14.462 -31.776,-32.116l-0,-24.036c-0,-1.698 0.136,-3.463 0.407,-5.16c0.204,-1.358 -0.747,-2.648 -2.105,-2.852c-0.068,-0.068 -0.203,-0.068 -0.339,-0.068c-0.543,-0 -1.019,0.204 -1.426,0.475Z" style="fill: currentColor; fill-rule: nonzero" />
                <path d="M18.701,29.857c-0.744,1.151 -0.406,2.707 0.745,3.452c0.541,0.406 1.15,0.473 1.827,0.338c0.677,-0.135 1.218,-0.474 1.557,-1.083c6.023,-9.339 15.903,-14.956 26.393,-14.956c17.258,0.068 31.808,15.295 31.808,33.297l0,24.025c0,1.488 -0.135,3.045 -0.271,4.466c-0.203,1.421 0.745,2.64 2.098,2.843c1.286,0.203 2.572,-0.812 2.775,-2.098c0.203,-1.692 0.338,-3.452 0.338,-5.211l0,-24.025c0,-9.814 -3.925,-19.559 -10.692,-26.733c-6.971,-7.444 -16.243,-11.505 -25.988,-11.572c-12.249,-0 -23.619,6.429 -30.59,17.257Z" style="fill: currentColor; fill-rule: nonzero" />
                <path d="M14.417,15.398c-9.219,9.709 -14.417,22.753 -14.417,35.896l0,15.986c0,1.373 1.079,2.55 2.452,2.55c1.373,-0 2.452,-1.177 2.452,-2.55l-0,-15.986c-0,-25.01 20.301,-46.292 44.33,-46.292l0.098,-0c21.773,0.098 40.996,17.948 43.84,40.701c0.098,0.687 0.392,1.275 0.883,1.668c0.588,0.392 1.176,0.588 1.863,0.49c1.373,-0.196 2.354,-1.373 2.158,-2.746c-1.472,-12.064 -7.258,-23.342 -16.085,-31.777c-9.121,-8.532 -20.694,-13.338 -32.659,-13.338c-13.142,0 -25.598,5.492 -34.915,15.398Z" style="fill: currentColor; fill-rule: nonzero" />
                <path d="M93.53,57.965l-0,16.986c-0,24.646 -19.933,44.775 -44.284,44.775c-22.78,-0.098 -41.73,-17.281 -44.087,-40.061c-0.098,-0.688 -0.393,-1.277 -0.884,-1.768c-0.491,-0.294 -0.982,-0.491 -1.571,-0.491l-0.196,0c-0.687,0.098 -1.277,0.393 -1.669,0.884c-0.491,0.491 -0.688,1.178 -0.589,1.865c1.276,12.176 6.873,23.37 15.906,31.618c9.034,8.346 20.816,12.862 32.992,12.862c27.198,0 49.291,-22.289 49.291,-49.684l0,-16.986c0,-1.375 -1.08,-2.455 -2.455,-2.455c-1.374,-0 -2.454,1.08 -2.454,2.455Z" style="fill: currentColor; fill-rule: nonzero" />
                <path d="M53.346,64.354l-0.1,0.1c0,-0 -0.621,0.48 -0.627,1.345c-0.003,0.865 0.011,6.252 0.011,6.252l-7.265,-0c-0,-0 0.008,-5.387 0.003,-6.252c-0.003,-0.865 -0.627,-1.345 -0.627,-1.345l-0.097,-0.1c-1.448,-1.312 -2.249,-3.121 -2.249,-5.019c0,-3.674 2.959,-6.663 6.6,-6.669c3.641,0.006 6.599,2.995 6.599,6.669c0,1.898 -0.801,3.707 -2.248,5.019m-4.346,-16.495l-0.011,0c-6.254,0.009 -11.337,5.155 -11.337,11.476c0,2.793 1.008,5.486 2.837,7.591l0.152,0.174l0,7.354c0,1.326 1.066,2.403 2.376,2.403l11.956,0c1.309,0 2.373,-1.077 2.373,-2.403l-0,-7.354l0.152,-0.174c1.831,-2.105 2.84,-4.798 2.84,-7.591c-0,-6.321 -5.086,-11.467 -11.338,-11.476" style="fill: currentColor; fill-rule: nonzero" />
              </svg>
            </div>
            <div>
              Opt in for passwordless authentication. <br />
              When enabled, you can login using your biometrics instead of password.
            </div>
            <input class="toggle-checkbox" name="optin" type="checkbox" style="display: none;" id="__loginid_register-passwordless-opt-in" checked="<?php echo esc_attr($this->optin) ?>">
            <div class="toggle-switch" style="flex-shrink: 0;"></div>
          </label>
        </div>
      <?php
      }
      ?>
      <div class="loginid-submit-row">
        <input class="loginid-auth-form-submit" type="submit" name="submit" value="<?php echo esc_attr($this->javascript_unsupported ? $type : LoginID_Operation::Next) ?>" id="__loginid_submit_button_<?php echo esc_attr($type) ?>" />
        <?php
        if ($type === LoginID_Operation::Login) {
        ?>
          <a class="loginid-auth-form-link" href="#" style="display: none;white-space: nowrap;" id="__loginid_use_password_instead">use a password instead</a>
        <?php
        }
        ?>
      </div>
      <input type="hidden" readonly name="shortcode" id="__loginid_input_shortcode_<?php echo esc_attr($type) ?>" value="<?php echo esc_attr(LoginID_DirectWeb::ShortCodes[$type]) ?>">
      <input type="hidden" readonly name="_wpnonce" id="__loginid_input_nonce_<?php echo esc_attr($type) ?>" value="<?php echo esc_attr(wp_create_nonce('loginid_dw_auth_field')) ?>">
      <?php if ($this->release_the_fido && isset($this->email) && $type === $this->current_auth_type) {
        $settings = loginid_dw_get_settings();
      ?>
        <input type="hidden" disabled name="udata" id="__loginid_input_udata_<?php echo esc_attr($type) ?>" value="<?php echo esc_attr($type === LoginID_Operation::Login ? $this->login_user_udata : $this->generate_hashed_string($this->email)) ?>">
        <input type="hidden" disabled name="baseurl" id="__loginid_input_baseurl_<?php echo esc_attr($type) ?>" value="<?php echo esc_attr($settings['base_url']) ?>">
        <input type="hidden" disabled name="apikey" id="__loginid_input_apikey_<?php echo esc_attr($type) ?>" value="<?php echo esc_attr($settings['api_key']) ?>">
      <?php }
      ?>
    </form>
    <?php
  }

  /**
   * Outputs WPError Objects
   * 
   * @since 0.1.0
   * @return boolean true if contains error; false if no errors; and null if input is not a wordpress error object
   */
  protected function output_wp_errors()
  {
    if (is_wp_error($this->wp_errors)) {

      $contains_error = false;
      foreach ($this->wp_errors->get_error_messages() as $error) {
        $contains_error = true;
        $this->render_error($error);
      }
      return $contains_error;
    }
    return null;
  }

  /**
   * Outputs error text, 
   * this is a pure function with no side effects 
   * 
   * @since 0.1.0
   */
  protected function render_error($error)
  {
    echo '<div class="__loginid-error-style">';
    echo esc_html($error);
    echo '</div>';
  }


  /**
   * renders the status banner if required
   * 
   * @since 1.0.12
   */
  public function render_banner()
  {
    $bannerStatus = sanitize_text_field(isset($_REQUEST['__loginid_status']) ? wp_unslash($_REQUEST['__loginid_status']) : '');
    $definitions = array(
      'success' => array(
        'msg' => 'Successfully created account with biometrics enabled',
        'color' => '#155724', 'bgc' => '#d4edda'
      ),
      'failure' => array(
        'msg' => 'Successfully created account, however, biometrics verification failed. Biometrics will not be enabled. You can trying again using your user profile settings.',
        'color' => '#721c24', 'bgc' => '#f8d7da'
      ),
      'unsupported' => array(
        'msg' => 'Successfully created account, however, your browser or device does not support FIDO2 authentication. You can try again using the user profile settings on another device. Or check out a list of supported devices here.',
        'color' => '#721c24', 'bgc' => '#f8d7da'
      )
    );

    if (array_key_exists($bannerStatus, $definitions)) {
    ?>
      <div id="__loginid_notification_header" style="position: absolute; top: 0; left: 0; right: 0; padding: 20px; z-index: 99999; display: flex; align-items: center; gap: 20px; <?php echo esc_html('background-color: ' . $definitions[$bannerStatus]['bgc'] . '; color: ' . $definitions[$bannerStatus]['color'] . ';') ?>">
        <div style="flex-grow: 1">
          <?php echo esc_html($definitions[$bannerStatus]['msg']) ?>
        </div>
        <button id="__loginid_notification_close" style="flex-shrink: 0;">close</button>
        <script>
          document.getElementById("__loginid_notification_close").addEventListener('click', () => {
            document.getElementById("__loginid_notification_header").style.display = 'None'
          })
        </script>
      </div>
<?php
    }
  }


  /**
   * renders the form depending on the data in the object
   * 
   * @since 0.1.0
   * @param string $type, basically 'login' or 'register'
   */
  public function render($type = LoginID_Operation::Login, $attrs = [], $tag = '')
  {
    // don't render if user is logged in (except for in previews)
    if (!is_user_logged_in() || is_preview()) {
      // make sure to only output error in the correct section, in case both login and register is in the same page
      if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shortcode']) && wp_verify_nonce(isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '', 'loginid_dw_auth_field')) {
        $shortcode = sanitize_text_field(wp_unslash($_POST['shortcode'])); // immediately sanitized
        $login_type = $this->validate_loginid_field($shortcode); // validate input
        if ($login_type !== false && $type === $login_type) {
          $this->output_wp_errors();
        }
      }

      // make sure to only output error in the correct section, in case both login and register is in the same page
      $this->render_form($type, $attrs, $tag);
    }
  }

  /**
   * The callback function that will replace [shortcode] for registration
   * 
   * @since 0.1.0
   */
  public function registration_shortcode($attrs = [], $tag = '')
  {
    ob_start();
    $this->render(LoginID_Operation::Register, $attrs, $tag);
    return ob_get_clean();
  }

  /**
   * The callback function that will replace [shortcode] for login
   * 
   * @since 0.1.0
   */
  public function login_shortcode($attrs = [], $tag = '')
  {
    ob_start();
    $this->render(LoginID_Operation::Login, $attrs, $tag); // defaults to login, but we set this for consistency
    return ob_get_clean();
  }

  /**
   * The callback function that will replace [shortcode] for settings
   * 
   */
  public function settings_shortcode()
  {
    ob_start();
    if (is_user_logged_in() || is_preview()) {
      $user = wp_get_current_user();
      loginid_dw_attach_to_profile($user);
    }

    return ob_get_clean();
  }

  /**
   * Merge multiple WP_Error objects together
   * credit: https://gist.github.com/wpscholar/9004667
   * this is a pure function with no side effects
   *
   * @since 0.1.0 
   * @param add as many WP_Error objects to be merged as you would like
   * @return WP_Error merged results of the errors
   */
  protected function wp_error_merge()
  {
    $wp_error_merged = new WP_Error();
    $wp_errors = func_get_args();
    foreach ($wp_errors as $wp_error) {
      if (!is_wp_error($wp_error)) {
        continue;
      }
      /**
       * @var WP_Error $wp_error
       */
      $error_codes = $wp_error->get_error_codes();
      foreach ($error_codes as $error_code) {
        // Merge error messages
        $error_messages = $wp_error->get_error_messages($error_code);
        foreach ($error_messages as $error_message) {
          $wp_error_merged->add($error_code, $error_message);
        }
        // Merge error data
        $error_data = $wp_error->get_error_data($error_code);
        if ($error_data) {
          $prev_error_data = $wp_error_merged->get_error_data($error_code);
          if (!empty($prev_error_data) && is_array($error_data) && is_array($prev_error_data)) {
            $wp_error_merged->add_data(array_merge($prev_error_data, $error_data), $error_code);
          } else {
            $wp_error_merged->add_data($error_data, $error_code);
          }
        }
      }
    }
    return $wp_error_merged;
  }

  /**
   * is this->wp_error empty
   * 
   * @since 0.1.0
   * @return boolean true if its empty, false if its not empty
   */
  function contains_no_errors()
  {
    return count($this->wp_errors->get_error_codes()) === 0;
  }

  /**
   * is this->wp_error has at least one error in it
   * 
   * @since 0.1.0
   * @return boolean false if its empty, true if its not empty
   */
  function contains_errors()
  {
    return !$this->contains_no_errors;
  }

  /** 
   * give user cookie to log them in, then redirect
   * 
   * @since 0.1.0
   */
  protected function login_user($user_id)
  {
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    $this->redirect();
  }

  /** 
   * redirects user
   * 
   * @since 1.0.15
   */
  protected function redirect()
  {
    $referer = wp_get_referer();
    if ($referer === false) {
      $base_url = home_url() . wp_unslash($_SERVER['REQUEST_URI']);
    } else {
      $base_url = $referer;
    }

    $redirect_message = $this->redirect_message !== '' ? ('?__loginid_status=' . $this->redirect_message) : '';

    wp_safe_redirect($base_url . $redirect_message);
    exit();
  }
}
