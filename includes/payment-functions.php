<?php
/**
 * Payment functions
 *
 * @since 1.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 *
 * Increases the sale count of a cross-sell/upsell download.
 *
 * @since 1.1
 * @param int $download_id Download ID
 * @return void
 */
function edd_csau_increase_purchase_count( $download_id, $type ) {

	$sales = edd_csau_get_download_sales_stats( $download_id, $type );

	$sales = $sales + 1;

	if ( update_post_meta( $download_id, '_edd_download_' . $type . '_sales', $sales ) )
		return $sales;

	return false;
}

/**
 * Return the sales number for a cross-sell/upsell download.
 *
 * @since 1.1
 * @param int $download_id Download ID
 * @return int $sales Amount of sales for a certain download
 */
function edd_csau_get_download_sales_stats( $download_id, $type ) {

	// If the current Download CPT has no sales value associated with it, we need to initialize it.
	// This is what enables us to sort it.
	if ( '' == get_post_meta( $download_id, '_edd_download_' . $type . '_sales', true ) ) {
		add_post_meta( $download_id, '_edd_download_' . $type . '_sales', 0 );
	}

	$sales = get_post_meta( $download_id, '_edd_download_' . $type . '_sales', true );

	return $sales;
}

/**
 * Increases the total earnings of a cross-sell/upsell download.
 *
 * @since 1.1
 * @param int $download_id Download ID
 * @param int $amount Earnings
 * @return void
 */
function edd_csau_increase_earnings( $download_id, $amount, $type ) {
	$earnings = edd_csau_get_download_earnings_stats( $download_id, $type );
	$earnings = $earnings + $amount;

	if ( update_post_meta( $download_id, '_edd_download_' . $type . '_earnings', $earnings ) )
		return $earnings;

	return false;
}

/**
 * Decreases the total earnings of a cross-sell/up-sell download. Primarily for when a purchase is refunded.
 *
 * @since 1.1
 * @param int $download_id Download ID
 * @param int $amount Earnings
 * @return void
 */
function edd_csau_decrease_earnings( $download_id, $amount, $type ) {
	$earnings = edd_csau_get_download_earnings_stats( $download_id, $type );

	if ( $earnings > 0 ) // Only decrease if greater than zero
		$earnings = $earnings - $amount;

	if ( update_post_meta( $download_id, '_edd_download_' . $type . '_earnings', $earnings ) )
		return $earnings;

	return false;
}

/**
 * Undos a purchase, including the decrease of sale and earning stats. Used for
 * when refunding or deleting a purchase
 *
 * @since 1.1
 * @todo make sure it works with quantities
 * @param int $download_id Download (Post) ID
 * @param int $payment_id Payment ID
 * @return void
 */
function edd_csau_undo_purchase( $download_id, $payment_id, $type ) {
	if ( edd_is_test_mode() )
        return; // Don't undo if we are in test mode!

	$payment = get_post( $payment_id );

	edd_csau_decrease_purchase_count( $download_id, $type );

	$user_info    = edd_get_payment_meta_user_info( $payment_id );
	$cart_details = edd_get_payment_meta_cart_details( $payment_id );
	$amount       = null;

	if ( is_array( $cart_details ) && edd_has_variable_prices( $download_id ) ) {

		$cart_item_id = array_search( $download_id, $cart_details );
		$price_id     = isset( $cart_details[ $cart_item_id ]['price'] ) ? $cart_details[ $cart_item_id ]['price'] : null;
		$amount       = edd_get_price_option_amount( $download_id, $price_id );
	}

	$amount = edd_get_download_final_price( $download_id, $user_info, $amount );

	edd_csau_decrease_earnings( $download_id, $amount, $type );

}

/**
 * Decreases the sale count of a cross-sell/upsell download. Primarily for when a purchase is
 * refunded.
 *
 * @since 1.1
 * @param int $download_id Download ID
 * @return void
 */
function edd_csau_decrease_purchase_count( $download_id, $type ) {
	$sales = edd_csau_get_download_sales_stats( $download_id, $type );

	if ( $sales > 0 ) // Only decrease if not already zero
		$sales = $sales - 1;

	if ( update_post_meta( $download_id, '_edd_download_' . $type . '_sales', $sales ) )
		return $sales;

	return false;
}


/**
 * Reduces earnings and sales stats when a purchase is refunded
 *
 * @since 1.1
 * @param $data Arguments passed
 * @return void
 */
function edd_csau_undo_purchase_on_refund( $payment_id, $new_status, $old_status ) {

	if( 'publish' != $old_status )
		return;

	if( 'refunded' != $new_status )
		return;

	$downloads = edd_get_payment_meta_cart_details( $payment_id );

	if( $downloads ) {
		foreach( $downloads as $download ) {

			if ( isset( $download['item_number']['options']['cross_sell'] ) )
				$type = 'cross_sell';
			elseif ( isset( $download['item_number']['options']['upsell'] ) )
				$type = 'upsell';
			else
				$type = null;

			edd_csau_undo_purchase( $download['id'], $payment_id, $type );
		}
	}

	// Decrease store earnings
	$amount = edd_csau_get_payment_amount( $payment_id, $type );
	//edd_decrease_total_earnings( $amount );
	edd_csau_decrease_total_earnings( $amount, $type );

}
add_action( 'edd_update_payment_status', 'edd_csau_undo_purchase_on_refund', 100, 3 );



/**
 * Record Cross-sell/Upsell Sale In Log
 *
 * Stores log information for a download sale.
 *
 * @since 1.1
 * @global $edd_logs
 * @param int $download_id Download ID
 * @param int $payment_id Payment ID
 * @param int $price_id Price ID, if any
 * @return void
*/
function edd_csau_record_sale_in_log( $download_id, $payment_id, $price_id = false, $type ) {
	global $edd_logs;

	$log_data = array(
		'post_parent' 	=> $download_id,
		'log_type'		=> $type
	);

	// store log meta
	$log_meta = array(
		'payment_id'    => $payment_id,
		$type .'_id' 	=> $download_id, // store the cross-sell/upsell's ID. Useful for getting the IDs of all Cross-sells
		'price_id'      => (int) $price_id
	);

	$log_id = $edd_logs->insert_log( $log_data, $log_meta );
}

/**
 * Add Cross-sells/Upsells as log terms
 *
 * @since 1.1
*/
function edd_csau_edd_log_types( $terms ) {
	$new_terms = array( 'cross_sell', 'upsell' );

	$terms = array_merge( $new_terms, $terms );
	return $terms;
}
add_filter( 'edd_log_types', 'edd_csau_edd_log_types' );

/**
 * Returns the total cross-sell earnings for a download.
 *
 * @since 1.1
 * @param int $download_id Download ID
 * @return int $earnings Earnings for a certain download
 */
function edd_csau_get_download_earnings_stats( $download_id, $type ) {
	// If the current Download CPT has no earnings value associated with it, we need to initialize it.
	// This is what enables us to sort it.
	if ( '' == get_post_meta( $download_id, '_edd_download_' . $type . '_earnings', true ) ) {
		add_post_meta( $download_id, '_edd_download_' . $type . '_earnings', 0 );
	}

	$earnings = get_post_meta( $download_id, '_edd_download_' . $type . '_earnings', true );

	return $earnings;
}

/**
 * Increase the Total Cross-sell Earnings
 *
 * @since 1.1
 * @return float $total Total earnings
 */
function edd_csau_increase_total_earnings( $amount = 0, $type ) {
	$total = (float) edd_csau_get_total_earnings( $type );
	$total += $amount;

	update_option( 'edd_' . $type . '_earnings_total', $total );

	return $total;
}

/**
 * Decrease the Total Earnings
 *
 * @since 1.8.4
 * @return float $total Total earnings
 */
function edd_csau_decrease_total_earnings( $amount = 0, $type ) {
	$total = (float) edd_csau_get_total_earnings( $type );
	$total -= $amount;

	if( $total < 0 ) {
		$total = 0;
	}

	update_option( 'edd_' . $type . '_earnings_total', $total );

	return $total;
}

/**
 * Get Total Cross-sell/Upsell Earnings
 *
 * @since 1.1
 * @return float $total Total earnings
 */
function edd_csau_get_total_earnings( $type ) {

	$total = get_option( 'edd_' . $type . '_earnings_total', 0 );

	// If no total stored in DB, use old method of calculating total earnings
	if( ! $total ) {

		$total = get_transient( 'edd_' . $type . '_earnings_total' );

		if( false === $total ) {

			$total = (float) 0;

			$args = apply_filters( 'edd_get_total_' . $type . '_earnings_args', array(
				'offset' => 0,
				'number' => -1,
				'mode'   => 'live',
				'status' => array( 'publish', 'revoked' ),
				'fields' => 'ids'
			) );

			$payments = edd_get_payments( $args );

			if ( $payments ) {
				foreach ( $payments as $payment ) {
					$total += (float) edd_csau_get_payment_amount( $payment, $type );
				}
			}

			// Cache results for 1 day. This cache is cleared automatically when a payment is made
			set_transient( 'edd_' . $type . '_earnings_total', $total, 86400 );

			// Store the total for the first time
			update_option( 'edd_' . $type . '_earnings_total', $total );
		}
	}

	if( $total < 0 ) {
		$total = 0; // Don't ever show negative earnings
	}

	return apply_filters( 'edd_' . $type . '_total_earnings', round( $total, 2 ), $type );
}

/**
 * Get the amount associated with a payment
 *
 * @access public
 * @since 1.1
 * @param int $payment_id Payment ID
 * @return string $amount Payment amount
 */
function edd_csau_get_payment_amount( $payment_id, $type ) {

	$amount = get_post_meta( $payment_id, '_edd_payment_' . $type . '_total', true );

	return (float) apply_filters( 'edd_csau_' . $type . '_amount', $amount, $type );
}

/**
 * Calculate the total price amount of cross-sells/upsells to store with each purchase
 *
 * @since 1.1
 * @return $amount total cross-sell/upsell payment total
*/
function edd_csau_calculate_sales( $payment_id, $type ) {

	$cart_details = edd_get_payment_meta_cart_details( $payment_id );

	// array to hold our values
	$amounts = array();

	foreach ( $cart_details as $item ) {
		// add each cross-sell/upsell amount to array
		if ( isset( $item['item_number']['options'][$type] ) ) {
			$amounts[] = $item['price'] * $item['quantity'];
		}
	}

	if ( $amounts ) {
		$amount = round( array_sum( $amounts ), 2 );

		return $amount;
	}

	return null;

}
