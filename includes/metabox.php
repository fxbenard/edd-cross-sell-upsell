<?php
/**
 * Metaboxes
 *
 * @since 1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add metabox
 *
 * @since 1.0
*/
function edd_csau_add_meta_box() {
	add_meta_box( 'edd_csau_cross_sell_upsell', apply_filters( 'edd_csau_meta_box_title', __( 'Cross-sell & Upsell', 'edd-csau' ) ), 'edd_csau_render_fields', 'download', apply_filters( 'edd_csau_meta_box_context', 'normal' ), apply_filters( 'edd_csau_meta_box_priority', 'high' ) );
}
add_action( 'add_meta_boxes', 'edd_csau_add_meta_box' );

/**
 * Render fields
 *
 * @since 1.0
*/
function edd_csau_render_fields() { 
	
	$upsell_heading = get_post_meta( get_the_ID(), '_edd_csau_upsell_heading', true );
	$cross_sell_heading = get_post_meta( get_the_ID(), '_edd_csau_cross_sell_heading', true );
	$products = get_posts( array( 'post_type' => 'download', 'nopaging' => true, 'orderby' => 'title', 'order' => 'ASC', 'exclude' => get_the_ID() ) );
?>
	<p><strong><label for="edd_csau_upsell_heading"><?php _e( 'Upsell Heading', 'edd-csau' ); ?></label></strong></p>

	<p><input type="text" class="widefat" name="edd_csau_upsell_heading" id="edd_csau_upsell_heading" value="<?php echo esc_attr( $upsell_heading ); ?>" size="30" /></p>
	<p><?php printf( __( 'Shown with upsell %s on the single %s page. If not set, the default heading in the plugin\'s settings will be used instead', 'edd-csau' ),  strtolower( edd_get_label_plural() ), strtolower( edd_get_label_singular() ) ); ?></p>

	<p><strong><label for="edd_csau_upsell_products"><?php printf( __( 'Upsell %s', 'edd-csau' ),  edd_get_label_plural() ); ?></label></strong></p>

	<select name="edd_csau_upsell_products[]" id="edd_csau_upsell_products" data-placeholder="<?php printf( __( 'Select %s', 'edd-csau' ), edd_get_label_plural() ); ?>" multiple class="edd-csau-select">
		<?php if ( $products ) : 
		// get upsell product IDs from DB
		$upsell_products = edd_csau_get_products( get_the_ID(), 'upsell' );

		foreach ( $products as $product ) { 
			$selected = in_array( $product->ID, $upsell_products ) ? $product->ID : '';
		?>
		<option value="<?php echo absint( $product->ID ); ?>" <?php echo selected( $selected, $product->ID, false ); ?>><?php echo esc_html( get_the_title( $product->ID ) ); ?></option>

		<?php } ?>

		<?php else : ?>

			<option><?php printf( __( 'No %s found', 'edd-csau' ), edd_get_label_plural() ); ?></option>

		<?php endif; ?>

	</select>
	<p><?php printf( __( 'Select the %s to upsell to the customer on the single %s page', 'edd-csau' ), strtolower( edd_get_label_plural() ), strtolower( edd_get_label_singular() ) ); ?></p>


	<p><strong><label for="edd_csau_cross_sell_heading"><?php _e( 'Cross-sell Heading', 'edd-csau' ); ?></label></strong></p>
	
	<p><input type="text" class="widefat" name="edd_csau_cross_sell_heading" id="edd_csau_cross_sell_heading" value="<?php echo esc_attr( $cross_sell_heading ); ?>" size="30" /></p>
	<p><?php printf( __( 'Shown with cross-sells at checkout, but only if the selected cross-sells below are shown on their own. If not set, or cross-sells from other %s are shown together, the default heading in the plugin\'s settings will be used instead', 'edd-csau' ), strtolower( edd_get_label_plural() ) ); ?></p>

	<p><strong><label for="edd_csau_cross_sell_products"><?php printf( __( 'Cross-sell %s', 'edd-csau' ),  edd_get_label_plural() ); ?></label></strong></p>

	<select name="edd_csau_cross_sell_products[]" id="edd_csau_cross_sell_products" data-placeholder="<?php printf( __( 'Select %s', 'edd-csau' ), edd_get_label_plural() ); ?>" multiple class="edd-csau-select">
		<?php if ( $products ) : 
		// get cross-sell product IDs from DB
		$cross_sell_products = edd_csau_get_products( get_the_ID(), 'cross_sell' );

		foreach ( $products as $product ) { 
			$selected = in_array( $product->ID, $cross_sell_products ) ? $product->ID : '';
		?>
		<option value="<?php echo absint( $product->ID ); ?>" <?php echo selected( $selected, $product->ID, false ); ?>><?php echo esc_html( get_the_title( $product->ID ) ); ?></option>

		<?php } ?>

		<?php else : ?>

			<option><?php printf( __( 'No %s found', 'edd-csau' ), edd_get_label_plural() ); ?></option>

		<?php endif; ?>

	</select>
	<p><?php printf( __( 'Select the %s to be shown to the customer at checkout when this %s is added to the cart', 'edd-csau' ), strtolower( edd_get_label_plural() ), strtolower( edd_get_label_singular() ) ); ?></p>

<?php wp_nonce_field( 'edd_csau_nonce', 'edd_csau_nonce' ); ?>

<?php }


/**
 * Save function
 *
 * @since       1.0
*/
function edd_csau_save_post( $post_id ) {

	// First we need to check if the current user is authorised to do this action. 
	if ( ( isset( $_POST['post_type'] ) && 'download' == $_POST['post_type'] )  ) {
		if ( ! current_user_can( 'edit_page', $post_id ) )
	    	return;
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) )
	    	return;
	}

	if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) 
		return $post_id;

	if ( ! isset( $_POST[ 'edd_csau_nonce' ] ) || ! wp_verify_nonce( $_POST[ 'edd_csau_nonce' ], 'edd_csau_nonce' ) )
		return;

	$fields = apply_filters( 'edd_csau_metabox_fields_save', array(
			'edd_csau_upsell_heading',
			'edd_csau_cross_sell_heading',
			'edd_csau_upsell_products',
			'edd_csau_cross_sell_products'
		)
	);
	
foreach ( $fields as $field ) {

	// multiple select fields
	if( 'edd_csau_cross_sell_products' == $field || 'edd_csau_upsell_products' == $field ) {

		// save multiple select menus, each meta key + value pair as a separate entry
		$old = get_post_meta( $post_id, '_' . $field );
		
		$new = isset ( $_POST[ $field ] ) ? $_POST[ $field ] : array();

		if ( empty ( $new ) ) {

		   // no downloads selected: completely delete all meta values for the post
		   delete_post_meta( $post_id, '_' . $field );

		} 
		// new downloads selected
		else {

		  $already = array();

		  // if there's already get_post_meta
		  if ( ! empty( $old ) ) {
		  	// loop over each meta key
		    foreach ( $old as $value ) {
		    	// if the meta key is in the new post array being sent
				if ( ! in_array( $value, $new ) )
					// this value was selected, but now it isn't, so delete it
					delete_post_meta( $post_id, '_' . $field, $value );
				else 
					// this value already saved, we can skip it from saving
					$already[] = $value;
		    }
		  }

		  // we don't save what already saved
		  $to_save = array_diff( $new, $already );

		  if ( ! empty( $to_save ) ) {

		    foreach ( $to_save as $product )
		       add_post_meta( $post_id, '_' . $field, $product );
		    
		  }

		}


	}
	// all other field types
	else {
		$new = ( isset( $_POST[ $field ] ) ? esc_attr( $_POST[ $field ] ) : '' );

		$new = apply_filters( 'edd_csau_save_' . $field, $new );

		// prefix with underscore
		$meta_key = '_' . $field;

		// Get the meta value of the custom field key.
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		// If a new meta value was added and there was no previous value, add it. 
		if ( $new && '' == $meta_value )
			add_post_meta( $post_id, $meta_key, $new, true );

		// If the new meta value does not match the old value, update it. 
		elseif ( $new && $new != $meta_value )
			update_post_meta( $post_id, $meta_key, $new );

		// If there is no new meta value but an old value exists, delete it. 
		elseif ( '' == $new && $meta_value )
			delete_post_meta( $post_id, $meta_key, $meta_value );

	}
	

} // end foreach

	
}
add_action( 'save_post', 'edd_csau_save_post' );