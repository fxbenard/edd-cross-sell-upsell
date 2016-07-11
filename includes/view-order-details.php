<?php
/**
 * View order details
 *
 * @since 1.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add cross-sells to view order screen
 *
 * @since 1.1
*/
function edd_csau_view_order_details_cross_sells( $payment_id ) {
	$item         = get_post( $payment_id );
	$payment_meta = edd_get_payment_meta( $payment_id );
	$cart_items   = edd_get_payment_meta_cart_details( $payment_id );
	$user_info    = edd_get_payment_meta_user_info( $payment_id );
	$user_id      = edd_get_payment_user_id( $payment_id );
	$payment_date = strtotime( $item->post_date );

	if ( ! get_post_meta( $payment_id, '_edd_payment_cross_sell_total', true ) ) {
		return;
	}

?>
<div id="edd-purchased-files" class="postbox">
	<h3 class="hndle"><?php _e( 'Cross-sells included with this payment', 'edd-csau' ); ?></h3>
	<div class="inside">
		<table class="wp-list-table widefat fixed" cellspacing="0">
			<tbody id="the-list">
				<?php
				if ( $cart_items ) :
					$i = 0;
					foreach ( $cart_items as $key => $cart_item ) :
						$id = isset( $payment_meta['cart_details'] ) ? $cart_item['id'] : $cart_item;
						$price_override = isset( $payment_meta['cart_details'] ) ? $cart_item['price'] : null;
						$price = edd_get_download_final_price( $id, $user_info, $price_override );

						if ( ! isset( $cart_item['item_number']['options']['cross_sell'] ) )
							continue;
						?>
						<tr class="<?php if ( $i % 2 == 0 ) { echo 'alternate'; } ?>">
							<td class="name column-name">
								<?php
								echo '<a href="' . admin_url( 'post.php?post=' . $id . '&action=edit' ) . '">' . get_the_title( $id ) . '</a>';

								if ( isset( $cart_items[ $key ]['item_number']['options'] ) ) {
									$price_options = $cart_items[ $key ]['item_number']['options'];

									if ( isset( $price_options['price_id'] ) ) {
										echo ' - ' . edd_get_price_option_name( $id, $price_options['price_id'], $payment_id );
									}
								}
								?>
							</td>
						</tr>
						<?php
						$i++;
					endforeach;
				endif;
				?>
			</tbody>
		</table>
	</div>
</div>
<?php }
add_action( 'edd_view_order_details_main_after', 'edd_csau_view_order_details_cross_sells' );


/**
 * Add upsells to order details screen
 *
 * @since 1.0
*/
function edd_csau_view_order_details_upsells( $payment_id ) {
	$item         = get_post( $payment_id );
	$payment_meta = edd_get_payment_meta( $payment_id );
	$cart_items   = edd_get_payment_meta_cart_details( $payment_id );
	$user_info    = edd_get_payment_meta_user_info( $payment_id );
	$user_id      = edd_get_payment_user_id( $payment_id );
	$payment_date = strtotime( $item->post_date );

if ( ! get_post_meta( $payment_id, '_edd_payment_upsell_total', true ) )
	return;
?>
<div id="edd-purchased-files" class="postbox">
	<h3 class="hndle"><?php _e( 'Upsells included with this payment', 'edd-csau' ); ?></h3>
	<div class="inside">
		<table class="wp-list-table widefat fixed" cellspacing="0">
			<tbody id="the-list">
				<?php
				if ( $cart_items ) :
					$i = 0;
					foreach ( $cart_items as $key => $cart_item ) :
						$id = isset( $payment_meta['cart_details'] ) ? $cart_item['id'] : $cart_item;
						$price_override = isset( $payment_meta['cart_details'] ) ? $cart_item['price'] : null;
						$price = edd_get_download_final_price( $id, $user_info, $price_override );

						if ( ! isset( $cart_item['item_number']['options']['upsell'] ) )
							continue;
						?>
						<tr class="<?php if ( $i % 2 == 0 ) { echo 'alternate'; } ?>">
							<td class="name column-name">
								<?php
								echo '<a href="' . admin_url( 'post.php?post=' . $id . '&action=edit' ) . '">' . get_the_title( $id ) . '</a>';

								if ( isset( $cart_items[ $key ]['item_number'] ) ) {
									$price_options = $cart_items[ $key ]['item_number']['options'];

									if ( isset( $price_options['price_id'] ) ) {
										echo ' - ' . edd_get_price_option_name( $id, $price_options['price_id'], $payment_id );
									}
								}
								?>
							</td>
						</tr>
						<?php
						$i++;
					endforeach;
				endif;
				?>
			</tbody>
		</table>
	</div>
</div>
<?php }
add_action( 'edd_view_order_details_main_after', 'edd_csau_view_order_details_upsells' );
