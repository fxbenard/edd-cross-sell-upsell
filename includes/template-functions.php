<?php
/**
 * Template functions
 *
 * @since 1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display Cross-sell/Upsell products
 *
 * @since 1.0
*/
function edd_csau_html( $columns = '3' ) {

	global $post, $edd_options;

	// upsell products for the single download page
	if ( is_singular( 'download' ) ) {
		$products = edd_csau_get_products( get_the_ID(), 'upsell' );
	}
	// cross-sell products at checkout
	elseif ( edd_is_checkout() ) {

		// get contents on the cart
		$cart_items = edd_get_cart_contents();

		// return if there's nothing in the cart
		if ( ! $cart_items )
			return;

		$cart = array();


		// create new products array with the cart items cross sell products
		if ( $cart_items ) {
			foreach ( $cart_items as $cart_item ) {
				$download_id = $cart_item[ 'id' ];

				// create $cart array with IDs
				$cart[] = (int) $cart_item[ 'id' ];

				// create $product_list array with cross sell products
				$product_list[] = get_post_meta( $download_id, '_edd_csau_cross_sell_products', false );
			}
		}

		$products = $product_list;

		// clean the array
		$products = array_filter( $products );

		// return if no cross sell products after clean
		if ( ! $products )
			return;

		// merge into single level array
		$products = call_user_func_array( 'array_merge', $products );

		// remove duplicate IDs
		$products = array_unique( $products );

	}
	else {
		return;
	}

	if ( $products ) { ?>

		<?php

			if ( edd_is_checkout() ) {
				$posts_per_page = isset( $edd_options[ 'edd_csau_cross_sell_number' ] ) && !empty( $edd_options[ 'edd_csau_cross_sell_number' ] ) ? $edd_options[ 'edd_csau_cross_sell_number' ] : '3';
			}
			elseif( is_singular( 'download' ) ) {
				$posts_per_page = isset( $edd_options[ 'edd_csau_upsell_number' ] ) && !empty( $edd_options[ 'edd_csau_upsell_number' ] ) ? $edd_options[ 'edd_csau_upsell_number' ] : '3';
			}

			$query = array(
				'post_type'      	=> 'download',
				'posts_per_page' 	=> $posts_per_page,
				'orderby'          	=> 'date',
				'order'            	=> 'DESC',
				'post__in'			=> $products,
			);

			$query = apply_filters( 'edd_csau_query', $query );

			$downloads = new WP_Query( $query );


			if ( $downloads->have_posts() ) :

			// upsell heading
			if ( is_singular( 'download' ) ) {
				$upsell_heading = get_post_meta( get_the_ID(), '_edd_csau_upsell_heading', true );

				// show singular heading
				if( $upsell_heading ) {
					$heading = esc_attr( $upsell_heading );
				}
				// show default in settings
				elseif( isset( $edd_options[ 'edd_csau_upsell_heading' ] ) ) {
					$heading = esc_attr( $edd_options[ 'edd_csau_upsell_heading' ] );
				}
				else {
					$heading = __( 'You may also like', 'edd-csau' );
				}
			}
			// cross-sell heading
			elseif ( edd_is_checkout() ) {
				$ids = edd_csau_get_cart_trigger_ids();

				if ( count( $ids ) == 1 && get_post_meta( $ids[0], '_edd_csau_cross_sell_heading', true ) ) {
				    $heading = esc_attr( get_post_meta( $ids[0], '_edd_csau_cross_sell_heading', true ) );
				}
				// show default in settings
				elseif( isset( $edd_options[ 'edd_csau_cross_sell_heading' ] ) ) {
					$heading = esc_attr( $edd_options[ 'edd_csau_cross_sell_heading' ] );
				}
				else {
					$heading = __( 'You may also like', 'edd-csau' );
				}
			} // end is_checkout

			$i = 1;

			global $wp_query;

			//$download_count = $downloads->found_posts > 3 ? 3 : $downloads->found_posts;

			$classes = array();
			$classes = apply_filters( 'edd_csau_classes', $classes );

			// default classes
			$classes[] = 'edd-csau-products';

			// columns
			if( $columns )
				$classes[] = 'col-' . $columns;

			// filter array and remove empty values
			$classes = array_filter( $classes );
			$classes = !empty( $classes ) ? implode( ' ', $classes ) : '';
			$class_list = !empty( $classes ) ? 'class="' . $classes  . '"' : '';

			ob_start();
			?>

			<div <?php echo $class_list; ?>>

				<?php if ( $heading ) : ?>
				<h2><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php while ( $downloads->have_posts() ) : $downloads->the_post(); ?>
					<div itemscope itemtype="http://schema.org/Product" class="<?php echo apply_filters( 'edd_download_class', 'edd_download', '', '' ); ?>" id="edd_download_<?php echo get_the_ID(); ?>">
						<div class="edd_download_inner">

							<?php

							do_action( 'edd_csau_download_before' );

							$show_excerpt 	= apply_filters( 'edd_csau_show_excerpt', true );
							$show_price 	= apply_filters( 'edd_csau_show_price', true );
							$show_button 	= apply_filters( 'edd_csau_upsell_show_button', true );

							edd_get_template_part( 'shortcode', 'content-image' );
							edd_get_template_part( 'shortcode', 'content-title' );

							if ( $show_price ) {
								edd_get_template_part( 'shortcode', 'content-price' );
							}


							if ( $show_excerpt ) {
								edd_get_template_part( 'shortcode', 'content-excerpt' );
							}

							// if the download is not in the cart, show the add to cart button

							if ( edd_is_checkout() ) {

								if ( ! edd_item_in_cart( get_the_ID() ) ) {
									$text = apply_filters( 'edd_csau_cross_sell_add_to_cart_text', __( 'Add to cart', 'edd-csau' ) );
									$price = apply_filters( 'edd_csau_cross_sell_show_button_price', false );

									if ( $show_button ) : ?>

									<div class="edd_download_buy_button">
										<?php echo edd_get_purchase_link( array( 'download_id' => get_the_ID(), 'text' => $text, 'price' => $price ) ); ?>
									</div>
									<?php endif; ?>

								<?php } else {
									echo apply_filters( 'edd_csau_added_to_cart_text', '<span class="edd-cart-added-alert"><i class="edd-icon-ok"></i> '. __( 'Added to cart', 'edd-csau' ) . '</span>' );
								}
							} else {
								$text = apply_filters( 'edd_csau_upsell_add_to_cart_text', __( 'Add to cart', 'edd-csau' ) );
								$price = apply_filters( 'edd_csau_upsell_show_button_price', false );

								$show_button = apply_filters( 'edd_csau_upsell_show_button', true );

								if ( $show_button ) :
							?>
								<div class="edd_download_buy_button">
									<?php echo edd_get_purchase_link( array( 'download_id' => get_the_ID(), 'text' => $text, 'price' => $price ) ); ?>
								</div>
								<?php endif; ?>
							<?php }

							do_action( 'edd_csau_download_after' );

							?>
						</div>
					</div>
					<?php if ( $columns && $i % $columns == 0 ) { ?><div style="clear:both;"></div><?php } ?>
				<?php $i++; endwhile; ?>

				<?php wp_reset_postdata(); ?>
			</div>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'edd_csau_html', $html, $downloads, $heading, $columns, $class_list );

		endif;

		?>

		<?php }

?>

<?php }


/**
 * Display on checkout page
 *
 * @since 1.0
*/
function edd_csau_display_on_checkout_page() {
	echo edd_csau_html();
}

/**
 * Show cross-sell downloads underneath cart on checkout
 *
 * @since 1.0
*/
function edd_csau_checkout_display() {
	add_action( 'edd_after_checkout_cart', 'edd_csau_display_on_checkout_page' );
}
add_action( 'template_redirect', 'edd_csau_checkout_display' );

/**
 * Add upsell downloads to single download pages, underneath content
 *
 * @since 1.0
*/
function edd_csau_single_download_upsells( $content ) {
	// upsells
	if( is_singular( 'download' ) && is_main_query() ) {
		$new_content = edd_csau_html();
		return $content . $new_content;
	}

	return $content;
}
// added to a later priority so it's below other things that made be added to the content
add_filter( 'the_content', 'edd_csau_single_download_upsells', 100 );
