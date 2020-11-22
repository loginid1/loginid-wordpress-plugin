<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;


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
  public const LoginIDError = array(LoginID_Error::Code => "loginid_error", LoginID_Error::Message => "Unable to verify your identity");

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

/**
 * Library class of this plugin
 *
 * @since 0.1.0
 */
class LoginID_DirectWeb
{
  // strings that contains the shortcode values [loginid_registration] and [loginid_login]
  protected const ShortCodes = array(LoginID_Operation::Register => "loginid_registration", LoginID_Operation::Login => "loginid_login");

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
    // add short codes
    add_shortcode(self::ShortCodes[LoginID_Operation::Register], array($self, 'registration_shortcode'));
    add_shortcode(self::ShortCodes[LoginID_Operation::Login], array($self, 'login_shortcode'));
  }

  protected $release_the_fido; //whether or not to release loginid direct web information for directweb login
  protected $email;
  protected $username;
  protected $password;
  protected $wp_errors;
  protected $javascript_unsupported;
  protected $loginid;
  protected $user_id;

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

    $this->loginid = null;
    $this->user_id = null;

    $this->wp_errors = new WP_Error;
    $this->javascript_unsupported = false;
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
   * logs in the user without a password
   * I expect stuff to be sanitized before calling this function
   * $this->email
   * $this->loginid to be parsed into an object already
   * 
   * @since 0.1.0
   * @return bool true if valid; false if invalid
   */
  protected function validate_loginid()
  {
    // TODO: validate claim from loginid api
    return true;
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
    if ($user === false || !($this->validate_loginid())) {
      // either user not found or unable to verify identity
      $error = new WP_Error();
      $error->add(LoginID_Errors::LoginIDError[LoginID_Error::Code], LoginID_Errors::LoginIDError[LoginID_Error::Message]);
      return $error;
    } else {
      // successfully obtained userid and loginid information is validated
      return $user->ID;
    }
  }

  /**
   * registers the user without a password
   * I expect stuff to be sanitized before calling this function
   * $this->email
   * $this->username
   * 
   * @since 0.1.0
   * @return int|WP_Error wordpress user id or a WP_Error object on failure
   */
  protected function register_passwordless()
  {
    if ($this->validate_loginid()) {
      return $this->register(array(
        'user_login'    =>   $this->username,
        'user_email'    =>   $this->email,
        'user_pass'     =>    wp_generate_password($length = 128, $include_standard_special_chars = true), // generate 128 character password with special characters
      ));
    } else {
      // this case loginid is unable to verify your identity
      $error = new WP_Error();
      $error->add(LoginID_Errors::LoginIDError[LoginID_Error::Code], LoginID_Errors::LoginIDError[LoginID_Error::Message]);
      return $error;
    }
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
   * @return int|WP_Error wordpress user id or a WP_Error object on failure
   */
  protected function authenticate($login_type)
  {
    $result = $this->{"{$login_type}_password"}(); // result could be a userid if successful or a WP_Error if failed

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
    if (isset($_POST['submit']) && isset($_POST['shortcode']) && !is_user_logged_in()) {
      // this method gotta be efficient, cuz its loaded on every page, avoid doing unnecessary work
      $submit = sanitize_text_field($_POST['submit']); // immediately sanitized
      $shortcode = sanitize_text_field($_POST['shortcode']); // immediately sanitized
      $login_type = $this->validate_loginid_field($shortcode); // validate input
      if ($login_type !== false) {
        // we have login type as register or login
        if ($submit === $login_type) {
          // the submit should return the correct submission type or else something went wrong on the front end;
          // never trust the front end xd

          // from this point on we are good to go
          $this->email = $_POST['email']; // these are okay we gotta remember to sanitize them later, before writing to db
          $this->username = isset($_POST['username']) ? $_POST['username'] : null; // these are okay we gotta remember to sanitize them later
          $this->password = isset($_POST['password']) ? $_POST['password'] : null; // these are okay we gotta remember to sanitize them later

          $this->wp_errors = $this->wp_error_merge($this->wp_errors, $this->validate_email($login_type), $login_type === LoginID_Operation::Register ? $this->validate_username() : null);
          if ($this->contains_no_errors()) {
            // this means that username and email validation just passed
            $fido2_support = isset($_POST['fido2']) ? sanitize_text_field($_POST['fido2']) : null;
            $loginid_data = isset($_POST['loginid']) ? sanitize_text_field($_POST['loginid']) : null;
            // this is a good place to sanitize email and username
            $this->email = sanitize_email($this->email);
            $this->username = sanitize_user($this->username);

            // now we need to figure out if we doing loginid login, password login or awaiting loginid direct web.
            if (isset($loginid_data)) {
              // do loginid login
              $loginid_data = sanitize_text_field($loginid_data);
              $loginid = json_decode($loginid_data);
              $this->loginid = $loginid;

              if (isset($loginid->{'error'})) {
                $this->wp_errors->add($loginid->{'error'}->{'name'}, $loginid->{'error'}->{'message'});
              } else {
                // create user then log them in
                $this->authenticate($login_type);
              }
            } else if (isset($this->password)) {
              // do password login
              $this->wp_errors = $this->wp_error_merge($this->wp_errors, $this->validate_password($login_type));
              if ($this->contains_no_errors()) {
                $this->password = esc_attr($this->password); // sanitize
                // create user then log them in
                $this->authenticate($login_type);
              }
            } else if ($fido2_support === LoginID_FIDO2::Supported) {
              // this point we still awaiting fido2 data from loginid direct web api backend
              $this->release_the_fido = true;
            } else {
              // something gone really wrong
              $this->wp_errors->add(LoginID_Errors::CriticalError[LoginID_Error::Code], LoginID_Errors::VersionMismatch[LoginID_Error::Message]);
            }
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
   * generates custom form
   * this form reacts to what is being posted and auto fills the input
   * 
   * @since 0.1.0
   * @param string $type, basically 'login' or 'register'
   */
  protected function render_form($type = LoginID_Operation::Login)
  {
?>
    <form id="<?php echo "__loginid_{$type}_form" ?>">
      <div>
        <label for="email">Email <strong>*</strong></label>
        <input id="__loginid_input_email" type="text" name="email" value="<?php echo $this->email ?>">
      </div>
      <?php if ($type === LoginID_Operation::Register) { ?>
        <div>
          <label for="username">Username <strong>*</strong></label>
          <input id="__loginid_input_username" type="text" name="username" value="<?php echo  $this->username ?>">
        </div>
      <?php } ?>
      <div id="__loginid_password_div" <?php echo ((!$this->javascript_unsupported) && empty($this->password) ? 'class="__loginid_hide-password"' : null) ?>>
        <label for="password">Password <strong>*</strong></label>
        <input id="__loginid_input_password" type="password" name="password" value="<?php echo $this->password ?>">
      </div>
      <input type="submit" name="submit" value="<?php echo LoginID_Operation::Next ?>" id="__loginid_submit_button" />
      <input type="hidden" readonly name="shortcode" id="__loginid_input_shortcode" value="<?php echo LoginID_DirectWeb::ShortCodes[$type] ?>">
      <?php if ($this->release_the_fido) {
        $settings = loginid_dwp_get_settings()
      ?>
        <input type="hidden" readonly name="baseurl" id="__loginid_input_baseurl" value="<?php echo $settings['base_url'] ?>">
        <input type="hidden" readonly name="apikey" id="__loginid_input_apikey" value="<?php echo $settings['api_key'] ?>">
      <?php } ?>
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
    echo $error;
    echo '</div>';
  }

  /**
   * debug dump
   * TODO: remove
   */
  private function debug_dump()
  {
    echo var_dump($this->release_the_fido);
    echo ";<br />";
    echo var_dump($this->email);
    echo ";<br />";
    echo var_dump($this->username);
    echo ";<br />";
    echo var_dump($this->password);
    echo ";<br />";
    echo var_dump($this->wp_errors);
    echo ";<br />";
    echo var_dump($this->javascript_unsupported);
    echo ";<br />";
    echo var_dump($this->loginid);
    echo ";<br />";
    echo var_dump($this->user_id);
    echo ";<br />";
  }

  /**
   * renders the form depending on the data in the object
   * 
   * @since 0.1.0
   * @param string $type, basically 'login' or 'register'
   */
  public function render($type = LoginID_Operation::Login)
  {
    // don't render if user is logged in
    if (!is_user_logged_in()) {

      // $this->debug_dump(); // todo: remove
      $this->output_wp_errors();
      $this->render_form($type);
    }
  }

  /**
   * The callback function that will replace [shortcode] for registration
   * 
   * @since 0.1.0
   */
  public function registration_shortcode()
  {
    ob_start();
    $this->render(LoginID_Operation::Register);
    return ob_get_clean();
  }

  /**
   * The callback function that will replace [shortcode] for login
   * 
   * @since 0.1.0
   */
  public function login_shortcode()
  {
    ob_start();
    $this->render(LoginID_Operation::Login); // defaults to login, but we set this for consistency
    return ob_get_clean();
  }

  /**
   * Merge multiple WP_Error objects together
   * credit: https://gist.github.com/wpscholar/9004667
   * this is a pure function with no side effects
   *
   * @params add as many WP_Error objects to be merged as you would like
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
   * @return boolean true if its empty, false if its not empty
   */
  function contains_no_errors()
  {
    return count($this->wp_errors->get_error_codes()) === 0;
  }

  /**
   * is this->wp_error has at least one error in it
   * 
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
    wp_redirect(home_url());
    exit();
  }
}
