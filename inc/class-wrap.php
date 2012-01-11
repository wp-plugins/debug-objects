<?php
/**
 * Add area for content
 *
 * @package     Debug Objects
 * @subpackage  Markup and Hooks for include content
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */

if ( ! class_exists( 'Debug_Objects_Wrap' ) ) {
	
	class Debug_Objects_Wrap extends Debug_Objects {
		
		/**
		 * Include class in plugin and init all functions
		 * 
		 * @access  public
		 * @since   2.0.0
		 * @return  void
		 */
		public static function init() {
			// not enough right - back
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			$options = Debug_Objects_Settings :: return_options();
			
			self::set_cookie_control();
			// check for output on frontend
			if ( isset( $options['frontend'] ) && '1' === $options['frontend']
				 || self::debug_control()
				 ) {
				add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts') );
				add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles') );
				add_action( 'wp_footer', array( __CLASS__, 'get_content' ), 9999 );
			}
			// check for output on backend
			if ( isset( $options['backend'] ) && '1' === $options['backend']
				 || self::debug_control()
				 ) {
				add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_styles') );
				add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts') );
				add_action( 'admin_footer', array( __CLASS__, 'get_content' ), 9999 );
			}
		}
		
		/**
		 * Check for url param to view output
		 * 
		 * @access  public
		 * @since   2.0.1
		 * @return  $debug boolean
		 */
		public function debug_control() {
			// Debug via _GET Param on URL
			if ( ! isset( $_GET['debug'] ) )
				$debug = FALSE;
			else
				$debug = TRUE;
			
			if ( ! $debug )
				$debug = self::get_cookie_control( $debug );
			
			return (bool) $debug;
		}
		
		/**
		 * Check for cookie to view output
		 * 
		 * @access  public
		 * @since   2.0.1
		 * @return  $debug boolean
		 */
		public function get_cookie_control( $debug ) {
			
			if ( ! isset( $_COOKIE[parent :: get_plugin_data() . '_cookie'] ) )
				return FALSE;
			
			if ( 'Debug_Objects_True' === $_COOKIE[parent :: get_plugin_data() . '_cookie'] )
				$debug = TRUE;
			
			return (bool) $debug;
		}
		
		/**
		 * Init cookie and control the live time
		 * 
		 * @access  public
		 * @since   2.0.1
		 * @return  void
		 */
		public function set_cookie_control() {
			
			if ( ! isset( $_GET['debugcookie'] ) )
				return;
			
			if ( $_GET['debugcookie'] ) {
				$cookie_live = time() + 60 * 60 * 24 * intval( $_GET['debugcookie'] ); // days
				setcookie( parent :: get_plugin_data() . '_cookie', 'Debug_Objects_True', $cookie_live, COOKIEPATH, COOKIE_DOMAIN );
			}
			
			if ( 0 == intval( $_GET['debugcookie'] ) )
				setcookie( parent :: get_plugin_data() . '_cookie', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
		}
		
		/**
		 * Enqueue stylesheets on frontend or backend
		 * 
		 * @access  public
		 * @since   2.0.0
		 * @return  void
		 */
		public static function enqueue_styles() {
			
			$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.dev' : '';
			
			wp_enqueue_style(
				parent :: get_plugin_data() . '-jquery-ui-all-css',
				str_replace( '/inc', '', plugins_url( '/css/ui.all.css', __FILE__ ) )
			);
			
			wp_enqueue_style(
				parent :: get_plugin_data() . '_style',
				str_replace( '/inc', '', plugins_url( '/css/style' . $suffix. '.css', __FILE__ ) ),
				parent :: get_plugin_data() . '-jquery-ui-all-css',
				FALSE,
				'screen'
			);
		}
		
		/**
		 * Enqueue scripts on frontend or backend
		 * 
		 * @access  public
		 * @since   2.0.0
		 * @return  void
		 */
		public static function enqueue_scripts( $where ) {
			
			$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.dev' : '';
			
			wp_enqueue_script(
				parent :: get_plugin_data() . '_script', 
				str_replace( '/inc', '', plugins_url( '/js/debug_objects' . $suffix. '.js', __FILE__ ) ), 
				array( 'jquery-ui-tabs', parent :: get_plugin_data() . '_cookie_script' ),
				'',
				TRUE
			);
			wp_enqueue_script(
				parent :: get_plugin_data() . '_cookie_script', 
				str_replace( '/inc', '', plugins_url( '/js/jquery.cookie.js', __FILE__ ) ), 
				array( 'jquery' ),
				'',
				TRUE
			);
		}
		
		/**
		 * Echo markup for view output
		 * 
		 * @access  public
		 * @since   2.0.0
		 * @return  string
		 */
		public static function get_content() {
			?>
			<div id="debugobjects">
				<div id="debugobjectstabs">
					<ul>
					<?php
					/**
					 *  use this filter for include new tabs with content
					$tabs[] = array( 
						'tab' => __( 'Conditional Tags', parent :: get_plugin_data() ),
						'function' => array( __CLASS__, 'get_conditional_tags' )
					);
					*/
					$tabs = apply_filters( 'debug_objects_tabs', $tabs = array() );
					
					foreach( $tabs as $tab ) {
						echo '<li><a href="#' . htmlentities2( tag_escape( $tab['tab'] ) ) . '">' . esc_attr( $tab['tab'] ) . '</a></li>';
					}
					?>
					</ul>
				
					<?php
					foreach( $tabs as $tab ) {
						echo '<div id="' . htmlentities2( tag_escape( $tab['tab'] ) ) . '">';
								// for php version 5.2 and only static method
								call_user_func( array( $tab['function'][0], $tab['function'][1] ) );
								// only with php 5.3 and higher
								//$tab['function'][0] :: $tab['function'][1]();
							do_action( 'debug_objects_function' );
						echo '</div>';
					}
					?>
				</div> <!-- end id=debugobjectstabs -->
			</div> <!-- end id=debugobjects -->
			<br style="clear: both;"/>
			<?php
		}
		
	} // end class
}// end if class exists
