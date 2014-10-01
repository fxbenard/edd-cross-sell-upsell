<?php
/**
 * Reports
 *
 * @since 1.1
*/

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add Cross-sell/Upsell report views
 *
 * @since 1.1
*/
function edd_csau_reports_views( $views ) {
	$views[ 'cross_sell' ] 	= __( 'Cross-sells', 'edd-csau' );
	$views[ 'upsell' ] 		= __( 'Upsells', 'edd-csau' );

	return $views;
}
add_filter( 'edd_report_views', 'edd_csau_reports_views' );

/**
 * Renders the Cross-sell Downloads Table
 *
 * @since 1.1
 * @uses EDD_Download_Reports_Table::prepare_items()
 * @uses EDD_Download_Reports_Table::display()
 * @return void
 */
function edd_csau_reports_cross_sell_table() {
	include_once( EDD_CSAU_DIR . 'includes/class-reports-table.php' );
	$downloads_table = new EDD_CSAU_Reports_Table( 'cross_sell' );
	$downloads_table->prepare_items();
	$downloads_table->display();
}
add_action( 'edd_reports_view_cross_sell', 'edd_csau_reports_cross_sell_table' );

/**
 * Renders the Upsell Downloads Table
 *
 * @since 1.1
 * @uses EDD_Download_Reports_Table::prepare_items()
 * @uses EDD_Download_Reports_Table::display()
 * @return void
 */
function edd_csau_reports_upsell_table() {
	include_once( EDD_CSAU_DIR . 'includes/class-reports-table.php' );
	$downloads_table = new EDD_CSAU_Reports_Table( 'upsell' );
	$downloads_table->prepare_items();
	$downloads_table->display();
}
add_action( 'edd_reports_view_upsell', 'edd_csau_reports_upsell_table' );