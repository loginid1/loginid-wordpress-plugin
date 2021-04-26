=== loginid-directweb ===
Contributors: loginidauth
Donate link: https://loginid.io
Tags: Authentication, Security, Oauth-2.0, Identity, Biometrics, Touch-id, Webauthn, Passwordless, Fido2, Fido, Passwordless login, Fido certified, Uaf
Requires at least: 5.4
Tested up to: 5.7
Stable tag: 1.0.11
Requires PHP: 7.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

FIDO-certified strong authentication in 5 clicks.  Go passwordless and eliminate account takeovers and fraud.

== Description ==

Getting started with LoginID’s innovative and highly secure FIDO <link or bubble popup to blog> based authentication technology is easy! You and your users will benefit immensely from this as they won’t have any passwords to remember and you won’t have to worry about storing them either!

Even if you still wish to keep passwords and captcha methods active, LoginID makes a fantastic addition by providing one extra layer as part of a holistic Multi-factor authentication process.   

*Did you know:* FIDO does not send login information through the internet which helps prevent many vulnerabilities such as credential phishing and “Man in The Middle” attacks.  Nor does your biometric leave your device and become stored in the cloud somewhere. Your biometric information is safe - it is not stored on any servers or in the cloud, thereby making it secure.

Benefits:
- Convenience: 
  - Eliminate the need for your users to enter a password when they log in to your website.
  - Reduce your user abandonment rates by making transactions as seamless as possible.
- Compliance: 
  - Become GDPR and PSD2 compliant with a FIDO-certified strong authentication solution
- Security:
  - Boost your website’s security through the use of multifactor authentication by replacing vulnerable static username/password credentials with strong public/private key credentials.  

For more information on how the DirectWeb plugin works, please view our [documentation](https://docs.loginid.io/websdks/dw).

== Installation ==

LoginID has taken the complexity out of the FIDO integration process and we can have you up and running in under 5 minutes with our easy to follow steps below.

Let’s get started!

### Step 1: Prerequisites

While logged into your WordPress (/wp-admin) account, browse to your dashboard and scroll down to the bottom right to ensure that your wordpress version is version 5.4 or later.

*Tip: We always recommend that you run the latest versions of WordPress and our LoginID plugin as part of a good security posture.*

### Step 2: Plugin Installation

We currently support installing the plugin through the WordPress Marketplace or manually uploading it if this is not available to you or you prefer to inspect the package first.

#### Marketplace Installation Method

Navigate to the Plug-in Marketplace on your Wordpress Admin Site

Search for our plugin named loginid-directweb 

*Security Tip: Please be careful when installing plugins from the marketplace. Always verify the name thoroughly to make sure you are indeed installing the correct plugin! Our plugin is spelled EXACTLY loginid-directweb*

Click Install and you are ready for configuration in Step 3! 

*Note: We want to make this as easy and seamless for you as possible so we have embedded the instructions in Step 3 and Step 4 below on the plugin settings page as well. You can continue to follow along here or you can move to the plugin settings page to complete the remainder of your setup. *

#### Manual Installation Method 

Download the plugin here: [https://github.com/loginid1/loginid-directweb](https://github.com/loginid1/loginid-directweb)

Navigate to the Plugins Page on your Wordpress admin site

Click the "Add New" button at the top left of the page, and then click Upload Plugin. You can upload the zip file containing the appropriate plugin.

### Step 3: LoginID Account Registration

In order for us to handle / broker the FIDO authentication for you we need you (the developer / integrator) to have an account on our system so that we have a secure means to receive the requests.

#### Setup Wizard Method

Navigate to the settings page of the plugin in the settings menu (/wp-admin/options-general.php?page=loginid-directweb) and perform the following 5 steps: 

1. Click "Run Express Setup Wizard" button and click the "Run wizard" button inside the dropdown. This will redirect you to loginID's dashboard and pre populate your information.
2. Create a LoginID account or login
3. You will land on the integrations page. Accept our Customer License Agreement, and scroll down to validate that your site information is correct.
4. Click the "Create" button. This will present you with a BaseURL and an API key that is unique to your integration.
5. Click the "Export to my Wordpress site: https://yoursite.com" button. Make sure that the URL matches to your website completely.

You will now be able to use the login or register form shortcodes on your wordpress site. 

#### Do It Yourself Method

This method will collect less data, but requires you to manually copy and paste over the BaseURL and API Key from LoginID.

1. Configure the plugin under Settings > LoginID DirectWeb 
2. Visit [LoginID Dashboard](https://usw1.loginid.io/integrations) on the integrations Page, and click "Add Integration"
3. Accept our Customer License Agreement.
4. Select DirectWeb on step 2 of the Integration creation process. 
5. Input the correct information about your website. Ensure that you check the box with the label "This integration is for LoginID Directweb Wordpress plugin"
6. Copy and Paste the BaseURL and API key from the loginID dashboard to the plugin.

You will now be able to use the login or register form shortcodes on your wordpress site. 

== Frequently Asked Questions ==

= How do I set up the plugin? =

An answer to that question.

Once you install the plugin, click on ‘Settings’. Here, you have two setup options:

*Let LoginID do the work for you (estimated time: 15s to 30s)*
1. Login to your WordPress developer dashboard
1. Install the LoginID DirectWeb plugin
1. Let LoginID automatically set up the plugin on your dashboard via the Setup Wizard. [Insert screenshot]
1. On your dashboard, you will need to fill in the application name and your Callback URL. [Insert screenshot]
1. Click on ‘Export to my Wordpress site’  [Insert screenshot]
1. On your WordPress setting page, click on Save Settings (All configuration variables will be pre-filled for you)
1. Generate your registration or login pages by clicking the corresponding links, or use our shortcode to embed the code into any of your WordPress pages.

*Do it yourself (DIY) (estimated time: 1min to 2mins)*
1. Login to your WordPress developer dashboard
1. Install the LoginID DirectWeb plugin
1. On a new tab, register your developer account on the LoginID dashboard. [Insert screenshot]
1. Click on the Integration tab
1. Agree to our Customer License Agreement
1. Choose the DirectWeb integration option and follow the instruction on screen
1. Copy your credentials or use ‘export to my Wordpress site’ to populate your configuration settings on the plugin
1. On your WordPress setting page, click on Save Settings (All configuration variables will be pre-filled for you)
1. Generate your registration or login pages by clicking the corresponding links, or use our shortcode to embed the code into any of your WordPress pages.

= How does the plugin work? =
When a user tries to sign in to your website, they will be prompted to register for an account on your website. Once the user completes the registration process, they will be able to sign in using strong FIDO-certified authentication versus using traditional passwords.

= Can I customize the registration page? =
Yes, you can customize the registration page in line with your website’s look and feel. You will be able to paste in LoginID registration and login shortcodes as per your UX needs on any of your UX journey for your end users.

= When I install the plugin, will my existing users be able to login? =
Yes. Our solution augments your existing authentication flows. You may choose to replace or add LoginID authentication based on your specific needs. 

= Can I integrate this plugin with my other installed plugins such as my eCommerce plugin? =
We can assist you with integrating the LoginID DirectWeb with your other installed plugins. Please email us at [email] or contact us via the Live Chat option on [loginid.io](https://loginid.io). 

= I need to customize the plugin or I need support and help? =
Please email us at [email] or contact us via the Live Chat option on [loginid.io](https://loginid.io). 

= Where can I report bugs or leave feedback? =
Please email us at [email] or contact us via the Live Chat option on [loginid.io](https://loginid.io). 

= I have other queries or need additional support. =
For any other queries or if you need additional support, please email us at [email] or contact us via the Live Chat option on [loginid.io](https://loginid.io). 

= Error: Your identity token could not be verified =
This error means that the plugin is unable to verify the validity of the login or register claim. This can often be resulted from a bad baseURL and api key combination. Please double check your Base URL and API Key parameters in the plugin settings against your credentials on the LoginID dashboard.

== Screenshots ==

Coming soon.

== Changelog ==

= 1.0.11 =
Changed the text to make things more apparent, better UX 

= 1.0.10 =
Added a self help section within the plugin, to make setup more clear.
The readme.md file still exists if the user wants to read that instead.

= 1.0.9 =
Changed how the setup wizard works under the hood.

= 1.0.8 =
Add TLS warning on production sites. This plugin only works on HTTPS enabled sites
Added plugin Action links to settings as well as external link to docs and support
Support login and register forms on the same page
Added loginid_settings so the site could display passwordless configurations on custom settings pages
Cleaned up a few bugs, and dependency vulnerabilities

= 1.0.7 =
Changed some wording options.

= 1.0.6 =
Fixed use password instead not working.
Added 'Login with FIDO2' and 'Register with FIDO2' buttons.

= 1.0.5 =
Changed the JWT origin to the same as baseURL for better consistency

= 1.0.4 =
Minor Style changes and also bump version in plugin directory to display properly

= 1.0.3 =
remove an unused function that might result in naming conflicts with wordpress naming conventions

= 1.0.2 =
fix password sanitation issue

= 1.0.1 =
Fixes to make this plugin wordpress compliant

= 1.0.0 =
Initial Version. 
Features: 
- Biometric login and register
- Add biometrics to existing user accounts

== Upgrade Notice == 
We recommend all users upgrading from version 1.0.9 and earlier to rerun the setup wizard.
Version 1.0.10 and later swapped to production environment for better stability.
Users may need to re-create their admin panel accounts again on production environment.