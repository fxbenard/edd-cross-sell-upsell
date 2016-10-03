<?php
/**
 * Cart functions
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Cross-sell IDs shown on checkout page. These have not been added to the cart yet
 *
 * @return array
 * @since 1.0
*/
function edd_csau_get_cart_cross_sell_ids() {

	// get the cart contents
	$cart_items = edd_get_cart_contents();

	if ( ! $cart_items )
		return;

	$cross_sell_ids = array();

	// loop through each of the products and compile a list of their cross sells
	foreach ( $cart_items as $cart_item => $item ) {
		$id = (int) $item[ 'id' ];
		$cross_sell_ids[] = get_post_meta( $id, '_edd_csau_cross_sell_products', false );
	}

	if ( $cross_sell_ids ) {
		// flatten 2 level array into 1
		$cross_sell_ids = call_user_func_array( 'array_merge', $cross_sell_ids );
		return $cross_sell_ids;
	}

	return null;
}

/**
 * Cross-sell IDs that are in the cart. These are the cross-sells that the customer is about to purchase
 *
 * @return array $ids
 * @since 1.0
*/
function edd_csau_get_cross_sells_in_cart() {
	// get the cart contents
	$cart_items = edd_get_cart_contents();

	if ( ! $cart_items )
		return;

	$ids = array();

	if ( edd_csau_get_cart_cross_sell_ids() ) {
		// loop through all the cross IDs at checkout
		foreach ( edd_csau_get_cart_cross_sell_ids() as $id ) {
			// see if the cross-sell is in the cart
			if ( edd_item_in_cart( $id ) ) {
				 $ids[] = $id;
		 	}
		}
	}

	return $ids;
}


/**
 * Get a cross-sell or upsell's trigger ID
 *
 * @since 1.0
*/
function edd_csau_get_trigger_id( $download_id = 0, $type ) {
	$args = array(
	    'post_type' => 'download',
	    'meta_query' => array(
	        array(
	            'key' => '_edd_csau_' . $type . '_products',
	            'value' => $download_id,
	        )
	    )
	);

	$post = get_posts( $args );

	// return the first array's ID
	if ( $post )
		return $post[0]->ID;
}


/**
 * Cart product IDs
 * This may include cross-sells of items already in cart
 *
 * @return array $cart_item_ids if IDs, else null
 * @since 1.0
*/
function edd_csau_get_cart_product_ids() {

	// get the cart contents
	$cart_items = edd_get_cart_contents();

	if ( ! $cart_items )
		return;

	// loop through each of the products and compile a list of their cross sells
	foreach ( $cart_items as $cart_item => $item ) {
		// store ids into array
		$cart_item_ids[] = (int) $item['id'];
	}

	if ( $cart_item_ids )
		return $cart_item_ids;

	return null;
}

/**
 * Non cross-sell product IDs. These are product IDs that aren't cross sells themselves
 * Could be a trigger product or a download without any cross-sells
 *
 * @return array $non_cross_sell_ids, else false
 * @since 1.0
*/
function edd_csau_get_cart_non_cross_sell_ids() {

	$non_cross_sell_ids = array_diff( edd_csau_get_cart_product_ids(), edd_csau_get_cart_cross_sell_ids() );

	if ( $non_cross_sell_ids )
		return $non_cross_sell_ids;

	return false;

}

/**
 * Trigger product IDs
 *
 * @return array $ids of cross-sell triggers in the cart, false otherwise
 * @since 1.0
*/
function edd_csau_get_cart_trigger_ids() {
	$cart_items = edd_get_cart_contents();

	// return if no cart items exist
	if ( ! $cart_items )
		return;

	// array of cross-sell IDs
	$ids = array();

	// loop through each of the products and compile a list of their cross sells
	foreach ( $cart_items as $cart_item => $item ) {
		// if it has the '_edd_csau_cross_sell_products' meta_key, then it's a cross-sell trigger
		if ( get_post_meta( (int) $item['id'], '_edd_csau_cross_sell_products', true ) ) {
			// store each id into array
			$ids[] = (int) $item['id'];
		}
	}

	if ( $ids ) {
		return $ids;
	}
	else {
		return false;
	}
}

/**
 * Cart has cross-sells
 *
 * @return boolean true if cart has cross-sells, false otherwise
 * @since 1.0
*/
function edd_csau_has_cart_cross_sells() {
	$cart_items = edd_get_cart_contents();

	if ( ! $cart_items )
		return;

	foreach ( $cart_items as $cart_item => $item ) {
		if ( get_post_meta( (int) $item[ 'id' ], '_edd_csau_cross_sell_products', true ) )
			return true;
	}

	return false;
}


/**
 * Use EDD_Session class to help us mark downloads as cross-sell or upsell
 *
 * @since 1.1
*/
function edd_csau_set_session() {

	// is single download page
	if ( is_singular( 'download' ) ) {
		EDD()->session->set( 'edd_is_single', true );
	}
	else {
		EDD()->session->set( 'edd_is_single', NULL );
	}

	// is checkout page
	if ( edd_is_checkout() ) {
		EDD()->session->set( 'edd_is_checkout', true );
	}
	else {
		EDD()->session->set( 'edd_is_checkout', NULL );
	}

}
add_action( 'template_redirect', 'edd_csau_set_session' );




/**
 * Store extra meta information against the download at the time it is added to the cart
 *
 * @param $item the default array of meta information stored with the download
 * @return $item the new array of meta information
 *
 * @since 1.1
*/
function edd_csau_add_to_cart_item( $item ) {

	// Cross-sells
	if ( EDD()->session->get( 'edd_is_checkout' ) ) {
		// if item is added from checkout page, and it's trigger product is already in the cart, then mark as cross-sell
		if ( edd_item_in_cart( edd_csau_get_trigger_id( $item['id'], 'cross_sell' ) ) ) {
			$item['options']['cross_sell'] = true;
	 	}
	}
	// Upsells
	else {
		// get the downloads trigger ID
		$trigger_id = edd_csau_get_trigger_id( $item['id'], 'upsell' );

		// if download does not have a trigger ID, exit
		if ( ! $trigger_id )
			return $item;

		// use the trigger ID to get an array of upsell IDs
		$upsell_ids = get_post_meta( $trigger_id, '_edd_csau_upsell_products' );

		// if this download exists in the upsell IDs array, then mark it as an upsell
		if ( EDD()->session->get( 'edd_is_single' ) && in_array( $item['id'], $upsell_ids ) ) {
			$item['options']['upsell'] = true;
		}

	}

	return $item;
}
add_filter( 'edd_add_to_cart_item', 'edd_csau_add_to_cart_item' );
