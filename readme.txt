=== FIDO-certified Passwordless biometric login ===
Contributors: loginidauth
Donate link: https://loginid.io
Tags: Authentication, OAuth 2.0, Security, Multifactor Authentication, FIDO, FIDO2, PSD2, Biometrics, Strong Customer Authentication, Fraud Prevention
Requires at least: 5.4
Tested up to: 5.7
Stable tag: 1.0.14
Requires PHP: 7.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

FIDO-certified strong authentication in 5 clicks.  Go passwordless and eliminate account takeovers and fraud.

== Description ==

LoginID’s DirectWeb Plugin enables your WordPress application’s end users  to utilize FIDO/FIDO2 certified passwordless authentication, including the ability to provide more sensitive operations such as credential management. By leveraging the end user’s existing device biometrics you can quickly integrate multi-factor authentication into your site. Our service is aligned with PSD2, GDPR, CCPA, and HIPPA.

**Benefits:**
Privacy:
  – Biometric information never leaves your device (based on FIDO/FIDO2 principles), not stored in the cloud.
  - No tracking of customers.
Convenience: 
  - Eliminate the need for your users to enter a password when they log in to your website from their primary device.
  - Reduce your user abandonment rates by making transactions as seamless as possible.
Compliance: 
  - FIDO/FIDO2 is aligned with the GDPR and PSD2 principals around the use of strong authentication.
Security:
  - Boost your website’s security through the use of multifactor authentication with strong public/private key credentials.

For more information on how the DirectWeb plugin works, please view our [documentation](https://docs.loginid.io).

**Tell us how we’re doing.**
Have the plugin in production? Tell us about your site on [marketing@loginid.io](marketing@loginid.io) and we’ll post on our social channels!

== Installation ==

1. Upload to the `/wp-content/plugins/` directory.
2. Activate the plugin.
3. Visit Settings > LoginID DirectWeb to configure this plugin.

== Frequently Asked Questions ==

= How do I set up the plugin? =

An answer to that question.

Once you install the plugin, click on ‘Settings’. Here, you have two setup options:

*Let LoginID do the work for you (estimated time: 15s to 30s)*

1. Login to your WordPress developer dashboard
2. Install the LoginID DirectWeb plugin
3. Let LoginID automatically set up the plugin on your dashboard via the Setup Wizard. [Insert screenshot]
4. On your dashboard, you will need to fill in the application name and your Callback URL. 
5. Click on ‘Export to my WordPress site’  
6. On your WordPress setting page, click on Save Settings (All configuration variables will be pre-filled for you)
7. Generate your registration or login pages by clicking the corresponding links, or use our shortcode to embed the code into any of your WordPress pages.

*Do it yourself (DIY) (estimated time: 1min to 2mins)*
1. Login to your WordPress developer dashboard
2. Install the LoginID DirectWeb plugin
3. On a new tab, register your developer account on the LoginID dashboard. 
4. Click on the Integration tab
5. Agree to our Customer License Agreement
6. Choose the DirectWeb integration option and follow the instruction on screen
7. Copy your credentials or use ‘export to my WordPress site’ to populate your configuration settings on the plugin
8. On your WordPress setting page, click on Save Settings (All configuration variables will be pre-filled for you)
9. Generate your registration or login pages by clicking the corresponding links, or use our shortcode to embed the code into any of your WordPress pages.

= How does the plugin work? =
When a user tries to sign in to your website, they will be prompted to register for an account on your website. Once the user completes the registration process, they will be able to sign in using strong FIDO-certified authentication versus using traditional passwords.

= Can I customize the registration page? =
Yes, you can customize the registration page in line with your website’s look and feel. You will be able to paste in LoginID registration and login shortcodes as per your UX needs on any of your UX journey for your end users.

= When I install the plugin, will my existing users be able to login? =
Yes. Our solution augments your existing authentication flows. You may choose to replace or add LoginID authentication based on your specific needs. 

= Can I integrate this plugin with my other installed plugins such as my eCommerce plugin? =
We can assist you with integrating the LoginID DirectWeb with your other installed plugins. Please email us at [support@loginid.io](support@loginid.io) or contact us via the Live Chat option on [loginid.io](https://loginid.io). 

= I need to customize the plugin or I need support and help? =
Please email us at [support@loginid.io](support@loginid.io) or contact us via the Live Chat option on [loginid.io](https://loginid.io). 

= Where can I report bugs or leave feedback? =
Please email us at [support@loginid.io](support@loginid.io) or contact us via the Live Chat option on [loginid.io](https://loginid.io). 

= I have other queries or need additional support. =
For any other queries or if you need additional support, please email us at [support@loginid.io](support@loginid.io) or contact us via the Live Chat option on [loginid.io](https://loginid.io). 

= Error: Your identity token could not be verified =
This error means that the plugin is unable to verify the validity of the login or register claim. This can often be resulted from a bad baseURL and api key combination. Please double check your Base URL and API Key parameters in the plugin settings against your credentials on the LoginID dashboard.

== Screenshots ==

Coming soon.

== Changelog ==

= 1.0.14 =
Updated to security best practices using phpcs.

= 1.0.13 =
Update node dependencies for security. 
Update plugin description.

= 1.0.12 =
Enforce password selection during registration process to make sure a user has multiple ways of logging in.
Reword certain features to clarify functionalities.

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