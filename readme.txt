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

LoginID’s DirectWeb Plugin enables your WordPress application’s end users to register and authenticate through highly secure public key cryptography instead of a password. 

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

1. Upload to the `/wp-content/plugins/` directory.
1. Activate the plugin.
1. Visit Settings > LoginID DirectWeb to configure this plugin.

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
Not applicable at the moment