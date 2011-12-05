<?php
/**
 * Add small screen with informations for different stuff from php, globals and WP
 *
 * @package     Debug Objects
 * @subpackage  Different Stuff
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */

if ( ! class_exists( 'Debug_Objects_Different_Stuff' ) ) {
	add_action( 'admin_init', array( 'Debug_Objects_Different_Stuff', 'init' ) );
	
	class Debug_Objects_Different_Stuff extends Debug_Objects {
		
		public static function init() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( __CLASS__, 'get_conditional_tab' ) );
		}
		
		public static function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'PHP, Globals &amp; WP', parent :: get_plugin_data() ),
				'function' => array( __CLASS__, 'get_different_stuff' )
			);
			
			return $tabs;
		}
		
		public static function get_different_stuff( $echo = TRUE ) {
			global $locale;
			
			if ( defined( 'WPLANG' ) )
				$locale = WPLANG;
			if ( empty($locale) )
				$locale = 'en_US';
				
			$memory_usage = function_exists( 'memory_get_usage' ) ? round(memory_get_usage() / 1024 / 1024, 2) : 0;
			$memory_limit = (int) ini_get( 'memory_limit' ) ;
			
			if ( ! empty($memory_usage) && ! empty($memory_limit) )
				$memory_percent = round( $memory_usage / $memory_limit * 100, 0 );
			
			$oskey = $_SERVER['HTTP_USER_AGENT'];
			//Operating-System scan start
			if ( preg_match( '=WIN=i', $oskey) ) { //Windows
				if (preg_match( '=NT=i', $oskey)) {
					if (preg_match( '=5.1=', $oskey) ) {
						$os = __( 'Windows XP', parent :: get_plugin_data() );
					} elseif(preg_match( '=5.0=', $oskey) ) {//Windows 2000
						$os = __( 'Windows 2000', parent :: get_plugin_data() );
					}
				} else {
					if (preg_match( '=ME=', $oskey) ) { //Windows ME
						$os = __( 'Windows ME', parent :: get_plugin_data() );
					} elseif(preg_match( '=98=', $oskey) ) { //Windows 98
						$os = __( 'Windows 98', parent :: get_plugin_data() );
					} elseif(preg_match( '=95=', $oskey) ) { //Windows 95
						$os = __( 'Windows 95', parent :: get_plugin_data() );}
				}
			} elseif (preg_match( '=MAC=i', $oskey) ) { //Macintosh
				$os = __( 'Macintosh', parent :: get_plugin_data() );
			} elseif (preg_match( '=LINUX=i', $oskey) ) { //Linux
				$os = __( 'Linux', parent :: get_plugin_data() );
			} //Operating-System scan end
			
			if ( ! defined( 'AUTOSAVE_INTERVAL' ) )
				$autosave_interval = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'AUTOSAVE_INTERVAL' ) )
				$autosave_interval = __( 'OFF', parent :: get_plugin_data() );
			else
				$autosave_interval = AUTOSAVE_INTERVAL . __( 's', parent :: get_plugin_data() );
			
			if ( ! defined( 'WP_POST_REVISIONS' ) )
				$post_revisions = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'WP_POST_REVISIONS' ) )
				$post_revisions = __( 'OFF', parent :: get_plugin_data() );
			else
				$post_revisions = WP_POST_REVISIONS;
			
			if ( ! defined( 'SAVEQUERIES' ) )
				$savequeries = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'SAVEQUERIES' ) )
				$savequeries = __( 'OFF', parent :: get_plugin_data() );
			elseif ( SAVEQUERIES == 1 )
				$savequeries = __( 'ON', parent :: get_plugin_data() );
			
			if ( ! defined( 'WP_DEBUG' ) )
				$debug = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'WP_DEBUG' ) )
				$debug = __( 'OFF', parent :: get_plugin_data() );
			elseif ( WP_DEBUG == 1 )
				$debug = __( 'ON', parent :: get_plugin_data() );
				
			if ( ! defined( 'FORCE_SSL_LOGIN' ) )
				$ssl_login = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'FORCE_SSL_LOGIN' ) )
				$ssl_login = __( 'OFF', parent :: get_plugin_data() );
			elseif ( FORCE_SSL_LOGIN == 1 )
				$ssl_login = __( 'ON', parent :: get_plugin_data() );
			
			if ( ! defined( 'CONCATENATE_SCRIPTS' ) )
				$concatenate_scripts = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'CONCATENATE_SCRIPTS' ) )
				$concatenate_scripts = __( 'OFF', parent :: get_plugin_data() );
			elseif ( CONCATENATE_SCRIPTS == 1 )
					$concatenate_scripts = __( 'ON', parent :: get_plugin_data() );
			
			if ( ! defined( 'COMPRESS_SCRIPTS' ) )
				$compress_scripts = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'COMPRESS_SCRIPTS' ) )
				$compress_scripts = __( 'OFF', parent :: get_plugin_data() );
			elseif ( COMPRESS_SCRIPTS == 1 )
				$compress_scripts = __( 'ON', parent :: get_plugin_data() );
			
			if ( ! defined( 'COMPRESS_CSS' ) )
				$compress_css = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'COMPRESS_CSS' ) )
				$compress_css = __( 'OFF', parent :: get_plugin_data() );
			elseif ( COMPRESS_CSS == 1 )
				$compress_css = __( 'ON', parent :: get_plugin_data() );
			
			if ( ! defined( 'ENFORCE_GZIP' ) )
				$enforce_gzip = __( 'Undefined', parent :: get_plugin_data() );
			elseif ( ! constant( 'ENFORCE_GZIP' ) )
				$enforce_gzip = __( 'OFF', parent :: get_plugin_data() );
			elseif ( ENFORCE_GZIP == 1 )
				$enforce_gzip = __( 'ON', parent :: get_plugin_data() );
			
			$output  = '';
			$output .= "\n" . '<h4>' . __( 'PHP Version &amp; System', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			
			$output .= '<li>' . __( 'PHP version:', parent :: get_plugin_data() ) . ' ' . PHP_VERSION . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Server:', parent :: get_plugin_data() ) . ' ' . substr( $_SERVER['SERVER_SOFTWARE'], 0, 14 ) . '</li>' . "\n";
			$output .= '<li>' . __( 'Server SW:', parent :: get_plugin_data() ) . ' ' . $_SERVER['SERVER_SOFTWARE'] . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'http User Agent:', parent :: get_plugin_data() ) . ' ' . $_SERVER['HTTP_USER_AGENT'] . '</li>' . "\n";
			$output .= '<li>' . __( 'OS version:', parent :: get_plugin_data() ) . ' ' . $os . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Memory usage:', parent :: get_plugin_data() ) . ' ' . $memory_usage . ' MByte</li>' . "\n";
			$output .= '<li>' . __( 'Memory limit, PHP Configuration', parent :: get_plugin_data() ) . ' : ' . $memory_limit . ' MByte</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Memory percent:', parent :: get_plugin_data() ) . ' ' . $memory_percent . ' % of 100%</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			$output .= "\n" . '<h4>' . __( 'WordPress Informations', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li>' . __( 'Version:', parent :: get_plugin_data() ) . ' ' . get_bloginfo( 'version' ) . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Language, constant', parent :: get_plugin_data() ) . ' <code>WPLANG</code>: ' . $locale . '</li>' . "\n";
			$output .= '<li>' . __( 'Language folder, constant', parent :: get_plugin_data() ) . ' <code>WP_LANG_DIR</code>: ' . WP_LANG_DIR . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Content URL, constant', parent :: get_plugin_data() ) . ' <code>WP_CONTENT_URL</code>: ' . WP_CONTENT_URL . '</li>' . "\n";
			$output .= '<li>' . __( 'Content folder, constant', parent :: get_plugin_data() ) . ' <code>WP_CONTENT_DIR</code>: ' . WP_CONTENT_DIR . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Memory limit, constant', parent :: get_plugin_data() ) . ' <code>WP_MEMORY_LIMIT</code>: ' . WP_MEMORY_LIMIT . ' Byte</li>' . "\n";
			$output .= '<li>' . __( 'Post revision, constant', parent :: get_plugin_data() ) . ' <code>WP_POST_REVISIONS</code>: ' . $post_revisions . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Save queries, constant', parent :: get_plugin_data() ) . ' <code>SAVEQUERIES</code>: ' . $savequeries . '</li>' . "\n";
			$output .= '<li>' . __( 'Debug option, constant', parent :: get_plugin_data() ) . ' <code>WP_DEBUG</code>: ' . $debug . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'SSL Login, constant', parent :: get_plugin_data() ) . ' <code>FORCE_SSL_LOGIN</code>: ' . $ssl_login . '</li>' . "\n";
			$output .= '<li>' . __( 'Concatenate scripts, constant', parent :: get_plugin_data() ) . ' <code>CONCATENATE_SCRIPTS</code>: ' . $concatenate_scripts . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Compress scripts, constant', parent :: get_plugin_data() ) . ' <code>COMPRESS_SCRIPTS</code>: ' . $compress_scripts . '</li>' . "\n";
			$output .= '<li>' . __( 'Compress stylesheet, constant', parent :: get_plugin_data() ) . ' <code>COMPRESS_CSS</code>: ' . $compress_css . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Enforce GZIP, constant', parent :: get_plugin_data() ) . ' <code>ENFORCE_GZIP</code>: ' . $enforce_gzip . '</li>' . "\n";
			$output .= '<li>' . __( 'Autosave interval, constant', parent :: get_plugin_data() ) . ' <code>AUTOSAVE_INTERVAL</code>: ' . $autosave_interval . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			if ( ! defined( 'COOKIE_DOMAIN' ) )
				$cookie_domain = __( 'Undefined', parent :: get_plugin_data() );
			else
				$cookie_domain = COOKIE_DOMAIN;
				
			if ( ! defined( 'COOKIEPATH' ) )
				$cookiepath = __( 'Undefined', parent :: get_plugin_data() );
			else
				$cookiepath = COOKIEPATH;
				
			if ( ! defined( 'SITECOOKIEPATH' ) )
				$sitecookiepath = __( 'Undefined', parent :: get_plugin_data() );
			else
				$sitecookiepath = SITECOOKIEPATH;
				
			if ( ! defined( 'PLUGINS_COOKIE_PATH' ) )
				$plugins_cookie_path = __( 'Undefined', parent :: get_plugin_data() );
			else
				$plugins_cookie_path = PLUGINS_COOKIE_PATH;
				
			if ( ! defined( 'ADMIN_COOKIE_PATH' ) )
				$admin_cookie_path = __( 'Undefined', parent :: get_plugin_data() );
			else
				$admin_cookie_path = ADMIN_COOKIE_PATH;
			
			$output .= "\n" . '<h4>' . __( 'WordPress Cookie Informations', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'Cookie domain, constant', parent :: get_plugin_data() ) . ' <code>COOKIE_DOMAIN</code>: ' . $cookie_domain . '</li>' . "\n";
			$output .= '<li>' . __( 'Cookie path, constant', parent :: get_plugin_data() ) . ' <code>COOKIEPATH</code>: ' . $cookiepath . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Site cookie path, constant', parent :: get_plugin_data() ) . ' <code>SITECOOKIEPATH</code>: ' . $sitecookiepath . '</li>' . "\n";
			$output .= '<li>' . __( 'Plugin cookie path, constant', parent :: get_plugin_data() ) . ' <code>PLUGINS_COOKIE_PATH</code>: ' . $plugins_cookie_path . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Admin cookie path, constant', parent :: get_plugin_data() ) . ' <code>ADMIN_COOKIE_PATH</code>: ' . $admin_cookie_path . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			if ( ! defined( 'FS_CHMOD_FILE' ) )
				$fs_chmod_file = __( 'Undefined', parent :: get_plugin_data() );
			else
				$fs_chmod_file = FS_CHMOD_FILE;
				
			if ( ! defined( 'FS_CHMOD_DIR' ) )
				$fs_chmod_dir = __( 'Undefined', parent :: get_plugin_data() );
			else
				$fs_chmod_dir = FS_CHMOD_DIR;
			
			$output .= "\n" . '<h4>' . __( 'WordPress File Permissions Informations', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'File Permissions, constant', parent :: get_plugin_data() ) . ' <code>FS_CHMOD_FILE</code>: ' . $fs_chmod_file . '</li>' . "\n";
			$output .= '<li>' . __( 'DIR Permissions, constant', parent :: get_plugin_data() ) . ' <code>FS_CHMOD_DIR</code>: ' . $fs_chmod_dir . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			if ( ! defined( 'CUSTOM_USER_TABLE' ) )
				$custom_user_table = __( 'Undefined', parent :: get_plugin_data() );
			else
				$custom_user_table = CUSTOM_USER_TABLE;
				
			if ( ! defined( 'CUSTOM_USER_META_TABLE' ) )
				$custom_user_meta_table = __( 'Undefined', parent :: get_plugin_data() );
			else
				$custom_user_meta_table = CUSTOM_USER_META_TABLE;
			
			$output .= "\n" . '<h4>' . __( 'WordPress Custom User &amp; Usermeta Tables', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'Custom User Table, constant', parent :: get_plugin_data() ) . ' <code>CUSTOM_USER_TABLE</code>: ' . $custom_user_table . '</li>' . "\n";
			$output .= '<li>' . __( 'Cookie path, constant', parent :: get_plugin_data() ) . ' <code>CUSTOM_USER_META_TABLE</code>: ' . $custom_user_meta_table . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			if ( ! defined( 'FS_METHOD' ) )
				$fs_method = __( 'Undefined', parent :: get_plugin_data() );
			else
				$fs_method = FS_METHOD;
				
			if ( ! defined( 'FTP_BASE' ) )
				$ftp_base = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_base = FTP_BASE;
			
			if ( ! defined( 'FTP_CONTENT_DIR' ) )
				$ftp_content_dir = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_content_dir = FTP_CONTENT_DIR;
				
			if ( ! defined( 'FTP_PLUGIN_DIR' ) )
				$ftp_plugin_dir = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_plugin_dir = FTP_PLUGIN_DIR;
			
			if ( ! defined( 'FTP_PUBKEY' ) )
				$ftp_pubkey = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_pubkey = FTP_PUBKEY;
				
			if ( ! defined( 'FTP_PRIVKEY' ) )
				$ftp_privkey = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_privkey = FTP_PRIVKEY;
			
			if ( ! defined( 'FTP_USER' ) )
				$ftp_user = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_user = FTP_USER;
				
			if ( ! defined( 'FTP_PASS' ) )
				$ftp_pass = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_pass = FTP_PASS;
			
			if ( ! defined( 'FTP_HOST' ) )
				$ftp_host = __( 'Undefined', parent :: get_plugin_data() );
			else
				$ftp_host = FTP_HOST;
			
			$output .= "\n" . '<h4>' . __( 'WordPress FTP/SSH Informations', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'Forces the filesystem method, constant', parent :: get_plugin_data() ) . ' <code>FS_METHOD</code> (<code>direct</code>, <code>ssh</code>, <code>ftpext</code> or <code>ftpsockets</code>): ' . $fs_method . '</li>' . "\n";
			$output .= '<li>' . __( 'Path to root install directory, constant', parent :: get_plugin_data() ) . ' <code>FTP_BASE</code>: ' . $ftp_base . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Absolute path to wp-content directory, constant', parent :: get_plugin_data() ) . ' <code>FTP_CONTENT_DIR</code>: ' . $ftp_content_dir . '</li>' . "\n";
			$output .= '<li>' . __( 'Absolute path to plugin directory, constant', parent :: get_plugin_data() ) . ' <code>FTP_PLUGIN_DIR</code>: ' . $ftp_plugin_dir . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Absolute path to SSH public key, constant', parent :: get_plugin_data() ) . ' <code>FTP_PUBKEY</code>: ' . $ftp_pubkey . '</li>' . "\n";
			$output .= '<li>' . __( 'dorector path to SSH private key, constant', parent :: get_plugin_data() ) . ' <code>FTP_PRIVKEY</code>: ' . $ftp_privkey . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'FTP or SSH username, constant', parent :: get_plugin_data() ) . ' <code>FTP_USER</code>: ' . $ftp_user . '</li>' . "\n";
			$output .= '<li>' . __( 'FTP or SSH password, constant', parent :: get_plugin_data() ) . ' <code>FTP_PASS</code>: ' . $ftp_pass . '</li>' . "\n";
			$output .= '<li class="alternate">' . __( 'Hostname, constant', parent :: get_plugin_data() ) . ' <code>FTP_HOST</code>: ' . $ftp_host . '</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			$output .= "\n" . '<h4>' . __( 'WordPress Query Informations', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li class="alternate">' . __( 'Queries:', parent :: get_plugin_data() ) . ' ' . get_num_queries() . 'q';
			$output .= '</li>' . "\n";
			$output .= '<li>' . __( 'Timer stop:', parent :: get_plugin_data() ) . ' ' . timer_stop() . 's</li>' . "\n";
			$output .= '</ul>' . "\n";
			
			// PHP_SELF
			if ( ! isset( $_SERVER['PATH_INFO'] ) )
				$_SERVER['PATH_INFO'] = __( 'Undefined', parent :: get_plugin_data() );
			if ( ! isset( $_SERVER['QUERY_STRING'] ) )
				$_SERVER['QUERY_STRING'] = __( 'Undefined', parent :: get_plugin_data() );
			if ( ! isset( $_SERVER['SCRIPT_FILENAME'] ) )
				$_SERVER['SCRIPT_FILENAME'] = __( 'Undefined', parent :: get_plugin_data() );
			if ( ! isset( $_SERVER['PHP_SELF'] ) )
				$_SERVER['PHP_SELF'] = __( 'Undefined', parent :: get_plugin_data() );
			if ( ! isset( $_GET['error'] ) )
				$_GET['error'] = __( 'Undefined', parent :: get_plugin_data() );
			
			$output .= "\n" . '<h4>' . __( 'Selected server and execution environment information', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul>' . "\n";
			$output .= '<li>' . __( 'PATH_INFO:', parent :: get_plugin_data() ) . ' ' . $_SERVER['PATH_INFO'] . '</li>';
			$output .= '<li class="alternate">' . __( 'REQUEST_URI:', parent :: get_plugin_data() ) . ' ' . $_SERVER['REQUEST_URI'] . '</li>';
			$output .= '<li>' . __( 'QUERY_STRING:', parent :: get_plugin_data() ) . ' ' . $_SERVER['QUERY_STRING'] . '</li>';
			$output .= '<li class="alternate">' . __( 'SCRIPT_NAME:', parent :: get_plugin_data() ) . ' ' . $_SERVER['SCRIPT_NAME'] . '</li>';
			$output .= '<li>' . __( 'SCRIPT_FILENAME:', parent :: get_plugin_data() ) . ' ' . $_SERVER['SCRIPT_FILENAME'] . '</li>';
			$output .= '<li class="alternate">' . __( 'PHP_SELF:', parent :: get_plugin_data() ) . ' ' . $_SERVER['PHP_SELF'] . '</li>';
			$output .= '<li>' . __( 'GET Error:', parent :: get_plugin_data() ) . ' ' . $_GET['error'] . '</li>';
			$output .= '<li class="alternate">' . __( 'FILE:', parent :: get_plugin_data() ) . ' ' . __FILE__ . '</li>';
			$output .= '</ul>' . "\n";
			
			// Globals 
			$output .= "\n" . '<h4>' . __( 'HTTP GET variables', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul><li>' . "\n";
			if ( ! isset( $_GET ) || empty( $_GET ) )
				$output .= __( 'Undefined or empty', parent :: get_plugin_data() );
			else 
				$output .= var_export( $_GET, TRUE );
			$output .= '</li></ul>' . "\n";
			
			$output .= "\n" . '<h4>' . __( 'HTTP POST variables', parent :: get_plugin_data() ) . '</h4>' . "\n";
			$output .= '<ul><li>' . "\n";
			if ( ! isset( $_POST ) || empty( $_POST ) )
				$output .= __( 'Undefined or empty', parent :: get_plugin_data() );
			else 
				$output .= var_export( $_POST, TRUE );
			$output .= '</li></ul>' . "\n";
			
			if ( $echo )
				echo $output;
			else
				return $output;
		}
		
	} // end class
} // end if class exists