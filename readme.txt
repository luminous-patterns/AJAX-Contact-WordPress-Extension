=== AJAX Contact ===
Contributors: kounterfeit 
Tags: ajax form, contact, jquery, form, contact form, jquery form, ajax contact form, contact widget, widget, ajax widget, form widget, captcha, recaptcha, google recaptcha
Requires at least: 3.0.0
Tested up to: 3.4.1
Stable tag: 2.0.3

Easily add AJAX contact forms to any page, post or widget area with an unlimited number of custom fields. Easy to manage and style!

== Description ==

<p>Easily and quickly create AJAX contact forms that you can then embed in posts, pages and widget areas.  Supports honeypot anti-bot validation and Google ReCAPTCHA to protect against spam.  Customise the email body on a per-form basis, all submissions are logged as well as emailed.</p>

<p>Form headings, labels, fields and error messages are easy to style using CSS.  Form validation and submission degrades gracefully when JavaScript is disabled on the client system.</p>

<ul>
<li>Main Features
  <ul>
    <li>SSL Support</li>
    <li>Unlimited number of fields</li>
    <li>jQuery AJAX Validation/Submission</li>
    <li>Degrades gracefully with JavaScript off</li>
    <li>Powerful JavaScript enhanced form editor interface</li>
    <li>Form submission logging</li>
    <li>Customise form email notification body</li>
    <li>Allows multiple forms/form instances per page</li>
    <li>Intuitive, <em>really easy to use</em> admin interface</li>
    <li>Easy to style form display using CSS</li>
    <li>Uses native wp_mail() function, (works well with <a href = 'http://wordpress.org/extend/plugins/wp-mail-smtp/screenshots/' >WP Mail SMTP</a>)</li>
    <li>Extra layer of server side form validation (for super-sneaky bots or spammers)</li>
    <li>Embed forms easily by use of a short code</li>
    <li>Ability to use the current page/post title or ID in field options or default values</li>
    <li>AJAX Contact widget</li>
    <li>Spam Prevention
        <ul>
            <li>Honeypot</li>
            <li>Google ReCAPTCHA</li>
        </ul>
    </li>
    <li>Multi-Language Support
        <ul>
            <li>English</li>
            <li>Slovak by Martin Krcho (www.mojandroid.sk)</li>
            <li>Polish by Krzysztof Pałka</li>
        </ul>
    </li>
  </ul>
</li>
<li>Field Types
  <ul>
    <li>Text Input</li>
    <li>Text Input (Password)</li>
    <li>Text Input (Read-Only)</li>
    <li>Text Input (email validation)</li>
    <li>Password Input</li>
    <li>Text Area</li>
    <li>Select Box</li>
    <li>Multi-Select Box</li>
    <li>Check box</li>
    <li>Radio button set</li>
    <li>Hidden field</li>
    <li>Read-only text input</li>
    <li>H1, H2, H3 & H4</li>
  </ul>
</li>
<li>Built for WordPress 3.0+</li>
</ul>

<p>This addon was developed by <a href = 'http://www.integratedweb.com.au'>Integrated Web Services</a>. Feel free to get in contact with us with any feedback or feature requests.</p>

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

== Frequently Asked Questions ==

= What happens if JavaScript is disabled on my visitors computer? =

AJAX Contact degrades gracefully if JavaScript is disabled.

= How do I stop the form editor from displaying on the edit post page? =

Via the screen options menu that you can display on the edit post page by clicking the tab in the top right hand corner.

= Can I use a field as the from address or in the subject line? =

Yes, just use %%field_id%% in the from address or subject line where you want the field value to appear.  Remember to replace field_id with the actual Field ID for the field you want it to replace with (e.g. %%email_address%%).

= Does AJAX Contact track submissions =

Yes, AJAX Contact keeps a copy of every email sent using your form for you to keep track of.

= Are there any anti-bot validation techniques available in AJAX Contact =

Yes, AJAX Contact supports both honeypot anti-bot validation and Google's ReCAPTCHA.

= I cant get the email to send or change the from address =

You may be having trouble sending emails due to hosting restrictions, you might try installing <a href = 'http://wordpress.org/extend/plugins/wp-mail-smtp/screenshots/' >WP Mail SMTP</a> and setting up a SMTP account for WordPress to use for sending emails.  In some instances users have found installing WP Mail SMTP and configuring an SMTP account fixed the problem.

== Screenshots ==

1. AJAX Contact Settings
2. Form Editor
3. View Form Submission
4. Example Form (with ReCAPTCHA)
5. AJAX Contact widget
6. Example Form (with ReCAPTCHA on twentyeleven theme)

== Changelog ==

= 2.0.4 =
* Fixed SSL bug in wp-admin
* Fixed SSL detection for Google ReCAPTCHA
* Polish translation added by Krzysztof Pałka

= 2.0.3 =
* Enhanced multi-language support added by Martin Krcho (www.mojandroid.sk)
* Slovak translation added by Martin Krcho (www.mojandroid.sk)

= 2.0.2 =
* Fix redirect

= 2.0.1 =
* Fix last update date on WordPress.org

= 2.0.0 =
* Custom Email Bodies
* Form Submission Logging
* New anti-spam handling including (but not limited to) Google ReCAPTCHA
* Improved AJAX handling
* Default settings
* Improved interface
* Lots of core changes

= 1.6.0 =
* Introduced basic spam bot prevention tactics
* Fixed a bug breaking forms when using non-english letters as field names
* Fixed a bug causing issues when using the same field name for multiple fields
* Removed id's from elements for better XHTML validity
* Some submission mis-behavior has been fixed as a result of the form element id removal

= 1.5.0 =
* Some admin UI enhancements
* Added ability to hide labels for fields
* Added two new field types (hidden and read-only)
* Introduced support for setting the current page/post title or ID as the default value or options for fields

= 1.4.1 =
* Bug fix for issue causing error messages to display when the form submission was successful

= 1.4.0 =
* Added AJAX Contact form widget
* Fixed a bug that broke some links in the admin section

= 1.3.0 =
* Contact form degrades gracefully with JavaScript off
* Multiple forms / form instances conflict when placed on a single page fixed
* Added custom post type for form management

= 1.2.0 =
* Admin Interface Enhancements
* Admin drag & drop re-ordering
* Added admin icons for better UEX
* Added 6 new field types

= 1.1.1 =
* Admin Interface Enhancements

= 1.1.0 =
* Major Admin Interface Enhancements
* Minor Bug Fixes

= 1.0.2 =
* Admin Interface Enhancements
* Minor Bug Fixes

= 1.0.1 =
* Admin Interface Enhancements
* Minor Bug Fixes

== Upgrade Notice ==

= 2.0.3 =
Update for improved multi-language support and Slovak translation by Martin Krcho (www.mojandroid.sk)

= 2.0.2 =
Fix for broken form redirect functionality

= 2.0.1 =
From v2 - Lots of core changes, custom email bodys, form submission logging, new anti-spam including Google ReCAPTCHA, better ajax handling, default settings and heaps more.  Worth the upgrade.

= 2.0.0 =
Lots of core changes, custom email bodys, form submission logging, new anti-spam including Google ReCAPTCHA, better ajax handling, default settings and heaps more.  Worth the upgrade.

= 1.6.0 =
This version introduces basic spam prevention, some bug fixes including a fix for non-english character sets and duplicate field names.  I'd strongly recommend upgrading to v1.6.

= 1.5.0 =
This version features some UI enhancements, adds two new field types (hidden and read-only) and introduces support for setting the current page/post title or ID as the default value or options for fields.

= 1.4.1 =
This version provides a fix for an issue causing error messages to display when the form submission was successful.

= 1.4.0 =
This version fixes some minor bugs including a one that broke some admin control panel links. This version also introduces a widget for the displaying of contact forms - meaning any widget area can now be filled with an AJAX Contact form!

= 1.3.0 =
This version introduces graceful degradation when JavaScript is disabled on the client system, contact forms as a custom post type and the bug causing issues with multiple forms / form instances on a single page has been fixed. 

= 1.2.0 =
This version introduces a number of admin UI enhancements as well as 6 new field types including headings.  Form field items can now be re-ordered in the admin interface by dragging & dropping.

= 1.1.1 =
This version introduces a number of minor admin UI enhancements.

= 1.1.0 =
This version introduces a number of minor admin UI enhancements and bug fixes.

= 1.0.2 =
This version introduces a number of minor admin UI enhancements and bug fixes.

= 1.0.1 =
This version introduces a number of minor admin UI enhancements and bug fixes.