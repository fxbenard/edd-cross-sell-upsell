<?php
/**
 * Functions
 *
 * @since 1.1
 * @param int $download_id Download ID
 * @param string $type Type of download, cross_sell or upsell
 * @return array $products
*/

function edd_csau_get_products( $download_id = 0, $type ) {
	$products = get_post_meta( $download_id, '_edd_csau_' . $type . '_products', false );

	foreach( $products as $key => $product ) {
		if( edd_item_in_cart( $product ) ) {
			unset( $products[ $key ] );
		}
	}

	return apply_filters( 'edd_csau_get_' . $type . '_products', $products, $download_id, $type );
}