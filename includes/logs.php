<?php
/**
 * Logging
 *
 * @since 1.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Cross-sells Log View
 *
 * @since 1.1
 * @uses EDD_Cross_Sells_Log_Table::prepare_items()
 * @uses EDD_Cross_Sells_Log_Table::display()
 * @return void
 */
function edd_csau_logs_view_cross_sells() {
	include_once( EDD_CSAU_DIR . 'includes/class-logs-list-table.php' );

	$logs_table = new EDD_CSAU_Log_Table( 'cross_sell' );
	$logs_table->prepare_items();
	$logs_table->display();
}
add_action( 'edd_logs_view_cross_sells', 'edd_csau_logs_view_cross_sells' );

/**
 * Upsells Log View
 *
 * @since 1.1
 * @uses EDD_Upsells_Log_Table::prepare_items()
 * @uses EDD_Upsells_Log_Table::display()
 * @return void
 */
function edd_csau_logs_view_upsells() {
	include_once( EDD_CSAU_DIR . 'includes/class-logs-list-table.php' );
	
	$logs_table = new EDD_CSAU_Log_Table( 'upsell' );
	$logs_table->prepare_items();
	$logs_table->display();
}
add_action( 'edd_logs_view_upsells', 'edd_csau_logs_view_upsells' );


/**
 * Add Cross-sells/Upsells view types
 *
 * @since 1.1
*/
function edd_csau_log_view( $views ) {
	$views['cross_sells'] = __( 'Cross-sells', 'edd-csau' );
	$views['upsells'] = __( 'Upsells', 'edd-csau' );

	return $views;
}
add_filter( 'edd_log_views', 'edd_csau_log_view' );