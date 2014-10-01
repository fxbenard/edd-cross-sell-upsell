<?php
/**
 * Exports Functions
 *
 * These are functions are used for exporting cross-sells and upsells from Easy Digital Downloads.
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-export.php';

/**
 * Exports all Cross-sells stored in Payment History to a CSV file using the EDD_Export class
 *
 * @since 1.1
 * @return void
 */
function edd_csau_export_cross_sell_history() {
	require_once( EDD_CSAU_DIR . 'includes/class-export-cross-sells.php' );
	$export = new EDD_Cross_Sells_Export();
	$export->export();
}
add_action( 'edd_cross_sells_export', 'edd_csau_export_cross_sell_history' );

/**
 * Exports all Upsells stored in Payment History to a CSV file using the EDD_Export class
 * 
 * @since 1.1
 * @return void
 */
function edd_csau_export_upsell_history() {
	require_once( EDD_CSAU_DIR . 'includes/class-export-upsells.php' );
	$export = new EDD_Upsells_Export();
	$export->export();
}
add_action( 'edd_upsells_export', 'edd_csau_export_upsell_history' );

/**
 * Adds metabox for exporting cross-sells from the reports -> export page
 *
 * @since 1.1
*/
function edd_csau_reports_export_cross_sells() { ?>
	<div class="postbox">
		<h3><span><?php _e( 'Export Cross-sell History', 'edd-csau' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Download a CSV of all cross-sells recorded.', 'edd-csau' ); ?></p>
			<p>
				<form method="post">
					<?php echo EDD()->html->year_dropdown(); ?>
					<?php echo EDD()->html->month_dropdown(); ?>
					<select name="edd_export_payment_status">
						<option value="0"><?php _e( 'All Statuses', 'edd-csau' ); ?></option>
						<?php
						$statuses = edd_get_payment_statuses();
						foreach( $statuses as $status => $label ) {
							echo '<option value="' . $status . '">' . $label . '</option>';
						}
						?>
					</select>
					<input type="hidden" name="edd-action" value="cross_sells_export" />
					<input type="submit" value="<?php _e( 'Generate CSV', 'edd-csau' ); ?>" class="button-secondary" />
				</form>
			</p>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php }
add_action( 'edd_reports_tab_export_content_bottom', 'edd_csau_reports_export_cross_sells' );

/**
 * Adds metabox for exporting upsells from the reports -> export page
 *
 * @since 1.1
*/
function edd_csau_reports_export_upsells() { ?>
	<div class="postbox">
		<h3><span><?php _e( 'Export Upsell History', 'edd-csau' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Download a CSV of all upsells recorded.', 'edd-csau' ); ?></p>
			<p>
				<form method="post">
					<?php echo EDD()->html->year_dropdown(); ?>
					<?php echo EDD()->html->month_dropdown(); ?>
					<select name="edd_export_payment_status">
						<option value="0"><?php _e( 'All Statuses', 'edd-csau' ); ?></option>
						<?php
						$statuses = edd_get_payment_statuses();
						foreach( $statuses as $status => $label ) {
							echo '<option value="' . $status . '">' . $label . '</option>';
						}
						?>
					</select>
					<input type="hidden" name="edd-action" value="upsells_export" />
					<input type="submit" value="<?php _e( 'Generate CSV', 'edd-csau' ); ?>" class="button-secondary" />
				</form>
			</p>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php }
add_action( 'edd_reports_tab_export_content_bottom', 'edd_csau_reports_export_upsells' );