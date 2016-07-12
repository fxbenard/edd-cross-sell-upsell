<?php
/*
Plugin Name: Easy Digital Downloads - Cross-sell & Upsell
Plugin URI: http://sumobi.com/shop/edd-cross-sell-and-upsell/
Description: Increase sales and customer retention by Cross-selling and Upselling to your customers
Version: 1.1.4
Author: Andrew Munro, Sumobi
Author URI: http://sumobi.com/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'EDD_Cross_Sell_And_Upsell' ) ) {

	class EDD_Cross_Sell_And_Upsell {

		/**
		 * Holds the instance
		 *
		 * Ensures that only one instance exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property property that holds the singleton instance.
		 *
		 * @var object
		 * @static
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * Plugin Version
		 */
		private $version = '1.1.4';

		/**
		 * Plugin Title
		 */
		public $title = 'EDD Cross-sell and Upsell';

		/**
		 * Main Instance
		 *
		 * Ensures that only one instance exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0
		 *
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Cross_Sell_And_Upsell ) ) {
				self::$instance = new EDD_Cross_Sell_And_Upsell;
				self::$instance->setup_globals();
				self::$instance->includes();
				self::$instance->setup_actions();
				self::$instance->licensing();
				self::$instance->load_textdomain();
			}

			return self::$instance;
		}

		/**
		 * Constructor Function
		 *
		 * @since 1.0
		 * @access private
		 */
		private function __construct() {
			self::$instance = $this;
		}

		/**
		 * Globals
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function setup_globals() {

			// paths
			$this->file         = __FILE__;
			$this->basename     = apply_filters( 'edd_csau_plugin_basenname', plugin_basename( $this->file ) );
			$this->plugin_dir   = apply_filters( 'edd_csau_plugin_dir_path',  plugin_dir_path( $this->file ) );
			$this->plugin_url   = apply_filters( 'edd_csau_plugin_dir_url',   plugin_dir_url ( $this->file ) );

			// includes
			$this->includes_dir = apply_filters( 'edd_csau_includes_dir', trailingslashit( $this->plugin_dir . 'includes'  ) );
			$this->includes_url = apply_filters( 'edd_csau_includes_url', trailingslashit( $this->plugin_url . 'includes'  ) );

			// constants
			if ( ! defined( 'EDD_CSAU_VERSION' ) ) {
				define( 'EDD_CSAU_VERSION', $this->version );
			}

			if ( ! defined( 'EDD_CSAU_URL' ) ) {
				define( 'EDD_CSAU_URL', plugin_dir_url( __FILE__ ) );
			}

			if ( ! defined( 'EDD_CSAU_DIR' ) ) {
				define( 'EDD_CSAU_DIR', plugin_dir_path( __FILE__ ) );
			}

		}

		/**
		 * Setup the default hooks and actions
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function setup_actions() {

			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta' ), 10, 2 );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'settings_link' ), 10, 2 );

			do_action( 'edd_csau_setup_actions' );

		}

		/**
		 * Licensing
		 *
		 * @since 1.0
		*/
		private function licensing() {
			// check if EDD_License class exists
			if ( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, $this->title, $this->version, 'Andrew Munro' );
			}
		}


		/**
		 * Loads the plugin language files
		 *
		 * @access public
		 * @since 1.0.4
		 * @return void
		 */
		public function load_textdomain() {

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
				// Look in global /wp-content/languages/edd-csau/ folder
				load_textdomain( 'edd-csau', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-csau/languages/ folder
				load_textdomain( 'edd-csau', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-csau', false, $lang_dir );
			}
		}



		/**
		 * Include required files.
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function includes() {

			include_once( EDD_CSAU_DIR . 'includes/template-functions.php' );
			include_once( EDD_CSAU_DIR . 'includes/cart-functions.php' );
			include_once( EDD_CSAU_DIR . 'includes/payment-actions.php' );
			include_once( EDD_CSAU_DIR . 'includes/payment-functions.php' );
			include_once( EDD_CSAU_DIR . 'includes/functions.php' );
			include_once( EDD_CSAU_DIR . 'includes/scripts.php' );

			do_action( 'edd_csau_include_files' );

			if ( is_admin() ) {

				include_once( EDD_CSAU_DIR . 'includes/reports.php' );
				include_once( EDD_CSAU_DIR . 'includes/logs.php' );
				include_once( EDD_CSAU_DIR . 'includes/metabox.php' );
				include_once( EDD_CSAU_DIR . 'includes/admin-settings.php' );
				include_once( EDD_CSAU_DIR . 'includes/contextual-help.php' );
				include_once( EDD_CSAU_DIR . 'includes/dashboard-columns.php' );
				include_once( EDD_CSAU_DIR . 'includes/view-order-details.php' );
				include_once( EDD_CSAU_DIR . 'includes/export-functions.php' );

				do_action( 'edd_csau_include_admin_files' );

			}


		}

		/**
		 * Plugin settings link
		 *
		 * @since 1.1
		*/
		public function settings_link( $links ) {
			$plugin_links = array(
				'<a href="' . admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions' ) . '">' . __( 'Settings', 'edd-csau' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Modify plugin metalinks
		 *
		 * @access      public
		 * @since       1.1.1
		 * @param       array $links The current links array
		 * @param       string $file A specific plugin table entry
		 * @return      array $links The modified links array
		 */
		public function plugin_meta( $links, $file ) {
		    if ( $file == plugin_basename( __FILE__ ) ) {
		        $plugins_link = array(
		            '<a title="'. __( 'View more plugins for Easy Digital Downloads by Sumobi', 'edd-csau' ) .'" href="https://easydigitaldownloads.com/blog/author/andrewmunro/?ref=166" target="_blank">' . __( 'Author\'s EDD plugins', 'edd-csau' ) . '</a>'
		        );

		        $links = array_merge( $links, $plugins_link );
		    }

		    return $links;
		}

	}

	/**
	 * Loads a single instance
	 *
	 * This follows the PHP singleton design pattern.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * @example <?php $edd_cross_sell_and_upsell = edd_cross_sell_and_upsell_load(); ?>
	 *
	 * @since 1.0
	 *
	 * @see EDD_Cross_Sell_And_Upsell::get_instance()
	 *
	 * @return object Returns an instance of the EDD_Cross_Sell_And_Upsell class
	 */
	function edd_cross_sell_and_upsell_load() {

	    if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {

	        if ( ! class_exists( 'EDD_Extension_Activation' ) ) {
	            require_once 'includes/class-activation.php';
	        }

	        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
	        $activation = $activation->run();

	    } else {
	        return EDD_Cross_Sell_And_Upsell::get_instance();
	    }
	}
	add_action( 'plugins_loaded', 'edd_cross_sell_and_upsell_load', apply_filters( 'edd_csau_action_priority', 10 ) );

}
