<?php
/**
 * @package Debug Objects
 * @author Frank B&uuml;ltge
 * @version 0.1
 */
 
/*
Plugin Name: Debug Objects
Plugin URI: http://bueltge.de/wordpress-theme-debuggen/536/
Description: List filter and action-hooks, cache data and return of conditional tags only for admins; for debug purposes. It is possible to include the plugin <a href="http://wordpress.org/extend/plugins/debug-queries/">Debug Objects</a>
Version: 0.1
Author: Frank B&uuml;ltge
Author URI: http://bueltge.de/
Last Change: 29.06.2009 13:11:27
*/

//error_reporting(E_ALL);

//avoid direct calls to this file, because now WP core and framework has been used
if ( !function_exists('add_action') ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

if ( function_exists('add_action') ) {
	//WordPress definitions
	if ( !defined('WP_CONTENT_URL') )
		define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
	if ( !defined('WP_CONTENT_DIR') )
		define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
	if ( !defined('WP_PLUGIN_URL') )
		define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins');
	if ( !defined('WP_PLUGIN_DIR') )
		define('WP_PLUGIN_DIR', WP_CONTENT_DIR. '/plugins');
	if ( !defined('PLUGINDIR') )
		define( 'PLUGINDIR', 'wp-content/plugins' ); // Relative to ABSPATH.  For back compat.
	if ( !defined('WP_LANG_DIR') )
		define('WP_LANG_DIR', WP_CONTENT_DIR . '/languages');

	// plugin definitions
	define( 'FB_WPDO_BASENAME', plugin_basename(__FILE__) );
	define( 'FB_WPDO_BASEFOLDER', plugin_basename( dirname( __FILE__ ) ) );
	define( 'FB_WPDO_TEXTDOMAIN', 'debug_objects' );
	define( 'FB_WPDO_VIEW_IS_NOT', TRUE ); // view all conditional tags, she have not true
	define( 'FB_WPDO_VIEW_HOOKS', TRUE );
	define( 'FB_WPDO_VIEW_CACHE', TRUE );
	
	define( 'FB_WPDO_SORT_HOOKS', TRUE );
	
	if ( !defined('SAVEQUERIES') )
		define('SAVEQUERIES', true);
}

if ( !class_exists('DebugObjects') ) {
	class DebugObjects {
		
		// constructor
		function DebugObjects() {
			
			if ( function_exists('register_activation_hook') )
				register_activation_hook( __FILE__, array(&$this, 'activate') );
			if ( function_exists('register_uninstall_hook') )
				register_uninstall_hook( __FILE__, array(&$this, 'deactivate') );
			if ( function_exists('register_deactivation_hook') )
				register_deactivation_hook( __FILE__, array(&$this, 'deactivate') );
				
			add_action( 'init', array(&$this, 'on_init'), 1 );
		}
		
		
		function on_init() {
			
			if ( !current_user_can('DebugObjects') )
				return;
			
			if ( !is_admin() ) {
				add_action( 'wp_footer', array(&$this, 'wp_footer') );
				wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'debug-objects', WP_PLUGIN_URL . '/' . FB_WPDO_BASEFOLDER . '/js/debug_objects.js', array('jquery') );
				wp_enqueue_style( 'do-jquery-ui-all-css', WP_PLUGIN_URL . '/' . FB_WPDO_BASEFOLDER . '/css/ui.all.css' );
				wp_enqueue_style( 'do-style', WP_PLUGIN_URL . '/' . FB_WPDO_BASEFOLDER . '/css/style-frontend.css' );
			}
		}
		
		
		function view_stuff() {
			global $locale;
			
			if ( defined('WPLANG') )
				$locale = WPLANG;
			if ( empty($locale) )
				$locale = 'en_US';
				
			$memory_usage = function_exists('memory_get_usage') ? round(memory_get_usage() / 1024 / 1024, 2) : 0;
			$memory_limit = (int) ini_get('memory_limit') ;
			
			if ( !empty($memory_usage) && !empty($memory_limit) )
				$memory_percent = round( $memory_usage / $memory_limit * 100, 0 );
			
			$oskey = $_SERVER['HTTP_USER_AGENT'];
			//Operating-System scan start
			if ( preg_match('=WIN=i', $oskey) ) { //Windows
				if (preg_match('=NT=i', $oskey)) {
					if (preg_match('=5.1=', $oskey) ) {
						$os = __( 'Windows XP', FB_WPDO_TEXTDOMAIN );
					} elseif(preg_match('=5.0=', $oskey) ) {//Windows 2000
						$os = __( 'Windows 2000', FB_WPDO_TEXTDOMAIN );
					}
				} else {
					if (preg_match('=ME=', $oskey) ) { //Windows ME
						$os = __( 'Windows ME', FB_WPDO_TEXTDOMAIN );
					} elseif(preg_match('=98=', $oskey) ) { //Windows 98
						$os = __( 'Windows 98', FB_WPDO_TEXTDOMAIN );
					} elseif(preg_match('=95=', $oskey) ) { //Windows 95
						$os = __( 'Windows 95', FB_WPDO_TEXTDOMAIN );}
				}
			} elseif (preg_match('=MAC=i', $oskey) ) { //Macintosh
				$os = __( 'Macintosh', FB_WPDO_TEXTDOMAIN );
			} elseif (preg_match('=LINUX=i', $oskey) ) { //Linux
				$os = __( 'Linux', FB_WPDO_TEXTDOMAIN );
			} //Operating-System scan end
			
			if ( !defined(AUTOSAVE_INTERVAL) )
				$autosave_interval = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( !constant('AUTOSAVE_INTERVAL') )
				$autosave_interval = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			else
				$autosave_interval = AUTOSAVE_INTERVAL . __( 's', FB_WPDO_TEXTDOMAIN );
			
			if ( !defined(WP_POST_REVISIONS) )
				$post_revisions = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( !constant('WP_POST_REVISIONS') )
				$post_revisions = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			else
				$post_revisions = WP_POST_REVISIONS;
			
			if ( !defined(SAVEQUERIES) )
				$savequeries = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( !constant('SAVEQUERIES') )
				$savequeries = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			elseif ( SAVEQUERIES == 1 )
				$savequeries = __( 'ON', FB_WPDO_TEXTDOMAIN );
			
			if ( !defined(WP_DEBUG) )
				$debug = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( !constant('WP_DEBUG') )
				$debug = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			elseif ( WP_DEBUG == 1 )
				$debug = __( 'ON', FB_WPDO_TEXTDOMAIN );
				
			if ( !defined(FORCE_SSL_LOGIN) )
				$ssl_login = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( !constant('FORCE_SSL_LOGIN') )
				$ssl_login = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			elseif ( FORCE_SSL_LOGIN == 1 )
				$ssl_login = __( 'ON', FB_WPDO_TEXTDOMAIN );
			
			if ( !defined(CONCATENATE_SCRIPTS) )
				$concatenate_scripts = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( !constant('CONCATENATE_SCRIPTS') )
				$concatenate_scripts = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			elseif ( CONCATENATE_SCRIPTS == 1 )
					$concatenate_scripts = __( 'ON', FB_WPDO_TEXTDOMAIN );
			
			if ( !defined(COMPRESS_SCRIPTS) )
				$compress_scripts = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( !constant('COMPRESS_SCRIPTS') )
				$compress_scripts = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			elseif ( COMPRESS_SCRIPTS == 1 )
				$compress_scripts = __( 'ON', FB_WPDO_TEXTDOMAIN );
			
			if ( !defined(COMPRESS_CSS) )
				$compress_css = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( !constant('COMPRESS_CSS') )
				$compress_css = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			elseif ( COMPRESS_CSS == 1 )
				$compress_css = __( 'ON', FB_WPDO_TEXTDOMAIN );
			
			if ( !defined(ENFORCE_GZIP) )
				$enforce_gzip = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( !constant('ENFORCE_GZIP') )
				$enforce_gzip = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			elseif ( ENFORCE_GZIP == 1 )
				$enforce_gzip = __( 'ON', FB_WPDO_TEXTDOMAIN );
			
			$echo  = '';
			$echo .= "\n" . '<h4>' . __( 'PHP Version &amp; System', FB_WPDO_TEXTDOMAIN ) . '</h4>' . "\n";
			$echo .= '<ul>' . "\n";
			
			$echo .= '<li>' . __( 'PHP version:', FB_WPDO_TEXTDOMAIN ) . ' ' . PHP_VERSION . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Server:', FB_WPDO_TEXTDOMAIN ) . ' ' . substr( $_SERVER['SERVER_SOFTWARE'], 0, 14 ) . '</li>' . "\n";
			$echo .= '<li>' . __( 'Server SW:', FB_WPDO_TEXTDOMAIN ) . ' ' . $_SERVER['SERVER_SOFTWARE'] . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'http User Agent:', FB_WPDO_TEXTDOMAIN ) . ' ' . $_SERVER['HTTP_USER_AGENT'] . '</li>' . "\n";
			$echo .= '<li>' . __( 'OS version:', FB_WPDO_TEXTDOMAIN ) . ' ' . $os . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Memory usage:', FB_WPDO_TEXTDOMAIN ) . ' ' . $memory_usage . ' MByte</li>' . "\n";
			$echo .= '<li>' . __( 'Memory limit, PHP Configuration', FB_WPDO_TEXTDOMAIN ) . ' : ' . $memory_limit . ' MByte</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Memory percent:', FB_WPDO_TEXTDOMAIN ) . ' ' . $memory_percent . ' % of 100%</li>' . "\n";
			$echo .= '</ul>' . "\n";
			
			$echo .= "\n" . '<h4>' . __( 'WordPress Informations', FB_WPDO_TEXTDOMAIN ) . '</h4>' . "\n";
			$echo .= '<ul>' . "\n";
			$echo .= '<li>' . __( 'Version:', FB_WPDO_TEXTDOMAIN ) . ' ' . get_bloginfo('version') . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Language, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>WPLANG</code>: ' . $locale . '</li>' . "\n";
			$echo .= '<li>' . __( 'Language folder, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>WP_LANG_DIR</code>: ' . WP_LANG_DIR . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Content URL, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>WP_CONTENT_URL</code>: ' . WP_CONTENT_URL . '</li>' . "\n";
			$echo .= '<li>' . __( 'Content folder, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>WP_CONTENT_DIR</code>: ' . WP_CONTENT_DIR . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Memory limit, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>WP_MEMORY_LIMIT</code>: ' . $memory_limit . ' MByte</li>' . "\n";
			$echo .= '<li>' . __( 'Post revision, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>WP_POST_REVISIONS</code>: ' . $post_revisions . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Save queries, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>SAVEQUERIES</code>: ' . $savequeries . '</li>' . "\n";
			$echo .= '<li>' . __( 'Debug option, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>WP_DEBUG</code>: ' . $debug . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'SSL Login, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>FORCE_SSL_LOGIN</code>: ' . $ssl_login . '</li>' . "\n";
			$echo .= '<li>' . __( 'Concatenate scripts, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>CONCATENATE_SCRIPTS</code>: ' . $concatenate_scripts . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Compress scripts, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>COMPRESS_SCRIPTS</code>: ' . $compress_scripts . '</li>' . "\n";
			$echo .= '<li>' . __( 'Compress stylesheet, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>COMPRESS_CSS</code>: ' . $compress_css . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Enforce GZIP, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>ENFORCE_GZIP</code>: ' . $enforce_gzip . '</li>' . "\n";
			$echo .= '<li>' . __( 'Autosave interval, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>AUTOSAVE_INTERVAL</code>: ' . $autosave_interval . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Queries: ', FB_WPDO_TEXTDOMAIN ) . get_num_queries() . 'q <small><a href="http://wordpress.org/extend/plugins/debug-queries/">' . __( 'See more Details with my plugin', FB_WPDO_TEXTDOMAIN) . ' Debug Objects</a></small></li>' . "\n";
			$echo .= '<li>' . __( 'Timer stop: ', FB_WPDO_TEXTDOMAIN ) . timer_stop() . 's</li>' . "\n";
			$echo .= '</ul>' . "\n";
			
			return $echo;
		}
		
		
		function view_conditional_tags() {
			
			$is = '';
			$is_not = '';
			
			$is .=  "\n" . '<h4><a href="http://codex.wordpress.org/Conditional_Tags">Conditional Tags</a></h4>' . "\n";
			$is .= '<p>' . __( 'The Conditional Tags can be used in your Template files to change what content is displayed and how that content is displayed on a particular page depending on what conditions that page matches. You see on this view the condition of all possible tags.', FB_WPDO_TEXTDOMAIN ) . '</p>' . "\n";
			$is .= '<ul>' . "\n";
			
			if ( is_admin() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_admin" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> admin</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> admin</li>' . "\n";
		
			if ( is_archive() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_archive" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> archive</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> archive</li>' . "\n";
		
			if ( is_attachment() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_attachment" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> attachment</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> attachment</li>' . "\n";
		
			if ( is_author() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_author" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> author</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> author</li>' . "\n";
		
			if ( is_category() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_category" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> category</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> category</li>' . "\n";
		
			if ( is_tag() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_tag" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> tag</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> tag</li>' . "\n";
			
			if ( is_tax() ) $is .= "\t" . '<li><a href="http://codex.wordpress.org/Function_Reference/is_tax" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> tag</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> tax</li>' . "\n";
			
			if ( is_comments_popup() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_comments_popup" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> comments_popup</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> comments_popup</li>' . "\n";
		
			if ( is_date() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_date" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> date</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> date</li>' . "\n";
			
			if ( is_day() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_day" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> day</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> day</li>' . "\n";
		
			if ( is_feed() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_feed" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> feed</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> feed</li>' . "\n";
			
			if ( is_front_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_front_page" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> front_page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> front_page</li>' . "\n";
			
			if ( is_home() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_home" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> home</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> home</li>' . "\n";
			
			if ( is_month() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_month" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> month</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> month</li>' . "\n";
		
			if ( is_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_page" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> page</li>' . "\n";
		
			if ( is_paged() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_paged" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> paged</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> paged</li>' . "\n";
			
			if ( is_plugin_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_plugin_page" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> plugin_page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> plugin_page</li>' . "\n";
			
			if ( is_preview() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_preview" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> preview</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> preview</li>' . "\n";
		
			if ( is_robots() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_robots" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> robots</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> robots</li>' . "\n";
		
			if ( is_search() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_search" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> search</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> search</li>' . "\n";
		
			if ( is_single() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_single" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> single</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> single</li>' . "\n";
		
			if ( is_singular() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_singular" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> singular</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> singular</li>' . "\n";
		
			if ( is_sticky() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_sticky" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> sticky</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> sticky</li>' . "\n";
		
			if ( is_time() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_time" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> time</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> time</li>' . "\n";
		
			if ( is_trackback() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_trackback" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> trackback</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> trackback</li>' . "\n";
		
			if ( is_year() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_year" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> year</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> year</li>' . "\n";
		
			if ( is_404() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_404" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __('is', FB_WPDO_TEXTDOMAIN) . '</b> 404</a></li>' . "\n";
			else $is_not .= '<li><i>' . __('no', FB_WPDO_TEXTDOMAIN) . '</i> 404</li>' . "\n";
		
			if ( FB_WPDO_VIEW_IS_NOT == ('TRUE' || 1) ) {
				$is .= $is_not;
			}
			
			$is .= '</ul>' . "\n";
			
			return $is;
		}
		
		
		// same as WP for check template
		function view_template() {
			
			if ( is_trackback() ) {
				$this->template = ABSPATH . 'wp-trackback.php';
			} else if ( is_404() && $template = get_404_template() ) {
				$this->template = $template;
			} else if ( is_search() && $template = get_search_template() ) {
				$this->template = $template;
			} else if ( is_tax() && $template = get_taxonomy_template()) {
				$this->template = $template;
			} else if ( is_home() && $template = get_home_template() ) {
				$this->template = $template;
			} else if ( is_attachment() && $template = get_attachment_template() ) {
				$this->template = $template;
			} else if ( is_single() && $template = get_single_template() ) {
				$this->template = $template;
			} else if ( is_page() && $template = get_page_template() ) {
				$this->template = $template;
			} else if ( is_category() && $template = get_category_template()) {
				$this->template = $template;
			} else if ( is_tag() && $template = get_tag_template()) {
				$this->template = $template;
			} else if ( is_author() && $template = get_author_template() ) {
				$this->template = $template;
			} else if ( is_date() && $template = get_date_template() ) {
				$this->template = $template;
			} else if ( is_archive() && $template = get_archive_template() ) {
				$this->template = $template;
			} else if ( is_comments_popup() && $template = get_comments_popup_template() ) {
				$this->template = $template;
			} else if ( is_paged() && $template = get_paged_template() ) {
				$this->template = $template;
			} else if ( is_tag() && $template = get_tag_template() ) {
				$this->template = $template;
			} else if ( is_tax() && $template = get_taxonomy_template() ) {
				$this->template = $template;
			} else if ( file_exists(TEMPLATEPATH . "/index.php") ) {
				$this->template = TEMPLATEPATH . "/index.php";
			}
			
			$theme_data = array();
			$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css');

			$echo  = '';
			$echo .=  "\n" . '<h4>' . __( 'Theme Values', FB_WPDO_TEXTDOMAIN ) . '</h4>' . "\n";
			$echo .= '<ul>' . "\n";
				$echo .= '<li class="alternate">' . __( 'Current theme name:', FB_WPDO_TEXTDOMAIN ) . ' ';
				if ( $theme_data['Name'] != '' )
					$echo .= $theme_data['Name'];
				else
					$echo .= __( 'Undefined', FB_WPDO_TEXTDOMAIN );
				$echo .= '</li>';
				
				$echo .= '<li>' . __( 'Current theme title:', FB_WPDO_TEXTDOMAIN ) . ' ' . $theme_data['Title'] . '</li>';
				
				$echo .= '<li class="alternate">' . __( 'Current theme uri:', FB_WPDO_TEXTDOMAIN ) . ' ';
				if ( $theme_data['URI'] != '' )
					$echo .= $theme_data['URI'];
				else
					$echo .= __( 'Undefined', FB_WPDO_TEXTDOMAIN );
				$echo .= '</li>';
				
				$echo .= '<li>' . __( 'Current theme description:', FB_WPDO_TEXTDOMAIN ) . ' ';
				if ( $theme_data['Description'] != '' )
					$echo .= $theme_data['Description'];
				else
					$echo .= __( 'Undefined', FB_WPDO_TEXTDOMAIN );
				$echo .= '</li>';
				
				$echo .= '<li class="alternate">' . __( 'Current theme author:', FB_WPDO_TEXTDOMAIN ) . ' ';
				if ( $theme_data['Author'] != '' )
					$echo .= $theme_data['Author'];
				else
					$echo .= __( 'Undefined', FB_WPDO_TEXTDOMAIN );
				$echo .= '</li>';
				
				$echo .= '<li>' . __( 'Current theme version:', FB_WPDO_TEXTDOMAIN ) . ' ';
				if ( $theme_data['Version'] != '' )
					$echo .= $theme_data['Version'];
				else
					$echo .= __( 'Undefined', FB_WPDO_TEXTDOMAIN );
				$echo .= '</li>';
				
				$echo .= '<li class="alternate">' . __( 'Current theme template:', FB_WPDO_TEXTDOMAIN ) . ' ';
				if ( $theme_data['Template'] != '' )
					$echo .= $theme_data['Template'];
				else
					$echo .= __( 'Undefined', FB_WPDO_TEXTDOMAIN );
				$echo .= '</li>';
				
				$echo .= '<li>' . __( 'Current theme status:', FB_WPDO_TEXTDOMAIN ) . ' ' . $theme_data['Status'] . '</li>';
				$echo .= '<li class="alternate">' . __( 'Current theme tags:', FB_WPDO_TEXTDOMAIN ) . ' ';
				if ( $theme_data['Tags'][0] != '' ) {
					$echo .= join(', ', $theme_data['Tags']);
				} else {
					$echo .= __( 'Undefined', FB_WPDO_TEXTDOMAIN );
				}
				$echo .= '</li>';
				
				$echo .= '<li>' . __( 'Current theme:', FB_WPDO_TEXTDOMAIN ) . ' ' . get_template() . '</li>';
				$echo .= '<li class="alternate">' . __( 'Current theme directory:', FB_WPDO_TEXTDOMAIN ) . ' ' . get_template_directory() . '</li>';
				$echo .= '<li>' . __( 'Current stylesheet:', FB_WPDO_TEXTDOMAIN ) . ' ' . get_stylesheet() . '</li>';
				$echo .= '<li class="alternate">' . __( 'Current stylesheet directory:', FB_WPDO_TEXTDOMAIN ) . ' ' . get_stylesheet_directory() . '</li>';
				$echo .= '<li>' . __( 'Current template path, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>TEMPLATEPATH</code>: ' . TEMPLATEPATH . '</li>';
				$echo .= '<li>' . __( 'Current stylesheet path, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>STYLESHEETPATH</code>: ' . STYLESHEETPATH . '</li>';
			$echo .= '</ul>' . "\n";
						
			$echo .=  "\n" . '<h4>' . __( 'Current template file', FB_WPDO_TEXTDOMAIN ) . '</h4>' . "\n";
			$echo .= '<ul>' . "\n";
			$echo .= '<li class="alternate">' . $template . '</li>';
			$echo .= '</ul>' . "\n";
			
			return $echo;
		}
		
		
		function view_hooks() {
			global $wp_filter;
			
			if (FB_WPDO_SORT_HOOKS)
				ksort($wp_filter);
			
			$class = '';
			$echo  = '';
			
			//hooks
			$echo .= "\n" . '<h4>' . __('Simple WordPress Hooks &amp; Filters Insight', FB_WPDO_TEXTDOMAIN) . '</h4>' . "\n";
			$echo .= "\n\n". '<ol>' . "\n";
			$wp_hook = 0;
			$wp_func = 0;
			
			foreach($wp_filter as $hook => $arrays) {
				if (FB_WPDO_SORT_HOOKS)
					ksort($arrays);
				$wp_hook ++;
				
				$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
				$echo .= '<li' . $class . ' id="hook_' . $wp_hook. '" title="Hook: ' . $hook. '">' . $hook . "\n";
				$echo .= '<ul id="li' . $wp_hook. '">' . "\n";
				
				foreach($arrays as $priority => $subarray) {
					$echo .= '<li>' . __('Priority', FB_WPDO_TEXTDOMAIN ) . ' <strong>' . $priority. '</strong> : ' . "\n";
					$echo .= '<ol>' . "\n";
					foreach($subarray as $sub) {
						$wp_func ++;
						
						$echo .= '<li>';
						$func = $sub['function'];
						if ( is_array($func) ) {
							$echo .= '<code><em>' .get_class($func[0]) . '-></em>' . $func[1] . '()</code>';
							if ( empty($func[0]) ) {
								$echo .= "\n". '<ul>' . "\n";
								$x = 0;
								foreach ($func[0] as $k=>$v) {
									$x ++;
									if ( !is_string($v) ) {
										$v  = htmlentities( serialize($v) );
										$v  = '<a href="javascript:toggle(\'serialize_' . $wp_func. $x. '\');">' . __('View data', FB_WPDO_TEXTDOMAIN ) . '</a><textarea style="display:none;" class="large-text code" id="serialize_' . $wp_func. $x. '"name="v" cols="50" rows="10">' . $v. '</textarea>';
									}
									$echo .= '<li>' . $k. ' : ' . $v. '</li>' . "\n";
								}
								$echo .= '</ul>' . "\n";
							}
						} else {
							$echo .= '<code>' . $func. '()</code>';
						}
						$echo .= '</li>' . "\n";
						
					}
					$echo .= '</ol>' . "\n";
					$echo .= '</li>' . "\n";
				}
				
				$echo .= '</ul>' . "\n";
				$echo .= '</li>' . "\n";
			}
			
			$echo .= '</ol>' . "\n";
			
			$echo .= '<p class="alternate">' . __( 'Hooks total:', FB_WPDO_TEXTDOMAIN ) . ' ' . $wp_hook . '<br />' . __( 'Register filter/actions total:', FB_WPDO_TEXTDOMAIN ) . ' ' . $wp_func . '</p>';
			
			return $echo;
		}
		
		
		/**
		 * tree for array
		 */
		function get_as_ul_tree($arr, $root_name = '', $unserialized_string = false) {
			global $wp_object;
			
			$wp_object = 0;
			$output = '';
			$wp_object ++;
			
			if ( !is_object($arr) && !is_array($arr) )
				return $output;
			
			if ($root_name) {
				$output .= '<ul class="root' . ($unserialized_string ? ' unserialized' : '') . '">' . "\n";
				if ( is_object($arr) ) {
					$output .= '<li class="vt-object"><span class="' . ($unserialized_string ? 'unserialized' : 'key') . '">' . $root_name . '</span>';
					if (!$unserialized_string) $output .= '<br />' . "\n";
					$output .= '<small><em>type</em>: object (' . get_class($arr) . ')</small><br/><small><em>count</em>: ' . count( get_object_vars($arr) ) . '</small><ul>'; 
				} else {
					$output .= '<li class="vt-array"><span class="' . ($unserialized_string ? 'unserialized' : 'key') . '">' . $root_name . '</span>';
					if (!$unserialized_string) $output .= '<br />' . "\n";
					$output .= '<small><em>type</em>: array</small><br/><small><em>count</em>: ' . count($arr) . '</small><ul>'; 
				}
			}
			
			foreach($arr as $key => $val) {
				$wp_object ++;
				
				if (is_numeric($key)) $key = "[". $key. "]"; 
				$vt = gettype($val);
				switch ($vt) {
					case "object":
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">".htmlspecialchars($key). "</span>";
						$output .= "<br/><small><em>type</em>: $vt (".get_class($val). ") | <em>count</em>: ".count($val). "</small>"; 
						$output .= "<ul>";
						$output .= $this->get_as_ul_tree($val);
						$output .= "</ul></li>";
					break;
					case "array":
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">".htmlspecialchars($key). "</span>";
						$output .= "<br/><small><em>type</em>: $vt | <em>count</em>: ".count($val). "</small>"; 
						$output .= "<ul>";
						$output .= $this->get_as_ul_tree($val);
						$output .= "</ul></li>";
					break;
					case "boolean":
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">".htmlspecialchars($key). "</span>";
						$output .= "<br/><small><em>type</em>: $vt</small><br/><small><em>value</em>: </small><span class=\"value\">".($val?"true":"false"). "</span></li>";
					break;
					case "integer":
					case "double":
					case "float":
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">".htmlspecialchars($key). "</span>";
						$output .= "<br/><small><em>type</em>: $vt</small><br/><small><em>value</em>: </small><span class=\"value\">$val</span></li>";
					break;
					case "string":
						$obj = @unserialize($val);
						$is_serialized = ($obj !== false && preg_match("/^(O:|a:)/", $val));
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">".htmlspecialchars($key). "</span>";
						$output .= "<br/><small><em>type</em>: $vt | <em>size</em>: ".strlen($val). " | <em>serialized</em>: ".($is_serialized !== false?"true":"false"). "</small><br/>";
						if ($is_serialized) {
							$output .= $this->get_as_ul_tree($obj, "<small><em>value</em>:</small> <span class=\"value\">[unserialized]</span>", true);
						}
						else {
							$output .= '<small><em>value</em>: </small><span class="value">' . htmlspecialchars($val) . '</span>';
						}
						$output .= '</li>';
					break;
					default: //what the hell is this ?
						$output .= '<li id="hook_$wp_object" class="vt-' . $vt . '"><span class="key">' . htmlspecialchars($key) . '</span>';
						$output .= '<br/><small><em>type</em>: ' . $vt . '</small><br/><small><em>value</em>:</small><span class="value">' . @htmlspecialchars($val) . '</span></li>';
					break;
				}
			}
			
			if ($root_name)
				$output .= "\t" . '</ul>' . "\n\t" . '</li>' . "\n" . '</ul>' . "\n";
			
			return $output;
		}
		
		
		function view_cache() {
			global $wp_object_cache, $wp_object;
			
			$echo  = '';
			$echo .= $this->get_as_ul_tree( $wp_object_cache, '<h4>WordPress Object Cache</h4>' );
			$echo .= '<p>' . __( 'Objects total:', FB_WPDO_TEXTDOMAIN ) . ' ' . $wp_object . '</p>';
			
			return $echo;
		}
		
		
		// return/echo
		function get_DebugObjects($view=true) {
			
			if ( !current_user_can('DebugObjects') )
				return;
			
			global $wp_filter;
			
			$plugins = get_option('active_plugins');
			$required_plugin = 'debug_queries/debug_queries.php';
			$debug_queries_on = FALSE;
			if ( in_array( $required_plugin , $plugins ) ) {
				$debug_queries_on = TRUE;
				global $DebugQueries;
			}
			
			$echo  = '';
			$echo .= '<br style="clear: both;"';
			$echo .= '<div id="debugobjects">' . "\n";
			$echo .= '<h3><a href="http://bueltge.de/">Debug Objects</a> ' . __('by Frank B&uuml;ltge') . ', <a href="http://bueltge.de/">bueltge.de</a></h3>' . "\n";
			$echo .= '<p>' . __( '&raquo; Deactivate after analysis!', FB_WPDO_TEXTDOMAIN ). '</p>' . "\n";
				
			//echo on footer
			$echo .= '<div id="debugobjectstabs">' . "\n";
				$echo .= '<ul>' . "\n";
				$echo .= '	<li><a href="#memory">' . __( 'PHP, Memory Usage &amp; WordPress', FB_WPDO_TEXTDOMAIN ) . '</a></li>' . "\n";
				$echo .= '	<li><a href="#conditional_tags">' . __( 'Conditional Tags', FB_WPDO_TEXTDOMAIN ) . '</a></li>' . "\n";
				$echo .= '	<li><a href="#template">' . __( 'Theme &amp; Template', FB_WPDO_TEXTDOMAIN ) . '</a></li>' . "\n";
				if (FB_WPDO_VIEW_CACHE)
					$echo .= '	<li><a href="#cache">' . __( 'Cache', FB_WPDO_TEXTDOMAIN ) . '</a></li>' . "\n";
				if (FB_WPDO_VIEW_HOOKS)
					$echo .= '	<li><a href="#hooks">' . __( 'Hooks &amp; Filter', FB_WPDO_TEXTDOMAIN ) . '</a>' . "\n";
				if ($debug_queries_on)
					$echo .= '	<li><a href="#queries">' . __( 'Queries', FB_WPDO_TEXTDOMAIN ) . '</a>' . "\n";
				$echo .= '</ul>' . "\n";
			
				$echo .= '<div id="memory">' . "\n";
				$echo .= $this->view_stuff();
				$echo .= '</div>' . "\n\n";
				
				$echo .= '<div id="conditional_tags">' . "\n";
				$echo .= $this->view_conditional_tags();
				$echo .= '</div>' . "\n\n";
				
				$echo .= '<div id="template">' . "\n";
				$echo .= $this->view_template();
				$echo .= '</div>' . "\n\n";
				
				if (FB_WPDO_VIEW_CACHE) {
					$echo .= '<div id="cache">' . "\n";
					$echo .= $this->view_cache();
					$echo .= '</div>' . "\n\n";
				}
				
				if (FB_WPDO_VIEW_HOOKS) {
					$echo .= '<div id="hooks">' . "\n";
					$echo .= $this->view_hooks();
					$echo .= '</div>' . "\n\n";
				}
				
				if ($debug_queries_on) {
					$echo .= '<div id="queries">' . "\n";
					$echo .= $DebugQueries->get_fbDebugQueries();
					$echo .= '</div>' . "\n\n";
				}
				
			$echo .= '</div>' . "\n\n";
				
			$echo .= '</div>' . "\n";
			
			if ($view)
				echo $echo;
			else
				return $echo;
		}
		
		
		// add user rights
		function activate() {
			global $wp_roles;
			
			$wp_roles->add_cap('administrator', 'DebugObjects');
		}
		
		
		// delete user rights
		function deactivate() {
			global $wp_roles;
			
			$wp_roles->remove_cap('administrator', 'DebugObjects');
		}
		
		
		// function for WP < 2.8
		function plugins_url($path = '', $plugin = '') {
			if ( function_exists('is_ssl') )
				$scheme = ( is_ssl() ? 'https' : 'http' );
			else
				$scheme = 'http';
			$url = WP_PLUGIN_URL;
			if ( 0 === strpos($url, 'http') ) {
				if ( function_exists('is_ssl') && is_ssl() )
					$url = str_replace( 'http://', "{$scheme}://", $url );
			}
		
			if ( !empty($plugin) && is_string($plugin) )
			{
				$folder = dirname(plugin_basename($plugin));
				if (' . ' != $folder)
					$url .= '/' . ltrim($folder, '/');
			}
		
			if ( !empty($path) && is_string($path) && strpos($path, ' .. ') === false )
				$url .= '/' . ltrim($path, '/');
		
			return apply_filters('plugins_url', $url, $path, $plugin);
		}
		
		
		// echo in frontend
		function wp_footer() {
			
			if ( !current_user_can('DebugObjects') )
				return;
			
			$echo  = '';
			$echo .= $this->get_DebugObjects();
			
			return $echo;
		}
	
	} // end class
	
	$DebugObjects = new DebugObjects();
} // end if class exists
?>