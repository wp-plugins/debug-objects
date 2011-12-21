=== Debug Objects ===
Contributors: Bueltge
Donate link: http://bueltge.de/wunschliste/
Tags: debug, sql, analyse, tuning, performance, database, queries, query, php, 
Requires at least: 2.7
Tested up to: 3.3
Stable tag: 1.1.0
The Plugin Debug Objects provides the user, which has the appropriate rights, normally the administrator, a large number of information. Values and content get displayed at the frontend of the blog, to analyze errors but also to better understand WordPress.


== Description ==
The Plugin Debug Objects provides the user, which has the appropriate rights, normally the administrator, a large number of information. Values and content get displayed at the frontend of the blog, to analyze errors but also to better understand WordPress.

= Important for use =
Add to any URL of the WP-installation the string `?debugobjects=true`, so that list all informations of the plugin below the site in frontend or backend.
You can set the constant `FB_WPDO_GET_DEBUG` to `FALSE` for the permanent diversion of all values.

= The Plugin provides in various tabs information to: =
* PHP
* Memory usage
* Operating System
* Server
* WordPress Version
* Language
* Very extensive definitions of various constants
* Cookie definitions
* File Permissions
* Separate user and usermeta tables
* FTP and SSH definitions
* Query information
* Conditional tags; value of the tag
* Theme information
* Template Information
* Cache content
* Hooks and filters
* Functions, which respond on hooks and filters
* Contents of arrays to hooks and filters
* All defined constants

The plugin can be used with my Plugin [Debug Queries](http://wordpress.org/extend/plugins/debug-queries/ "Debug Queries") and thus the analysis and optimization of the blog can be used. [Debug Queries](http://wordpress.org/extend/plugins/debug-queries/ "Debug Queries") will be integrated into the frontend of Debug Objects and is only for users with appropriate rights possible.

The plugin does not filter values and should only be used for information and optimization, I don't recommended to use it on a live blog. For developers it can rapidly deliver data, which is useful in a development environment.
There are no data in the database and there are no settings. Therefore, the installation is pretty simple: Just upload the Plugin in the Plugin directory or use the automatic installation of the backend to install and activate the Plugin. In the footer of the frontend of the blog, you can see the information.

The plugin list all entries on frontend and backend of your install; you can custom this with the follow constants on the php-file in the folder of the plugin
`// Hook on Frontend
define( 'FB_WPDO_FRONTEND', TRUE );`
`// Hook on Backend
define( 'FB_WPDO_BACKEND', TRUE );`

= Localizations =
* Thanks for belorussion translation to [Marcis G.](http://pc.de/ "pc.de")
* Thanks for japanese translation to [Fumito Mizuno](http://ounziw.com/ "Standing on the Shoulder of Linus")
* Thanks for hindi translation to[Alois M&auml;nner](http://www.nautilus-one.at "http://www.nautilus-one.at")
* Thanks for dutch translation to [Renè](http://wpwebshop.com/premium-wordpress-themes/ "WP webshop")

= More Plugins = 
Please see also my [Premium Plugins](http://wpplugins.com/author/malo.conny/). Maybe you find an solution for your requirement.

= Interested in WordPress tips and tricks =
You may also be interested in WordPress tips and tricks at [WP Engineer](http://wpengineer.com/) or for german people [bueltge.de](http://bueltge.de/) 


== Installation ==
= Requirements =
* WordPress version 2.6 and later (tested at 2.9)

= Installation =
1. Unpack the download-package
1. Upload the file to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. See the informations on the frontend of your blog
1. Ready


== Screenshots ==
1. Example Screenshot (Version 2.0.0, WordPress 3.3)


== Other Notes ==
= Licence =
Good news, this plugin is free for everyone! Since it's released under the GPL, you can use it free of charge on your personal or commercial blog. But if you enjoy this plugin, you can thank me and leave a [small donation](http://bueltge.de/wunschliste/ "Wishliste and Donate") for the time I've spent writing and supporting this plugin. And I really don't want to know how many hours of my life this plugin has already eaten ;)

= Translations =
The plugin comes with various translations, please refer to the [WordPress Codex](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") for more information about activating the translation. If you want to help to translate the plugin to your language, please have a look at the .pot file which contains all defintions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) (Windows).


== Changelog ==
= 2.0.0 =
* current under development; but you can test and use it

= v1.0.3 (03/23/2011) =
* changes for the plugin Debug Queries
* small changes fpr WP Codex and notice of WP 3.1

= v1.0.2 (03/06/2011))=
* small fix on 2 php notice
* change the description of plugins
* add new language file for german users

= v1.0.1 (11/12/2010) =
* Bugfix: check for vars for no php warnings from WP Errors

= v1.0.0 (11/06/2010) =
* Bugfix: set vars for no php warnings
* Feature: add param for only debug via get-params; see description

= v0.3 (02/05/2010) =
* Small fix for search plugin Debug Queries

= v0.2 (17/12/2009) =
* also view all contens in backend of WordPress
* small bugfixes on html-markup
* 2 new constants for hook on frontend and backend; see the php-file

= v0.1 (30/06/2009) =
* Write a Plugin based on my ideas and my many help files


== Frequently Asked Questions ==
= I love this plugin! How can I show the developer how much I appreciate his work? =
Please visit [my website](http://bueltge.de/ "bueltge.de") and let him know your care or see the [wishlist](http://bueltge.de/wunschliste/ "Wishlist") of the author or use the donate form.
