<?php
/*
Plugin Name: Easy Digital Downloads - Cross-sell & Upsell
Plugin URI: http://sumobi.com/shop/edd-cross-sell-and-upsell/
Description: Increase sales and customer retention by Cross-selling and Upselling to your customers
Version: 1.1
Author: Andrew Munro, Sumobi
Author URI: http://sumobi.com/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Constants
 *
 * @since 1.0
*/
if ( !defined( 'EDD_CSAU_VERSION' ) )
	define( 'EDD_CSAU_VERSION', '1.1' );

if ( !defined( 'EDD_CSAU_URL' ) )
	define( 'EDD_CSAU_URL', plugin_dir_url( __FILE__ ) );

if ( !defined( 'EDD_CSAU_DIR' ) )
	define( 'EDD_CSAU_DIR', plugin_dir_path( __FILE__ ) );


/**
 * Check if EDD is loaded
 *
 * @since 1.1
*/
function edd_csau_is_edd_active() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	// if EDD active?
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {

		// is this plugin active?
		if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
			// deactivate the plugin
	 		deactivate_plugins( plugin_basename( __FILE__ ) );
	 		// unset activation notice
	 		unset( $_GET[ 'activate' ] );
	 		// display notice
	 		add_action( 'admin_notices', 'edd_csau_edd_notice' );
		}
		
	}
}
add_action( 'plugins_loaded', 'edd_csau_is_edd_active' );

/**
 * Show notice if this plugin is activated before Easy Digital Downloads
 *
 * @since 1.1
*/
function edd_csau_edd_notice() {
?>
	<div class="updated">
		<p><?php printf( __( 'EDD Cross-sell &amp; Upsell requires Easy Digital Downloads. Please install &amp; activate Easy Digital Downloads and try again.', 'edd-csau' ) ); ?></p>
	</div>
<?php 
}


/**
 * Includes
 *
 * @since 1.0
*/
function edd_csau_setup() {

	if ( ! class_exists( 'Easy_Digital_Downloads' ) )
		return;

	include_once( EDD_CSAU_DIR . 'includes/template-functions.php' );
	include_once( EDD_CSAU_DIR . 'includes/cart-functions.php' );
	include_once( EDD_CSAU_DIR . 'includes/payment-actions.php' );
	include_once( EDD_CSAU_DIR . 'includes/payment-functions.php' );
	include_once( EDD_CSAU_DIR . 'includes/functions.php' );
	include_once( EDD_CSAU_DIR . 'includes/scripts.php' );

	if ( is_admin() ) {
		include_once( EDD_CSAU_DIR . 'includes/reports.php' );
		include_once( EDD_CSAU_DIR . 'includes/logs.php' );
		include_once( EDD_CSAU_DIR . 'includes/metabox.php' );
		include_once( EDD_CSAU_DIR . 'includes/admin-settings.php' );
		include_once( EDD_CSAU_DIR . 'includes/contextual-help.php' );
		include_once( EDD_CSAU_DIR . 'includes/dashboard-columns.php' );
		include_once( EDD_CSAU_DIR . 'includes/view-order-details.php' );
		include_once( EDD_CSAU_DIR . 'includes/export-functions.php' );
	}
}
add_action( 'plugins_loaded', 'edd_csau_setup', 11 );

/**
 * Internationalization
 *
 * @since 1.0
 */
function edd_csau_textdomain() {
	// Set filter for plugin's languages directory
	$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$lang_dir = apply_filters( 'edd_csau_languages_directory', $lang_dir );

	// Traditional WordPress plugin locale filter
	$locale        = apply_filters( 'plugin_locale',  get_locale(), 'edd-csau' );
	$mofile        = sprintf( '%1$s-%2$s.mo', 'edd-csau', $locale );

	// Setup paths to current locale file
	$mofile_local  = $lang_dir . $mofile;
	$mofile_global = WP_LANG_DIR . '/edd-csau/' . $mofile;

	if ( file_exists( $mofile_global ) ) {
		// Look in global /wp-content/languages/edd-csau-register folder
		load_textdomain( 'edd-csau', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) {
		// Look in local /wp-content/plugins/edd-csau/languages/ folder
		load_textdomain( 'edd-csau', $mofile_local );
	} else {
		// Load the default language files
		load_plugin_textdomain( 'edd-csau', false, $lang_dir );
	}
}
add_action( 'after_setup_theme', 'edd_csau_textdomain' );

/**
 * Updater
 *
 * @since 1.0
*/

// Load the EDD license handler only if not already loaded. Must be placed in the main plugin file
if( ! class_exists( 'EDD_License' ) )
	include( dirname( __FILE__ ) . '/includes/EDD_License_Handler.php' );


// Instantiate the licensing / updater. Must be placed in the main plugin file
$license = new EDD_License( __FILE__, 'EDD Cross-sell and Upsell', EDD_CSAU_VERSION, 'Andrew Munro' );

/**
 * Plugin action links
 *
 * @since 1.1
*/
function edd_csau_action_links( $links, $pluginLink ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions' ) . '">' . __( 'Settings', 'edd-csau' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'edd_csau_action_links', 10, 2 );