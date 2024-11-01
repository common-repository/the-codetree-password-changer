=== Plugin Name ===
Contributors: Ryan Huff, The CodeTree
Donate link: http://mycodetree.com/donations/
Tags: security, hack, utility, password
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 2.0

A Wordpress utility that can be used by itself or with an active subscription from http://mycodetree.com.

== Description ==

This plugin addresses a much needed feature within the Wordpress core; a password change reminder service. Yes, being reminded to change your password can be a nusance but it is a good idea to always and regularly change your password.

The CodeTree Password Changer is a simple utility that will remind users to change their account password. You can specify the intervals in which reminders are made.  Once a user's password has expired the utility will send a daily email to the user until the password is changed.  The CodeTree Password Changer also includes access to a simple random password generator for convenience. Users *ARE NOT* prevented from logging in to Wordpress if their password expires but they will receive a daily email until their password is changed. The plugin also provides a handy link to some randomly generated passwords. You may enable or disable password monitoring on an individual user level.

Using the plugin without an API key will limit the user the three predefined interval periods; using a valid API key will allow the user to specify any interval. You can get your API key at http://mycodetree.com

== Installation ==

1. Upload the plugin archive file into Wordpress using the 'Add New' option under the plugin menu in the Administration area.
1. Alternatively, you can unzip the plugin archive and FTP the contents to the wp-content/plugin/ folder of Wordpress
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Navigate to 'Codetree Pass' under the settings menu. If using the plugin with an active subscription then place your 
API key in the 'API Key' in the appropriate box.
1. Turn monitoring 'on' for the users you wish to monitor or 'off' for the users you do not want to monitor. The default for news users is 'off'.

== Frequently Asked Questions ==

= Please supply a valid API key found in the subscription area of your account .. =

This indicates that you have not supplied an API key to the plugin. You do not need an API key or
an active CodeTree subscription in order to use the Manual Backup feature of the plugin.

= This is not a valid API key =

This indicates that the API key supplied to the plugin is not a valid API key issued by The CodeTree.
Login into your account at http://mycodetree.com and verify your API key.  Please email support@mycodetree.com
if you have an difficulties.

= The API key is not valid for the domain =

This indicates that the API key supplied to the plugin may be valid but is not issued for the domain that
it is being used with. Login into your account at http://mycodetree.com and verify your API key.  
Please email support@mycodetree.com if you have an difficulties.

== Screenshots ==

1. Example of the plugin's settings menu.
2.  Without a valid API key you'll see a *get API key* link and a restricted duration selection box


== Changelog ==

= 1.0 =
* Launch Version
= 1.5 =
* Added increased features for duration
= 2.0 =
* Blessed version, released to the world at large

== Upgrade Notice ==

= 1.0 =
* First Stable Release
= 1.5 =
* Fixed several typos and bugs
= 2.0 =
* Fixed one typo and a calculation error in the difference engine
