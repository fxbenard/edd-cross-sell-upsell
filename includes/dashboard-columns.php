<?php
/**
 * Dashboard Columns
 *
 * @since 1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add filter for displaying posts
 *
 * @since 1.0
*/
function edd_csau_pre_get_posts( $wp_query ) {
    global $typenow;

    if ( $typenow == 'download')
        add_filter( 'posts_where' , 'edd_csau_posts_where' );

}
add_action( 'pre_get_posts', 'edd_csau_pre_get_posts' );

/**
 * Posts where
 *
 * @since 1.0
*/
function edd_csau_posts_where( $where ) {

    global $wpdb;  

    if ( isset( $_GET[ 'edd_csau' ] ) && !empty( $_GET[ 'edd_csau' ] ) ) {

        if( 'upsells' == $_GET[ 'edd_csau' ] )
        	$meta_key = '_edd_csau_upsell_products';

        elseif( 'cross_sells' == $_GET[ 'edd_csau' ] )
        	$meta_key = '_edd_csau_cross_sell_products';


        if( 'both' == $_GET[ 'edd_csau' ] )
        	 $where .= " AND ID IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_edd_csau_upsell_products' OR meta_key='_edd_csau_cross_sell_products' )";
        else
        	$where .= " AND ID IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key='$meta_key' )";

    }

    return $where;

}

/**
 * Add Download Filters
 *
 * Adds cross-sell/upsell drop down filters for downloads.
 *
 * @since 1.0
 * @return void
 */
function edd_csau_download_filters() {
	global $typenow;

	// Checks if the current post type is 'download'
	if ( $typenow == 'download') {
		
		$types = array(
			'cross_sells' => sprintf( __( '%s with cross-sells', 'edd-csau' ), edd_get_label_plural() ),
			'upsells' => sprintf( __( '%s with upsells', 'edd-csau' ), edd_get_label_plural() ),
			'both' => sprintf( __( '%s with cross-sells or upsells', 'edd-csau' ), edd_get_label_plural() ),
		);

		echo "<select name='edd_csau' id='edd_csau' class='postform'>";
			echo "<option value=''>" . sprintf( __( 'Show all %s', 'edd-csau' ), strtolower( edd_get_label_plural() ) ) . "</option>";
			
			foreach ( $types as $key => $label ) {


				$args = array(
					'post_type' => 'download',
					'post_status' => 'publish',
				);

				if( 'upsells' == $key ) {
					$args[ 'meta_key' ] = '_edd_csau_upsell_products';
				}

		        elseif( 'cross_sells' == $key ) {
		        	$args[ 'meta_key' ] = '_edd_csau_cross_sell_products';
		        }

		        elseif( 'both' == $key ) {
		        	$args[ 'meta_query' ] = array(
		        		'relation' => 'OR',
		        		array(
		        			'key' => '_edd_csau_cross_sell_products',
		        		),
		        		array(
		        			'key' => '_edd_csau_upsell_products',
		        		),
		        		
		        	);
		        }
		        else {
		        	$meta_key = '';
		        }

				$type = get_posts( $args );
				$count = count( $type );


				$selected = isset( $_GET[ 'edd_csau' ]) && $_GET[ 'edd_csau' ] == $key ? ' selected="selected"' : '';
				echo '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $label ) .' (' . $count .')</option>';
			}
		echo "</select>";
		
	}

}
add_action( 'restrict_manage_posts', 'edd_csau_download_filters', 100 );