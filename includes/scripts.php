<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Scripts
 *
 * @since 1.0
*/
function edd_csau_load_scripts() {
	
	global $edd_options;

	// only load stylesheet on single download pages or checkout page
	if ( is_singular( 'download' ) || ( isset( $edd_options['purchase_page'] ) && is_page( $edd_options[ 'purchase_page' ] ) ) ) {

		// load css file from child theme if it exists
		if ( file_exists( get_stylesheet_directory() . '/css/edd-csau.css' ) )
			wp_register_style( 'edd-csau-css', get_stylesheet_directory_uri() . 'assets/css/edd-csau.css', '', EDD_CSAU_VERSION, 'screen' );
		else
			wp_register_style( 'edd-csau-css', EDD_CSAU_URL . 'assets/css/edd-csau.css', '', EDD_CSAU_VERSION, 'screen' );
		
		wp_enqueue_style( 'edd-csau-css' );

	}

}
add_action( 'wp_enqueue_scripts', 'edd_csau_load_scripts' );

/**
 * JS for admin page to allow options to be visible
 *
 * @since 1.0
*/
function edd_csau_js() { 
	global $pagenow, $typenow;

	if ( 'download' == $typenow && in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) :
	?>
	<script>
		jQuery(document).ready(function ($) {

			$('.edd-csau-select').chosen({
				width: '100%'
			});

		});
	</script>
<?php endif; 
}
add_action( 'in_admin_footer', 'edd_csau_js' );