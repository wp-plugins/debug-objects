<?php
/**
 * Add small features for better use the debug-tools
 *
 * @package     Debug Objects
 * @subpackage  Features
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */
if ( ! class_exists( 'Debug_Objects_Feature' ) ) {
	//add_action( 'admin_init', array( 'Debug_Objects_Feature', 'init' ) );

	class Debug_Objects_Feature extends Debug_Objects {
		
		public static function init() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_action( 'admin_print_footer_scripts', array( __CLASS__, 'admin_bar_scrolltop' ) );
		}
		
		public static function admin_bar_scrolltop() {
			
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
		
	} // end class
}