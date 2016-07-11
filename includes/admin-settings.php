<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register our settings section
 *
 * @since 1.1.4
 * @return array
 */
function edd_csau_settings_section( $sections ) {
	// Note the array key here of 'ck-settings'
	$sections['csau'] = __( 'Cross-sell & Upsell', 'edd-csau' );
	return $sections;
}
add_filter( 'edd_settings_sections_extensions', 'edd_csau_settings_section' );

/**
 * Adds the settings to the Misc section
 *
 * @since 1.0
*/
function edd_csau_add_settings( $settings ) {

  $edd_csau_settings = array(
  		
		array(
			'id' => 'edd_csau_settings',
			'name' => '<strong>' . __( 'Cross-sell & Upsell', 'edd-csau' ) . '</strong>',
			'desc' => __( 'Configure EDD Upsell Settings', 'edd-csau' ),
			'type' => 'header'
		),
		array(
			'id' => 'edd_csau_upsell_heading',
			'name' => __( 'Default Upsell Heading', 'edd-csau' ),
			'desc' => sprintf( __( 'Enter the text to display above Upsell %s on the single %s page', 'edd-csau' ), strtolower( edd_get_label_plural() ), strtolower( edd_get_label_singular() ) ),
			'type' => 'text',
			'size' => 'regular',
			'std' => __( 'You may also like', 'edd-csau' ),
		),
		array(
			'id' => 'edd_csau_cross_sell_heading',
			'name' => __( 'Default Cross-sell Heading', 'edd-csau' ),
			'desc' => sprintf( __( 'Enter the text to display above Cross-sell %s at checkout', 'edd-csau' ), strtolower( edd_get_label_plural() ) ),
			'type' => 'text',
			'size' => 'regular',
			'std' => __( 'You may also like', 'edd-csau' ),
		),
		array(
			'id' => 'edd_csau_upsell_number',
			'name' => __( 'Maximum Upsells To Show', 'edd-csau' ),
			'desc' =>  sprintf( __( 'Enter the number of Upsells that should display on a single %s page', 'edd-csau' ), strtolower( edd_get_label_singular() ) ),
			'type' => 'select',
			'std' => '3',
			'options' => array(
				'1' => __( '1', 'edd-csau' ),
				'2' => __( '2', 'edd-csau' ),
				'3' => __( '3', 'edd-csau' ),
			),
		),
		
		array(
			'id' => 'edd_csau_cross_sell_number',
			'name' => __( 'Maximum Cross-sells To Show', 'edd-csau' ),
			'desc' => __( 'Enter the number of cross-sells that should display at checkout', 'edd-csau' ),
			'type' => 'select',
			'options' => array(
				'1' => __( '1', 'edd-csau' ),
				'2' => __( '2', 'edd-csau' ),
				'3' => __( '3', 'edd-csau' ),
			),
			'std' => '3'
		),
	);

	if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
		// Use the previously noted array key as an array key again and next your settings
		$edd_csau_settings = array( 'csau' => $edd_csau_settings );
	}

	return array_merge( $settings, $edd_csau_settings );

}
add_filter( 'edd_settings_extensions', 'edd_csau_add_settings' );