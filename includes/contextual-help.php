<?php
/**
 * Contextual Help
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add help tab
 *
 * @since 1.0
*/
function edd_csau_contextual_help( $screen ) {

	$screen->add_help_tab( array(
		'id'	    => 'edd-csau',
		'title'	    => __( 'Cross-selling/Upselling', 'edd-csau' ),
		'content'	=>
			
			'<p>' . sprintf( __( '<strong>Upselling</strong> - Upsell %s appear on the Single %s page', 'edd-csau' ), edd_get_label_plural(), edd_get_label_singular() ) . '</p>' .
			'<p>' . sprintf( __( '<strong>Cross-selling</strong> - Cross-sell %s appear on the Checkout page', 'edd-csau' ), edd_get_label_plural() ) . '</p>'
	) );

	return $screen;
}
add_action( 'edd_downloads_contextual_help', 'edd_csau_contextual_help' );