<?php
/**
 * Payment Actions
 *
 * @since 1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Payment actions
 *
 * @since 1.1
*/
function edd_csau_payment_actions( $payment_id ) {

	$types        = array();
	$payment      = new EDD_Payment( $payment_id );
	$cart_details = $payment->cart_details;

	if ( is_array( $cart_details ) ) {

		// Increase purchase count and earnings
		foreach ( $cart_details as $download ) {

			// "bundle" or "default"
			$download_type = edd_get_download_type( $download['id'] );

			$price_id      = isset( $download['options']['price_id'] ) ? (int) $download['options']['price_id'] : false;

			// Increase earnings, and fire actions once per quantity number
			for( $i = 0; $i < $download['quantity']; $i++ ) {

				if ( ! edd_is_test_mode() || apply_filters( 'edd_log_test_payment_stats', false ) ) {

					if ( isset( $download['item_number']['options']['cross_sell'] ) ) {
						$type = 'cross_sell';
					} elseif ( isset( $download['item_number']['options']['upsell'] ) ) {
						$type = 'upsell';
					} else {
						$type = null;
					}

					if ( $type ) {
						edd_csau_increase_purchase_count( $download['id'], $type );
						edd_csau_increase_earnings( $download['id'], $download['price'], $type );
						edd_csau_record_sale_in_log( $download['id'], $payment_id, $price_id, $type );
					}

				}

				if( ! empty( $type ) ) {
					$types[] = $type;
					$types = array_unique( array_filter( $types ) );
					// Clear the total earnings cache
					delete_transient( 'edd_' . $type . '_earnings_total' );
				}
			}

		}

	}

	if ( $types ) {
		foreach ( $types as $type ) {
			// store the total amount of cross-sell earnings
			update_post_meta( $payment_id, '_edd_payment_' . $type . '_total', edd_csau_calculate_sales( $payment_id, $type ) );

			$amount = edd_csau_get_payment_amount( $payment_id, $type );

			// increase earnings
			edd_csau_increase_total_earnings( $amount, $type );
		}
	}

}
add_action( 'edd_complete_purchase', 'edd_csau_payment_actions' );
