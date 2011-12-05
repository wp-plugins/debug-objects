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
	add_action( 'admin_init', array( 'Debug_Objects_Wrap', 'init' ) );
	
	class Debug_Objects_Wrap extends Debug_Objects {
		
		public static function init() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_action( 'admin_footer', array( __CLASS__, 'in_admin_footer' ) );
			
			add_action( 'admin_print_styles',  array( __CLASS__, 'enqueue_styles') );
			add_action( 'admin_print_scripts', array( __CLASS__, 'enqueue_scripts') );
		}
		
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
		
		public static function enqueue_scripts( $where ) {
			
			$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.dev' : '';
			
			wp_enqueue_script(
				parent :: get_plugin_data() . '_script', 
				str_replace( '/inc', '', plugins_url( '/js/debug_objects' . $suffix. '.js', __FILE__ ) ), 
				array( 'jquery-ui-tabs' ),
				'',
				TRUE
			);
		}
		
		public static function in_admin_footer() {
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
							$tab['function'][0] :: $tab['function'][1]();
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
