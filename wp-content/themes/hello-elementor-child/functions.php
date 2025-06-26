<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'hello-elementor','hello-elementor','hello-elementor-theme-style','hello-elementor-header-footer' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 5000 );

// END ENQUEUE PARENT ACTION

function child_theme_register_menus() {
    register_nav_menu('mega-menu', 'Mega Menu');
}
add_action('after_setup_theme', 'child_theme_register_menus');


// functions.php or a custom plugin
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
add_action( 'woocommerce_review_order_before_payment', 'custom_checkout_coupon_before_payment_methods' );

// function custom_checkout_coupon_before_payment_methods() {
// 	if ( ! wc_coupons_enabled() ) {
// 		return;
// 	}

// 	echo '<div class="custom-checkout-coupon">';
// 	echo '<div class="woocommerce-notices-wrapper"></div>';
// 	woocommerce_checkout_coupon_form(); // uses native structure and messages
// 	wc_print_notices();
// 	echo '</div>';
// }
add_action('woocommerce_before_checkout_form', function() {
	// Move notices where you want them (pre-coupon form)
	remove_action('woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10);
}, 1);



function custom_checkout_text() {
	echo '<div class="custom-checkout-txt">';
	echo get_field('checkout_info', 'option'); 
	echo '</div>';
}
add_action('woocommerce_review_order_before_payment', 'custom_checkout_text',5);




// Output notices and coupon form together where you want
function custom_checkout_coupon_before_payment_methods() {
	if ( ! wc_coupons_enabled() ) return;

	echo '<div class="custom-checkout-coupon">';
	echo '<div class="woocommerce-notices-wrapper"></div>'; // important: global wrapper class!
	echo '<form class="checkout_coupon woocommerce-form-coupon" method="post" style="display: block;">';
	echo '<p class="form-row form-row-first">';
	echo '<input type="text" name="coupon_code" class="input-text" placeholder="' . esc_attr__('Coupon code', 'woocommerce') . '" id="coupon_code" value="" />';
	echo '</p>';
	echo '<p class="form-row form-row-last">';
	echo '<button href="#coupon-notifications" type="submit" class="button" name="apply_coupon" value="' . esc_attr__('Apply coupon', 'woocommerce') . '">' . esc_html__('Apply coupon', 'woocommerce') . '</button>';
	echo '</p>';
	echo '<div class="clear"></div>';
	echo '</form>';
	echo '</div>';
}
add_action('woocommerce_review_order_before_payment', 'custom_checkout_coupon_before_payment_methods',10);


// add_filter('woocommerce_update_order_review_fragments', function($fragments) {
// 	ob_start();
// 	wc_print_notices();
// 	$fragments['.coupon-notifications'] = ob_get_clean(); // target your custom div
// 	return $fragments;
// });





add_filter('woocommerce_update_order_review_fragments', function($fragments) {
    ob_start();
    wc_print_notices();
    $fragments['.woocommerce-notices-wrapper'] = ob_get_clean();
    return $fragments;
});


add_filter('woocommerce_cart_item_name', function($name, $cart_item, $cart_item_key) {
    $product = $cart_item['data'];
    
    $regular_price = $product->get_regular_price();
    $sale_price    = $product->get_price();

    if ($regular_price > $sale_price) {
        $qty = $cart_item['quantity'];
        $total_discount = ($regular_price - $sale_price) * $qty;

        $discount_html = sprintf(
            '<br><small style="color:grey;">Discount: -%s</small>',
            wc_price($total_discount)
        );

        return $name . $discount_html;
    }

    return $name;
}, 10, 3);

