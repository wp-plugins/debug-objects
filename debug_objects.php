<?php
/**
 * @package Debug Objects
 * @author  Frank B&uuml;ltge
 */
 
/**
 * Plugin Name: Debug Objects
 * Plugin URI:  http://bueltge.de/debug-objects-wordpress-plugin/966/
 * Description: List filter and action-hooks, cache data, defined constants, php and memory informations and return of conditional tags only for admins; for debug, informations or learning purposes. It is possible to include the plugin <a href="http://wordpress.org/extend/plugins/debug-queries/">Debug Queries</a>. Add to any URL of the WP-installation the string <code>?debugobjects=TRUE</code>, so that list all informations of the plugin below the site in frontend or backend. You can set the constant <code>FB_WPDO_GET_DEBUG</code> to <code>FALSE</code> for the permanent diversion of all values.
 * Version:     1.1.0
 * License:     GPLv3
 * Author:      Frank B&uuml;ltge
 * Author URI:  http://bueltge.de/
 * Last Change: 01.12.2011 11:56:02
 */

//error_reporting(E_ALL);

//avoid direct calls to this file, because now WP core and framework has been used
if ( !function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( function_exists( 'add_action' ) ) {
	//WordPress definitions
	if ( ! defined( 'WP_CONTENT_URL' ) )
		define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
	if ( ! defined( 'WP_CONTENT_DIR' ) )
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	if ( ! defined( 'WP_PLUGIN_URL' ) )
		define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
	if ( ! defined( 'WP_PLUGIN_DIR' ) )
		define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR. '/plugins' );
	if ( ! defined( 'PLUGINDIR' ) )
		define( 'PLUGINDIR', 'wp-content/plugins' ); // Relative to ABSPATH.  For back compat.
	if ( ! defined( 'WP_LANG_DIR' ) )
		define( 'WP_LANG_DIR', WP_CONTENT_DIR . '/languages' );

	// plugin definitions
	define( 'FB_WPDO_BASENAME', plugin_basename(__FILE__) );
	define( 'FB_WPDO_BASEFOLDER', plugin_basename( dirname( __FILE__ ) ) );
	define( 'FB_WPDO_TEXTDOMAIN', 'debug_objects' );
	define( 'FB_WPDO_VIEW_IS_NOT', TRUE ); // view all conditional tags, she have not TRUE
	define( 'FB_WPDO_VIEW_CACHE', TRUE );
	define( 'FB_WPDO_VIEW_HOOKS', TRUE );
	define( 'FB_WPDO_VIEW_CONSTANTS', TRUE );
	define( 'FB_WPDO_VIEW_HOOK_TABLE', TRUE );
	define( 'FB_WPDO_VIEW_ENQUEUED_STUFF', TRUE );
	
	define( 'FB_WPDO_SORT_HOOKS', false );
	// list only on get-param in url
	define( 'FB_WPDO_GET_DEBUG', TRUE );
	// Hook on Frontend
	define( 'FB_WPDO_FRONTEND', TRUE );
	// Hook on Backend
	define( 'FB_WPDO_BACKEND', TRUE );
	
	if ( ! defined( 'SAVEQUERIES' ) )
		define( 'SAVEQUERIES', TRUE);
}

if ( ! class_exists( 'Debug_Objects' ) ) {
	
	class Debug_Objects {
		
		// constructor
		function Debug_Objects () {
			
			register_activation_hook( __FILE__,   array( &$this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );
			register_uninstall_hook(__FILE__,     array( 'Debug_Objects', 'deactivate' ) );
			
			add_action( 'init', array( &$this, 'on_init' ), 1 );
			add_action( 'all',  array( $this, 'record_hook_usage' ) );
			// small js for klick on admin bar and scroll top
			add_action( 'admin_print_footer_scripts', array( $this, 'admin_bar_scrolltop' ) );
		}
		
		
		function on_init () {
			
			if ( ! current_user_can( 'DebugObjects' ) )
				return;
			
			if ( defined( 'FB_WPDO_GET_DEBUG' ) && FB_WPDO_GET_DEBUG ) {
				if ( ! isset($_GET['debugobjects']) )
					return;
			}
			
			if ( defined( 'FB_WPDO_FRONTEND' ) && FB_WPDO_FRONTEND && !is_admin() ) {
				add_action( 'init', array( &$this, 'textdomain' ) );
				add_action( 'wp_footer', array( &$this, 'wp_footer' ) );
				
				wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'debug-objects', WP_PLUGIN_URL . '/' . FB_WPDO_BASEFOLDER . '/js/debug_objects.js', array( 'jquery' ) );
				wp_enqueue_style( 'do-jquery-ui-all-css', WP_PLUGIN_URL . '/' . FB_WPDO_BASEFOLDER . '/css/ui.all.css' );
				wp_enqueue_style( 'do-style', WP_PLUGIN_URL . '/' . FB_WPDO_BASEFOLDER . '/css/style-frontend.css' );
			} elseif ( defined( 'FB_WPDO_BACKEND' ) && FB_WPDO_BACKEND && is_admin() ) {
				add_action( 'init', array( &$this, 'textdomain' ) );
				add_action( 'admin_footer', array( &$this, 'wp_footer' ) );
				
				wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'debug-objects', WP_PLUGIN_URL . '/' . FB_WPDO_BASEFOLDER . '/js/debug_objects.js', array( 'jquery' ) );
				wp_enqueue_style( 'do-jquery-ui-all-css', WP_PLUGIN_URL . '/' . FB_WPDO_BASEFOLDER . '/css/ui.all.css' );
				wp_enqueue_style( 'do-style', WP_PLUGIN_URL . '/' . FB_WPDO_BASEFOLDER . '/css/style-frontend.css' );
			}
		}
		
		
		function textdomain () {
			
			load_plugin_textdomain(FB_WPDO_TEXTDOMAIN, FALSE, dirname( plugin_basename(__FILE__) ) . '/languages' );
		}
		
		
		function view_stuff () {
			global $locale;
			
			$plugins = get_option( 'active_plugins' );
			$required_plugin = 'debug-queries/debug_queries.php';
			$debug_queries_on = FALSE;
			if ( in_array( $required_plugin , $plugins ) ) {
				$debug_queries_on = TRUE;
				global $debug_queries;
			}
			
			if ( defined( 'WPLANG' ) )
				$locale = WPLANG;
			if ( empty($locale) )
				$locale = 'en_US';
				
			$memory_usage = function_exists( 'memory_get_usage' ) ? round(memory_get_usage() / 1024 / 1024, 2) : 0;
			$memory_limit = (int) ini_get( 'memory_limit' ) ;
			
			if ( !empty($memory_usage) && !empty($memory_limit) )
				$memory_percent = round( $memory_usage / $memory_limit * 100, 0 );
			
			$oskey = $_SERVER['HTTP_USER_AGENT'];
			//Operating-System scan start
			if ( preg_match( '=WIN=i', $oskey) ) { //Windows
				if (preg_match( '=NT=i', $oskey)) {
					if (preg_match( '=5.1=', $oskey) ) {
						$os = __( 'Windows XP', FB_WPDO_TEXTDOMAIN );
					} elseif(preg_match( '=5.0=', $oskey) ) {//Windows 2000
						$os = __( 'Windows 2000', FB_WPDO_TEXTDOMAIN );
					}
				} else {
					if (preg_match( '=ME=', $oskey) ) { //Windows ME
						$os = __( 'Windows ME', FB_WPDO_TEXTDOMAIN );
					} elseif(preg_match( '=98=', $oskey) ) { //Windows 98
						$os = __( 'Windows 98', FB_WPDO_TEXTDOMAIN );
					} elseif(preg_match( '=95=', $oskey) ) { //Windows 95
						$os = __( 'Windows 95', FB_WPDO_TEXTDOMAIN );}
				}
			} elseif (preg_match( '=MAC=i', $oskey) ) { //Macintosh
				$os = __( 'Macintosh', FB_WPDO_TEXTDOMAIN );
			} elseif (preg_match( '=LINUX=i', $oskey) ) { //Linux
				$os = __( 'Linux', FB_WPDO_TEXTDOMAIN );
			} //Operating-System scan end
			
			if ( ! defined( 'AUTOSAVE_INTERVAL' ) )
				$autosave_interval = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( ! constant( 'AUTOSAVE_INTERVAL' ) )
				$autosave_interval = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			else
				$autosave_interval = AUTOSAVE_INTERVAL . __( 's', FB_WPDO_TEXTDOMAIN );
			
			if ( ! defined( 'WP_POST_REVISIONS' ) )
				$post_revisions = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( ! constant( 'WP_POST_REVISIONS' ) )
				$post_revisions = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			else
				$post_revisions = WP_POST_REVISIONS;
			
			if ( ! defined( 'SAVEQUERIES' ) )
				$savequeries = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( ! constant( 'SAVEQUERIES' ) )
				$savequeries = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			elseif ( SAVEQUERIES == 1 )
				$savequeries = __( 'ON', FB_WPDO_TEXTDOMAIN );
			
			if ( ! defined( 'WP_DEBUG' ) )
				$debug = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( ! constant( 'WP_DEBUG' ) )
				$debug = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			elseif ( WP_DEBUG == 1 )
				$debug = __( 'ON', FB_WPDO_TEXTDOMAIN );
				
			if ( ! defined( 'FORCE_SSL_LOGIN' ) )
				$ssl_login = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( ! constant( 'FORCE_SSL_LOGIN' ) )
				$ssl_login = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			elseif ( FORCE_SSL_LOGIN == 1 )
				$ssl_login = __( 'ON', FB_WPDO_TEXTDOMAIN );
			
			if ( ! defined( 'CONCATENATE_SCRIPTS' ) )
				$concatenate_scripts = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( ! constant( 'CONCATENATE_SCRIPTS' ) )
				$concatenate_scripts = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			elseif ( CONCATENATE_SCRIPTS == 1 )
					$concatenate_scripts = __( 'ON', FB_WPDO_TEXTDOMAIN );
			
			if ( ! defined( 'COMPRESS_SCRIPTS' ) )
				$compress_scripts = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( ! constant( 'COMPRESS_SCRIPTS' ) )
				$compress_scripts = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			elseif ( COMPRESS_SCRIPTS == 1 )
				$compress_scripts = __( 'ON', FB_WPDO_TEXTDOMAIN );
			
			if ( ! defined( 'COMPRESS_CSS' ) )
				$compress_css = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( ! constant( 'COMPRESS_CSS' ) )
				$compress_css = __( 'OFF', FB_WPDO_TEXTDOMAIN );
			elseif ( COMPRESS_CSS == 1 )
				$compress_css = __( 'ON', FB_WPDO_TEXTDOMAIN );
			
			if ( ! defined( 'ENFORCE_GZIP' ) )
				$enforce_gzip = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			elseif ( ! constant( 'ENFORCE_GZIP' ) )
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
			$echo .= '<li>' . __( 'Version:', FB_WPDO_TEXTDOMAIN ) . ' ' . get_bloginfo( 'version' ) . '</li>' . "\n";
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
			$echo .= '</ul>' . "\n";
			
			if ( ! defined( 'COOKIE_DOMAIN' ) )
				$cookie_domain = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$cookie_domain = COOKIE_DOMAIN;
				
			if ( ! defined( 'COOKIEPATH' ) )
				$cookiepath = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$cookiepath = COOKIEPATH;
				
			if ( ! defined( 'SITECOOKIEPATH' ) )
				$sitecookiepath = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$sitecookiepath = SITECOOKIEPATH;
				
			if ( ! defined( 'PLUGINS_COOKIE_PATH' ) )
				$plugins_cookie_path = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$plugins_cookie_path = PLUGINS_COOKIE_PATH;
				
			if ( ! defined( 'ADMIN_COOKIE_PATH' ) )
				$admin_cookie_path = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$admin_cookie_path = ADMIN_COOKIE_PATH;
			
			$echo .= "\n" . '<h4>' . __( 'WordPress Cookie Informations', FB_WPDO_TEXTDOMAIN ) . '</h4>' . "\n";
			$echo .= '<ul>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Cookie domain, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>COOKIE_DOMAIN</code>: ' . $cookie_domain . '</li>' . "\n";
			$echo .= '<li>' . __( 'Cookie path, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>COOKIEPATH</code>: ' . $cookiepath . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Site cookie path, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>SITECOOKIEPATH</code>: ' . $sitecookiepath . '</li>' . "\n";
			$echo .= '<li>' . __( 'Plugin cookie path, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>PLUGINS_COOKIE_PATH</code>: ' . $plugins_cookie_path . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Admin cookie path, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>ADMIN_COOKIE_PATH</code>: ' . $admin_cookie_path . '</li>' . "\n";
			$echo .= '</ul>' . "\n";
			
			if ( ! defined( 'FS_CHMOD_FILE' ) )
				$fs_chmod_file = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$fs_chmod_file = FS_CHMOD_FILE;
				
			if ( ! defined( 'FS_CHMOD_DIR' ) )
				$fs_chmod_dir = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$fs_chmod_dir = FS_CHMOD_DIR;
			
			$echo .= "\n" . '<h4>' . __( 'WordPress File Permissions Informations', FB_WPDO_TEXTDOMAIN ) . '</h4>' . "\n";
			$echo .= '<ul>' . "\n";
			$echo .= '<li class="alternate">' . __( 'File Permissions, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>FS_CHMOD_FILE</code>: ' . $fs_chmod_file . '</li>' . "\n";
			$echo .= '<li>' . __( 'DIR Permissions, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>FS_CHMOD_DIR</code>: ' . $fs_chmod_dir . '</li>' . "\n";
			$echo .= '</ul>' . "\n";
			
			if ( ! defined( 'CUSTOM_USER_TABLE' ) )
				$custom_user_table = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$custom_user_table = CUSTOM_USER_TABLE;
				
			if ( ! defined( 'CUSTOM_USER_META_TABLE' ) )
				$custom_user_meta_table = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$custom_user_meta_table = CUSTOM_USER_META_TABLE;
			
			$echo .= "\n" . '<h4>' . __( 'WordPress Custom User &amp; Usermeta Tables', FB_WPDO_TEXTDOMAIN ) . '</h4>' . "\n";
			$echo .= '<ul>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Custom User Table, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>CUSTOM_USER_TABLE</code>: ' . $custom_user_table . '</li>' . "\n";
			$echo .= '<li>' . __( 'Cookie path, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>CUSTOM_USER_META_TABLE</code>: ' . $custom_user_meta_table . '</li>' . "\n";
			$echo .= '</ul>' . "\n";
			
			if ( ! defined( 'FS_METHOD' ) )
				$fs_method = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$fs_method = FS_METHOD;
				
			if ( ! defined( 'FTP_BASE' ) )
				$ftp_base = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$ftp_base = FTP_BASE;
			
			if ( ! defined( 'FTP_CONTENT_DIR' ) )
				$ftp_content_dir = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$ftp_content_dir = FTP_CONTENT_DIR;
				
			if ( ! defined( 'FTP_PLUGIN_DIR' ) )
				$ftp_plugin_dir = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$ftp_plugin_dir = FTP_PLUGIN_DIR;
			
			if ( ! defined( 'FTP_PUBKEY' ) )
				$ftp_pubkey = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$ftp_pubkey = FTP_PUBKEY;
				
			if ( ! defined( 'FTP_PRIVKEY' ) )
				$ftp_privkey = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$ftp_privkey = FTP_PRIVKEY;
			
			if ( ! defined( 'FTP_USER' ) )
				$ftp_user = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$ftp_user = FTP_USER;
				
			if ( ! defined( 'FTP_PASS' ) )
				$ftp_pass = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$ftp_pass = FTP_PASS;
			
			if ( ! defined( 'FTP_HOST' ) )
				$ftp_host = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			else
				$ftp_host = FTP_HOST;
			
			$echo .= "\n" . '<h4>' . __( 'WordPress FTP/SSH Informations', FB_WPDO_TEXTDOMAIN ) . '</h4>' . "\n";
			$echo .= '<ul>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Forces the filesystem method, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>FS_METHOD</code> (<code>direct</code>, <code>ssh</code>, <code>ftpext</code> or <code>ftpsockets</code>): ' . $fs_method . '</li>' . "\n";
			$echo .= '<li>' . __( 'Path to root install directory, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>FTP_BASE</code>: ' . $ftp_base . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Absolute path to wp-content directory, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>FTP_CONTENT_DIR</code>: ' . $ftp_content_dir . '</li>' . "\n";
			$echo .= '<li>' . __( 'Absolute path to plugin directory, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>FTP_PLUGIN_DIR</code>: ' . $ftp_plugin_dir . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Absolute path to SSH public key, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>FTP_PUBKEY</code>: ' . $ftp_pubkey . '</li>' . "\n";
			$echo .= '<li>' . __( 'dorector path to SSH private key, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>FTP_PRIVKEY</code>: ' . $ftp_privkey . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'FTP or SSH username, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>FTP_USER</code>: ' . $ftp_user . '</li>' . "\n";
			$echo .= '<li>' . __( 'FTP or SSH password, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>FTP_PASS</code>: ' . $ftp_pass . '</li>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Hostname, constant', FB_WPDO_TEXTDOMAIN ) . ' <code>FTP_HOST</code>: ' . $ftp_host . '</li>' . "\n";
			$echo .= '</ul>' . "\n";
			
			$echo .= "\n" . '<h4>' . __( 'WordPress Query Informations', FB_WPDO_TEXTDOMAIN ) . '</h4>' . "\n";
			$echo .= '<ul>' . "\n";
			$echo .= '<li class="alternate">' . __( 'Queries:', FB_WPDO_TEXTDOMAIN ) . ' ' . get_num_queries() . 'q';
			if ($debug_queries_on)
				$echo .= ' <small><a href="http://wordpress.org/extend/plugins/debug-queries/">' . __( 'See more Details with my plugin', FB_WPDO_TEXTDOMAIN) . ' Debug Queries</a></small>';
			$echo .= '</li>' . "\n";
			$echo .= '<li>' . __( 'Timer stop:', FB_WPDO_TEXTDOMAIN ) . ' ' . timer_stop() . 's</li>' . "\n";
			$echo .= '</ul>' . "\n";
			
			// PHP_SELF
			if ( ! isset( $_SERVER['PATH_INFO'] ) )
				$_SERVER['PATH_INFO'] = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			if ( ! isset( $_SERVER['QUERY_STRING'] ) )
				$_SERVER['QUERY_STRING'] = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			if ( ! isset( $_SERVER['SCRIPT_FILENAME'] ) )
				$_SERVER['SCRIPT_FILENAME'] = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			if ( ! isset( $_SERVER['PHP_SELF'] ) )
				$_SERVER['PHP_SELF'] = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			if ( ! isset( $_GET['error'] ) )
				$_GET['error'] = __( 'Undefined', FB_WPDO_TEXTDOMAIN );
			
			$echo .= "\n" . '<h4>' . __( 'Selected server and execution environment information', FB_WPDO_TEXTDOMAIN ) . '</h4>' . "\n";
			$echo .= '<ul>' . "\n";
			$echo .= '<li>' . __( 'PATH_INFO:', FB_WPDO_TEXTDOMAIN ) . ' ' . $_SERVER['PATH_INFO'] . '</li>';
			$echo .= '<li class="alternate">' . __( 'REQUEST_URI:', FB_WPDO_TEXTDOMAIN ) . ' ' . $_SERVER['REQUEST_URI'] . '</li>';
			$echo .= '<li>' . __( 'QUERY_STRING:', FB_WPDO_TEXTDOMAIN ) . ' ' . $_SERVER['QUERY_STRING'] . '</li>';
			$echo .= '<li class="alternate">' . __( 'SCRIPT_NAME:', FB_WPDO_TEXTDOMAIN ) . ' ' . $_SERVER['SCRIPT_NAME'] . '</li>';
			$echo .= '<li>' . __( 'SCRIPT_FILENAME:', FB_WPDO_TEXTDOMAIN ) . ' ' . $_SERVER['SCRIPT_FILENAME'] . '</li>';
			$echo .= '<li class="alternate">' . __( 'PHP_SELF:', FB_WPDO_TEXTDOMAIN ) . ' ' . $_SERVER['PHP_SELF'] . '</li>';
			$echo .= '<li>' . __( 'GET Error:', FB_WPDO_TEXTDOMAIN ) . ' ' . $_GET['error'] . '</li>';
			$echo .= '<li class="alternate">' . __( 'FILE:', FB_WPDO_TEXTDOMAIN ) . ' ' . __FILE__ . '</li>';
			$echo .= '</ul>' . "\n";
			
			// Globals 
			$echo .= "\n" . '<h4>' . __( 'HTTP GET variables', FB_WPDO_TEXTDOMAIN ) . '</h4>' . "\n";
			$echo .= '<ul><li>' . "\n";
			if ( ! isset( $_GET ) || empty( $_GET ) )
				$echo .= __( 'Undefined or empty', FB_WPDO_TEXTDOMAIN );
			else 
				$echo .= var_export( $_GET, true );
			$echo .= '</li></ul>' . "\n";
			
			$echo .= "\n" . '<h4>' . __( 'HTTP POST variables', FB_WPDO_TEXTDOMAIN ) . '</h4>' . "\n";
			$echo .= '<ul><li>' . "\n";
			if ( ! isset( $_POST ) || empty( $_POST ) )
				$echo .= __( 'Undefined or empty', FB_WPDO_TEXTDOMAIN );
			else 
				$echo .= var_export( $_POST, true );
			$echo .= '</li></ul>' . "\n";
			
			return $echo;
		}
		
		
		function view_conditional_tags() {
			
			$is = '';
			$is_not = '';
			
			$is .=  "\n" . '<h4><a href="http://codex.wordpress.org/Conditional_Tags">Conditional Tags</a></h4>' . "\n";
			$is .= '<p>' . __( 'The Conditional Tags can be used in your Template files to change what content is displayed and how that content is displayed on a particular page depending on what conditions that page matches. You see on this view the condition of all possible tags.', FB_WPDO_TEXTDOMAIN ) . '</p>' . "\n";
			$is .= '<ul>' . "\n";
			
			if ( is_admin() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_admin" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> admin</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> admin</li>' . "\n";
		
			if ( is_archive() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_archive" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> archive</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> archive</li>' . "\n";
		
			if ( is_attachment() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_attachment" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> attachment</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> attachment</li>' . "\n";
		
			if ( is_author() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_author" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> author</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> author</li>' . "\n";
		
			if ( is_category() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_category" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> category</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> category</li>' . "\n";
		
			if ( is_tag() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_tag" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> tag</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> tag</li>' . "\n";
			
			if ( is_tax() ) $is .= "\t" . '<li><a href="http://codex.wordpress.org/Function_Reference/is_tax" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> tag</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> tax</li>' . "\n";
			
			if ( is_comments_popup() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_comments_popup" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> comments_popup</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> comments_popup</li>' . "\n";
		
			if ( is_date() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_date" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> date</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> date</li>' . "\n";
			
			if ( is_day() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_day" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> day</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> day</li>' . "\n";
		
			if ( is_feed() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_feed" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> feed</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> feed</li>' . "\n";
			
			if ( is_front_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_front_page" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> front_page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> front_page</li>' . "\n";
			
			if ( is_home() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_home" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> home</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> home</li>' . "\n";
			
			if ( is_month() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_month" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> month</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> month</li>' . "\n";
		
			if ( is_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_page" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> page</li>' . "\n";
		
			if ( is_paged() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_paged" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> paged</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> paged</li>' . "\n";
			
			if ( is_plugin_page() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_plugin_page" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> plugin_page</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> plugin_page</li>' . "\n";
			
			if ( is_preview() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_preview" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> preview</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> preview</li>' . "\n";
		
			if ( is_robots() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_robots" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> robots</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> robots</li>' . "\n";
		
			if ( is_search() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_search" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> search</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> search</li>' . "\n";
		
			if ( is_single() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_single" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> single</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> single</li>' . "\n";
		
			if ( is_singular() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_singular" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> singular</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> singular</li>' . "\n";
		
			if ( ! is_admin() && is_sticky() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_sticky" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> sticky</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> sticky</li>' . "\n";
		
			if ( is_time() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_time" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> time</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> time</li>' . "\n";
		
			if ( is_trackback() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_trackback" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> trackback</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> trackback</li>' . "\n";
		
			if ( is_year() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_year" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> year</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> year</li>' . "\n";
		
			if ( is_404() ) $is .= "\t" . '<li class="alternate"><a href="http://codex.wordpress.org/Function_Reference/is_404" title="' . __( 'Documentation in Codex', FB_WPDO_TEXTDOMAIN ) . '"><b>' . __( 'is', FB_WPDO_TEXTDOMAIN) . '</b> 404</a></li>' . "\n";
			else $is_not .= '<li><i>' . __( 'no', FB_WPDO_TEXTDOMAIN) . '</i> 404</li>' . "\n";
		
			if ( FB_WPDO_VIEW_IS_NOT ) {
				$is .= $is_not;
			}
			
			$is .= '</ul>' . "\n";
			
			return $is;
		}
		
		
		// same as WP for check template
		function view_template () {
			
			if ( isset($template) ) {
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
			}
			$theme_data = array();
			$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );

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
				if ( isset($theme_data['Tags'][0]) && $theme_data['Tags'][0] != '' ) {
					$echo .= join( ', ', $theme_data['Tags']);
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
			
			if ( isset($template) && $template) {
				$echo .=  "\n" . '<h4>' . __( 'Current template file', FB_WPDO_TEXTDOMAIN ) . '</h4>' . "\n";
				$echo .= '<ul>' . "\n";
				$echo .= '<li class="alternate">' . $template . '</li>';
				$echo .= '</ul>' . "\n";
			}
			
			return $echo;
		}
		
		
		function view_hooks () {
			global $wp_filter;
			
			if ( empty( $wp_filter ) )
				return NULL;
			
			if ( FB_WPDO_SORT_HOOKS )
				ksort( $wp_filter );
			
			$class = '';
			$echo  = '';
			
			//hooks
			$echo .= "\n" . '<h4>' . __( 'Simple WordPress Hooks &amp; Filters Insight', FB_WPDO_TEXTDOMAIN) . '</h4>' . "\n";
			$echo .= "\n\n". '<ol>' . "\n";
			$wp_hook = 0;
			$wp_func = 0;
			
			foreach( $wp_filter as $hook => $arrays ) {
				
				if ( FB_WPDO_SORT_HOOKS )
					ksort($arrays);
				
				$wp_hook ++;
				
				$hook = esc_html( $hook );
				
				$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
				$echo .= '<li' . $class . ' id="hook_' . $wp_hook. '" title="Hook: ' . $hook. '">' . $hook . "\n";
				$echo .= '<ul id="li' . $wp_hook . '">' . "\n";
				
				foreach( $arrays as $priority => $subarray ) {
					$echo .= '<li>' . __( 'Priority', FB_WPDO_TEXTDOMAIN ) . ' <strong>' . $priority . '</strong>: ' . "\n";
					$echo .= '<ol>' . "\n";
					foreach($subarray as $sub) {
						$wp_func ++;
						
						$echo .= '<li>';
						$func = $sub['function'];
						$args = $sub['accepted_args'];
						if ( is_array( $func ) ) {
							
							if ( is_object($func[0]) ) {
								$name  = get_class($func[0]) . '::' . $func[1];
								if ( empty($func[0]) ) {
									$echo .= "\n". '<ul>' . "\n";
									$x = 0;
									foreach ( $func[0] as $k => $v ) {
										$x ++;
										if ( ! is_string($v) ) {
											$v  = htmlentities( serialize($v) );
											$v  = '<a href="javascript:toggle(\'serialize_' . $wp_func. $x. '\' );">' . __( 'View data', FB_WPDO_TEXTDOMAIN ) . '</a><textarea style="display:none;" class="large-text code" id="serialize_' . $wp_func. $x. '"name="v" cols="50" rows="10">' . $v. '</textarea>';
										}
										$echo .= '<li>' . $k. ' : ' . $v. '</li>' . "\n";
									}
									$echo .= '</ul>' . "\n";
								}
							} else {
								$echo .= '<code>' . $func[0] . '()</code>';
							} // end if is_object()
						
						} else {
							$name  = $func;
						} // end if is_array()
						
						// echo params
					$echo .= sprintf (
						"\t<code>%s()</code> (%s)",
						esc_html( $name ),
						sprintf(
							_n(
								__( '1 accepted argument', FB_WPDO_TEXTDOMAIN ),
								__( '%s accepted argument', FB_WPDO_TEXTDOMAIN ),
								$args
							),
							$args
						)
					);
				
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
		 * Tests if an input is valid PHP serialized string.
		 *
		 * Checks if a string is serialized using quick string manipulation
		 * to throw out obviously incorrect strings. Unserialize is then run
		 * on the string to perform the final verification.
		 *
		 * Valid serialized forms are the following:
		 * <ul>
		 * <li>boolean: <code>b:1;</code></li>
		 * <li>integer: <code>i:1;</code></li>
		 * <li>double: <code>d:0.2;</code></li>
		 * <li>string: <code>s:4:"test";</code></li>
		 * <li>array: <code>a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}</code></li>
		 * <li>object: <code>O:8:"stdClass":0:{}</code></li>
		 * <li>null: <code>N;</code></li>
		 * </ul>
		 *
		 * @author		Chris Smith <code+php@chris.cs278.org>
		 * @copyright	Copyright (c) 2009 Chris Smith (http://www.cs278.org/)
		 * @license		http://sam.zoy.org/wtfpl/ WTFPL
		 * @param		string	$value	Value to test for serialized form
		 * @param		mixed	$result	Result of unserialize() of the $value
		 * @return		boolean			True if $value is serialized data, otherwise FALSE
		 */
		function is_serialized( $value, &$result = null ) {
			// Bit of a give away this one
			if ( ! is_string( $value ) ) {
				return FALSE;
			}
		
			// Serialized FALSE, return TRUE. unserialize() returns FALSE on an
			// invalid string or it could return FALSE if the string is serialized
			// FALSE, eliminate that possibility.
			if ( $value === 'b:0;' ) {
				$result = FALSE;
				return TRUE;
			}
		
			$length	= strlen($value);
			$end	= '';
			
			if ( isset( $value[0] ) ) {
				switch ($value[0]) {
					case 's':
						if ( $value[$length - 2] !== '"' )
							return FALSE;
						
					case 'b':
					case 'i':
					case 'd':
						// This looks odd but it is quicker than isset()ing
						$end .= ';';
					case 'a':
					case 'O':
						$end .= '}';
			
						if ($value[1] !== ':')
							return FALSE;
			
						switch ($value[2]) {
							case 0:
							case 1:
							case 2:
							case 3:
							case 4:
							case 5:
							case 6:
							case 7:
							case 8:
							case 9:
							break;
			
							default:
								return FALSE;
						}
					case 'N':
						$end .= ';';
					
						if ( $value[$length - 1] !== $end[0] )
							return FALSE;
					break;
					
					default:
						return FALSE;
				}
			}
			
			if ( ( $result = @unserialize($value) ) === FALSE ) {
				$result = null;
				return FALSE;
			}
			
			return TRUE;
		}
		
		
		/**
		 * tree for array
		 */
		function get_as_ul_tree($arr, $root_name = '', $unserialized_string = false) {
			global $wp_object;
			
			$wp_object = 0;
			$output    = '';
			$wp_object ++;
			
			if ( !is_object($arr) && !is_array($arr) )
				return $output;
			
			if ($root_name) {
				$output .= '<ul class="root' . ($unserialized_string ? ' unserialized' : '' ) . '">' . "\n";
				if ( is_object($arr) ) {
					$output .= '<li class="vt-object"><span class="' . ($unserialized_string ? 'unserialized' : 'key' ) . '">' . $root_name . '</span>';
					if (!$unserialized_string)
						$output .= '<br />' . "\n";
					$output .= '<small><em>type</em>: object ( ' . get_class($arr) . ' )</small><br/><small><em>count</em>: ' . count( get_object_vars($arr) ) . '</small><ul>'; 
				} else {
					$output .= '<li class="vt-array"><span class="' . ($unserialized_string ? 'unserialized' : 'key' ) . '">' . $root_name . '</span>';
					if (!$unserialized_string)
						$output .= '<br />' . "\n";
					$output .= '<small><em>type</em>: array</small><br/><small><em>count</em>: ' . count($arr) . '</small><ul>'; 
				}
			}
			
			foreach($arr as $key => $val) {
				$wp_object ++;
				
				if ( is_numeric($key) )
					$key = "[". $key. "]"; 
				$vt = gettype($val);
				switch ($vt) {
					case "object":
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">" . htmlspecialchars($key) . '</span>';
						$output .= "<br/><small><em>type</em>: $vt (" . get_class($val) . ") | <em>count</em>: " . count($val) . "</small>"; 
						if ($val) {
							$output .= '<ul>';
							$output .= $this->get_as_ul_tree($val);
							$output .= '</ul>';
						}
						$output .= '</li>';
					break;
					case "array":
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">" . htmlspecialchars($key) . '</span>';
						$output .= "<br/><small><em>type</em>: $vt | <em>count</em>: " . count($val) . '</small>'; 
						if ($val) {
							$output .= '<ul>';
							$output .= $this->get_as_ul_tree($val);
							$output .= '</ul>';
						}
						$output .= '</li>';
					break;
					case "boolean":
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">" . htmlspecialchars($key) . '</span>';
						$output .= "<br/><small><em>type</em>: $vt</small><br/><small><em>value</em>: </small><span class=\"value\">".($val?"true":"false"). '</span></li>';
					break;
					case "integer":
					case "double":
					case "float":
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">" . htmlspecialchars($key) . '</span>';
						$output .= "<br/><small><em>type</em>: $vt</small><br/><small><em>value</em>: </small><span class=\"value\">$val</span></li>";
					break;
					case "string":
						$val = trim( $val );
						//$val = strtolower( stripslashes( $val ) );
						//$val = base64_decode($val);
						$val = preg_replace( '/;n;/', ';N;', $val );
						$val = str_replace( "\n", "", $val );
						$val = normalize_whitespace($val);
						if ( is_serialized_string( $val ) )
							$obj = unserialize( $val );
						else
							$obj = normalize_whitespace( $val );
						$is_serialized = ($obj !== false && preg_match("/^(O:|a:)/", $val));
						$output .= "<li class=\"vt-$vt\"><span class=\"key\">" . htmlspecialchars($key) . '</span>';
						$output .= "<br/><small><em>type</em>: $vt | <em>size</em>: ".strlen($val). " | <em>serialized</em>: ".(is_serialized($val) !== false?"true":"false"). '</small><br/>';
						if ( is_serialized($val) ) {
							$output .= $this->get_as_ul_tree($obj, "<small><em>value</em>:</small> <span class=\"value\">[unserialized]</span>", true);
						}
						else {
							if ($val)
								$output .= '<small><em>value</em>: </small><span class="value">' . htmlspecialchars($val) . '</span>';
							else
								$output .= '';
						}
						$output .= '</li>';
					break;
					default: //what the hell is this ?
						$output .= '<li id="hook_' . $wp_object . '_' . $vt . '" class="vt-' . $vt . '"><span class="key">' . htmlspecialchars($key) . '</span>';
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
			$echo .= $this->get_as_ul_tree( $wp_object_cache, '<strong class="h4">WordPress Object Cache</strong>' );
			$echo .= '<p>' . __( 'Objects total:', FB_WPDO_TEXTDOMAIN ) . ' ' . $wp_object . '</p>';
			
			return $echo;
		}
		
		
		function view_def_constants() {
			global $wp_object;
			
			$echo  = '';
			$echo .= $this->get_as_ul_tree( get_defined_constants(), '<strong class="h4">All Defined Constants</strong>' );
			$echo .= '<p>' . __( 'Objects total:', FB_WPDO_TEXTDOMAIN ) . ' ' . $wp_object . '</p>';
			
			return $echo;
		}
		
		
		function view_enqueued_stuff( $handles = array() ) {
			global $wp_scripts, $wp_styles;
			
			// scripts
			foreach ( $wp_scripts -> registered as $registered )
				$script_urls[ $registered -> handle ] = $registered -> src;
			// styles
			foreach ( $wp_styles -> registered as $registered )
				$style_urls[ $registered -> handle ] = $registered -> src;
			
			if ( empty( $handles ) ) {
				$handles = array_merge( $wp_scripts -> queue, $wp_styles -> queue );
				array_values( $handles );
			}
			$output = '';
			foreach ( $handles as $handle ) {
				if ( ! empty( $script_urls[ $handle ] ) )
					$output .= '<li>' . $script_urls[ $handle ] . '</li>';
				if ( ! empty( $style_urls[ $handle ] ) )
					$output .= '<li class="alternate">' . $style_urls[ $handle ] . '</li>';
			}
			$output = substr( $output, 0, -1 );
			
			return '<ul>' . $output . '</ul>';
		}
		
		/*
		 * Return Hook for current page
		 */
		function instrument_hooks() {
			global $wpdb;
			
			$hooks = $wpdb->get_results( "SELECT * FROM wp_hook_list ORDER BY first_call" );
			
			$html = array();
			$html[] = '<table>
			<tr>
				<th>1.Call</th>
				<th>Hook-Name</th>
				<th>-Type</th>
				<th>Arguments</th>
				<th>Called by</th>
				<th>Line</th>
				<th>File Name</th>
			</tr>';
			
			$class = '';
			foreach( $hooks as $hook ) {
				$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
				if ( 30 < (int) strlen( $hook->hook_name ) )
					$hook->hook_name = '<span title="' . $hook->hook_name . '">' . substr($hook->hook_name, 0, 30) . '</span>';
				if ( 20 < (int) strlen( $hook->file_name ) )
					$hook->file_name = '<span title="' . $hook->file_name . '">' . substr($hook->file_name, -20, 20) . '</span>';
				$html[] = "<tr{$class}>
					<td>{$hook->first_call}</td>
					<td>{$hook->hook_name}</td>
					<td>{$hook->hook_type}</td>
					<td>{$hook->arg_count}</td>
					<td>{$hook->called_by}</td>
					<td>{$hook->line_num}</td>
					<td>{$hook->file_name}</td>
				</tr>";
			}
			$html[] = '</table>';
			
			return implode( "\n", $html );
		}
		
		/**
		 * Save Hooks in custom table
		 */
		function record_hook_usage( $hook ) {
			global $wpdb;
			
			static $in_hook = FALSE;
			static $first_call = 1;
			static $doc_root;
			$callstack = debug_backtrace();
			if ( ! $in_hook ) {
				$in_hook = TRUE;
				if ( 1 == $first_call ) {
					$doc_root = esc_attr( $_SERVER['DOCUMENT_ROOT'] );
					$results = $wpdb->get_results("SHOW TABLE STATUS LIKE 'wp_hook_list'");
					if ( 1 == count($results) ) {
						$wpdb->query("TRUNCATE TABLE wp_hook_list");
					} else {
						$wpdb->query("CREATE TABLE wp_hook_list (
						called_by varchar(96) NOT NULL,
						hook_name varchar(96) NOT NULL,
						hook_type varchar(15) NOT NULL,
						first_call int(11) NOT NULL,
						arg_count tinyint(4) NOT NULL,
						file_name varchar(128) NOT NULL,
						line_num smallint NOT NULL,
						PRIMARY KEY (first_call,hook_name))"
						);
					}
				}
				$args = func_get_args();
				$arg_count = count($args) - 1;
				$hook_type = str_replace('do_','',
					str_replace(
						'apply_filters','filter',
						str_replace( '_ref_array', '[]', $callstack[3]['function'] )
					)
				);
				$file_name = addslashes( str_replace( $doc_root, '', $callstack[3]['file'] ) );
				$line_num  = $callstack[3]['line'];
				if ( ! isset( $callstack[4] ) )
					$called_by = __( 'Undefinded', FB_WPDO_TEXTDOMAIN );
				else
					$called_by = $callstack[4]['function'] . '()';
				$wpdb->query("INSERT wp_hook_list
					(first_call,called_by,hook_name,hook_type,arg_count,file_name,line_num)
					VALUES ($first_call,'$called_by','$hook','$hook_type',$arg_count,'$file_name',$line_num)");
				$first_call++;
				$in_hook = FALSE;
			}
		}
		
		function admin_bar_scrolltop() {
			if ( ! is_admin_bar_showing() )
				return;
			
			?>
			<script>
			( function( $ ) {
				$( '#wpadminbar' ).click( function() {
					$( 'html, body' ).animate( { scrollTop: 0 }, 100 );
				} );
				$( '#wpadminbar li' ).click( function( e ) { 
					e.stopPropagation(); 
				} );
			} )( jQuery );
			</script>
			<?php
		}
		
		// return/echo
		function get_debug_objects($view=TRUE) {
			
			if ( !current_user_can( 'DebugObjects' ) )
				return;
			
			global $wp_filter;
			
			$plugins = get_option( 'active_plugins' );
			$required_plugin = 'debug-queries/debug_queries.php';
			$debug_queries_on = FALSE;
			if ( in_array( $required_plugin , $plugins ) ) {
				$debug_queries_on = TRUE;
				global $debug_queries;
			}
			
			$echo  = '';
			$echo .= '<br style="clear: both;"/>';
			$echo .= '<div id="debugobjects">' . "\n";
			$echo .= '<h3><a href="http://bueltge.de/">Debug Objects</a> ' . __( 'by Frank B&uuml;ltge', FB_WPDO_TEXTDOMAIN ) . ', <a href="http://bueltge.de/">bueltge.de</a></h3>' . "\n";
			
			//echo on footer
			$echo .= '<div id="debugobjectstabs">' . "\n";
			$echo .= '<ul>' . "\n";
			$echo .= '	<li><a href="#memory">' . __( 'PHP, Globals &amp; WP', FB_WPDO_TEXTDOMAIN ) . '</a></li>' . "\n";
			$echo .= '	<li><a href="#conditional_tags">' . __( 'Conditional Tags', FB_WPDO_TEXTDOMAIN ) . '</a></li>' . "\n";
			$echo .= '	<li><a href="#template">' . __( 'Theme &amp; Template', FB_WPDO_TEXTDOMAIN ) . '</a></li>' . "\n";
			if ( FB_WPDO_VIEW_CACHE )
				$echo .= '	<li><a href="#cache">' . __( 'Cache', FB_WPDO_TEXTDOMAIN ) . '</a></li>' . "\n";
			if ( FB_WPDO_VIEW_HOOKS )
				$echo .= '	<li><a href="#hooks">' . __( 'Hooks &amp; Filter', FB_WPDO_TEXTDOMAIN ) . '</a></li>' . "\n";
			if ( FB_WPDO_VIEW_CONSTANTS )
				$echo .= '	<li><a href="#constants">' . __( 'Constants', FB_WPDO_TEXTDOMAIN ) . '</a></li>' . "\n";
			if ( FB_WPDO_VIEW_ENQUEUED_STUFF )
				$echo .= '	<li><a href="#enqueue">' . __( 'Enqueued Stuff', FB_WPDO_TEXTDOMAIN ) . '</a></li>' . "\n";
			if ( FB_WPDO_VIEW_HOOK_TABLE )
				$echo .= '	<li><a href="#hook_table">' . __( 'Page Hooks', FB_WPDO_TEXTDOMAIN ) . '</a></li>' . "\n";
			if ( $debug_queries_on )
				$echo .= '	<li><a href="#queries">' . __( 'Queries', FB_WPDO_TEXTDOMAIN ) . '</a></li>' . "\n";
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
			
			if ( FB_WPDO_VIEW_CACHE ) {
				$echo .= '<div id="cache">' . "\n";
				$echo .= $this->view_cache();
				$echo .= '</div>' . "\n\n";
			}
			
			if ( FB_WPDO_VIEW_HOOKS ) {
				$echo .= '<div id="hooks">' . "\n";
				$echo .= $this->view_hooks();
				$echo .= '</div>' . "\n\n";
			}
			
			if ( FB_WPDO_VIEW_CONSTANTS ) {
				$echo .= '<div id="constants">' . "\n";
				$echo .= $this->view_def_constants();
				$echo .= '</div>' . "\n\n";
			}
			
			if ( FB_WPDO_VIEW_ENQUEUED_STUFF ) {
				$echo .= '<div id="enqueue">' . "\n";
				$echo .= $this->view_enqueued_stuff();
				$echo .= '</div>' . "\n\n";
			}
			
			if ( FB_WPDO_VIEW_HOOK_TABLE ) {
				$echo .= '<div id="hook_table">' . "\n";
				$echo .= $this->instrument_hooks();
				$echo .= '</div>' . "\n\n";
			}
			
			if ( $debug_queries_on ) {
				$echo .= '<div id="queries">' . "\n";
				$echo .= $debug_queries->get_queries();
				$echo .= '</div>' . "\n\n";
			}
				
			$echo .= '</div>' . "\n\n";
				
			$echo .= '</div>' . "\n";
			
			if ( $view )
				echo $echo;
			else
				return $echo;
		}
		
		
		// add user rights
		function activate() {
			global $wp_roles;
			
			$wp_roles->add_cap( 'administrator', 'DebugObjects' );
		}
		
		
		// delete user rights
		function deactivate() {
			global $wp_roles, $wpdb;;
			
			$wp_roles->remove_cap( 'administrator', 'DebugObjects' );
			// remove hook table
			$table = $wpdb->prefix . 'hook_list';
			$wpdb->query( "DROP TABLE IF EXISTS $table" );
		}
		
		
		// function for WP < 2.8
		function plugins_url( $path = '', $plugin = '' ) {
			if ( function_exists( 'is_ssl' ) )
				$scheme = ( is_ssl() ? 'https' : 'http' );
			else
				$scheme = 'http';
			$url = WP_PLUGIN_URL;
			if ( 0 === strpos($url, 'http' ) ) {
				if ( function_exists( 'is_ssl' ) && is_ssl() )
					$url = str_replace( 'http://', "{$scheme}://", $url );
			}
		
			if ( !empty($plugin) && is_string($plugin) ) {
				$folder = dirname(plugin_basename($plugin));
				if ( ' . ' != $folder)
					$url .= '/' . ltrim($folder, '/' );
			}
		
			if ( !empty($path) && is_string($path) && strpos($path, ' .. ' ) === FALSE )
				$url .= '/' . ltrim($path, '/' );
		
			return apply_filters( 'plugins_url', $url, $path, $plugin);
		}
		
		
		// echo in frontend
		function wp_footer() {
			
			if ( !current_user_can( 'DebugObjects' ) )
				return;
			
			$echo  = '';
			$echo .= $this->get_debug_objects();
			
			return $echo;
		}
	
	} // end class
	
	$debug_objects = new Debug_Objects();
} // end if class exists
?>
