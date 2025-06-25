<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://github.com/sweazy4a/
 * @since             1.1.9
 * @package           Telson_App
 *
 * @wordpress-plugin
 * Plugin Name:       Telson App Plugin
 * Plugin URI:        https://github.com/sweazy4a/
 * Description:       Custom telson features
 * Version:           1.1.9
 * Author:            Vlad
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       telson_app
 */

use App\TelsonApp\TA_Init;

if (!defined('ABSPATH')) {
    die;
}

// Composer autoloader
if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Define constants
define('TA_VERSION', '1.1.9');
define('TA_DIR', plugin_dir_path(dirname(__FILE__)) . 'telson-app/');
define('TA_URL', plugin_dir_url(dirname(__FILE__)) . 'telson-app/');
define('TA_AJAX', admin_url('admin-ajax.php'));
define('TA_WP_URL', get_site_url());
define('TA_DATE_FORMAT', 'Y-m-d');



if (!class_exists('ACF')) {
    define('MY_ACF_PATH', TA_DIR . '/vendor/advanced-custom-fields/');
    define('MY_ACF_URL', TA_URL . '/vendor/advanced-custom-fields/');

    include_once(MY_ACF_PATH . 'acf.php');

    add_filter('acf/settings/url', function ($url) {
        return MY_ACF_URL;
    });

    add_filter('acf/settings/show_admin', function ($show_admin) {
        return false; 
    });
}
add_action( 'wp_enqueue_scripts', function() {
    wp_dequeue_script( 'wc-add-to-cart-variation' ); // if using variable product
    wp_dequeue_script( 'wc-add-to-cart' );
    wp_dequeue_script( 'wc-cart-fragments' );
}, 100 );



add_filter( 'body_class', 'add_wc_error_body_class' );
function add_wc_error_body_class( $classes ) {
    if ( ! is_checkout() ) {
        return $classes;
    }

    // WooCommerce stores notices in a session — this works for both errors and validation notices.
    if ( wc_notice_count( 'error' ) > 0 ) {
        $classes[] = 'woocommerce-checkout-has-errors';
    }

    return $classes;
}


function enqueue_choices_js_assets() {
    // Enqueue Choices.js CSS
    wp_enqueue_style(
        'choicesjs-style',
        'https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css',
        array(),
        null
    );

    // Enqueue Choices.js JS
    wp_enqueue_script(
        'choicesjs-script',
        'https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js',
        array(), // No jQuery dependency
        null,
        true // Load in footer
    );
}
add_action('wp_enqueue_scripts', 'enqueue_choices_js_assets');



function init_choices_js_for_telson_filters() {
    ?>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const selects = document.querySelectorAll('.telson-select-filter');
        selects.forEach(function(select) {
          if (!select.classList.contains('choices-initialized')) {
            new Choices(select, {
              removeItemButton: true,
              placeholder: true,
              placeholderValue: select.getAttribute('data-placeholder') || '',
              shouldSort: false
            });
            select.classList.add('choices-initialized');
          }
        });
      });
    </script>
    <?php
}
add_action('wp_footer', 'init_choices_js_for_telson_filters', 20);


add_action('wp_enqueue_scripts', 'ta_enqueue_swiper_assets');

function ta_enqueue_swiper_assets() {
    // Swiper CSS
    wp_enqueue_style(
        'ta-swiper-style',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
        [],
        '11.0.0'
    );

    // Swiper JS
    wp_enqueue_script(
        'ta-swiper-script',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
        [],
        '11.0.0',
        true // Load in footer
    );
}





add_action('wp_ajax_telson_filter_products2', 'handle_telson_ajax_filter2');
add_action('wp_ajax_nopriv_telson_filter_products2', 'handle_telson_ajax_filter2');

function handle_telson_ajax_filter2() {
    // check_ajax_referer('telson_filter_nonce', 'nonce');

    $args = [
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'tax_query'      => [],
        'meta_query'     => [],
    ];

    // Price filter
    if (!empty($_POST['min_price']) && !empty($_POST['max_price'])) {
        $args['meta_query'][] = [
            'key'     => '_price',
            'value'   => [floatval($_POST['min_price']), floatval($_POST['max_price'])],
            'compare' => 'BETWEEN',
            'type'    => 'NUMERIC',
        ];
    }

    // Taxonomy filters
    foreach ($_POST as $key => $values) {
        if (strpos($key, 'filter_') === 0 && is_array($values)) {
            $taxonomy = sanitize_text_field(str_replace('filter_', '', $key));
            $term_ids = $values[0][0];

            if (taxonomy_exists($taxonomy)) {
                $args['tax_query'][] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $term_ids,
                    'operator' => 'IN',
                ];
            }
        }
    }


 // Process ACF filters
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'acf_filter_') === 0) {
            $acf_field = str_replace('acf_filter_', '', $key);
            
            // Handle both single and multiple values
            $values = is_array($value) ? $value : [$value];
            $values = array_filter($values); // Remove empty values
            
            if (!empty($values)) {
                $args['meta_query'][] = [
                    'key' => $acf_field,
                    'value' => $values,
                    'compare' => 'IN'
                ];
            }
        }
    }



    $query = new WP_Query($args);

    ob_start();

    echo '<div class="ta-loader-wrapper"><div class="ta-loader"><div class="spinner"></div></div></div>';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $product;

            echo '<div class="product ta-product">';
            echo '<a href="' . get_permalink() . '">' . get_the_post_thumbnail(get_the_ID(), 'woocommerce_thumbnail') . '</a>';
            echo '<h2>' . get_the_title() . '</h2>';
            echo '<span class="price">' . $product->get_price_html() . '</span>';

            // Add to cart or select options button
            if ($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) {
                echo apply_filters('woocommerce_loop_add_to_cart_link',
                    sprintf(
                        '<a href="%s" data-quantity="1" class="button ajax_add_to_cart add_to_cart_button product_type_simple" data-product_id="%s" data-product_sku="%s" aria-label="%s" rel="nofollow">%s</a>',
                        esc_url($product->add_to_cart_url()),
                        esc_attr($product->get_id()),
                        esc_attr($product->get_sku()),
                        esc_attr($product->add_to_cart_description()),
                        esc_html($product->add_to_cart_text())
                    ),
                    $product
                );
            } elseif ($product->is_type('variable')) {
                echo sprintf(
                    '<a href="%s" class="button product_type_variable">%s</a>',
                    esc_url(get_permalink($product->get_id())),
                    esc_html__('Select options', 'woocommerce')
                );
            }

            echo '</div>';
        }
    } else {
        echo '<p>No products found.</p>';
    }

    wp_reset_postdata();

    wp_send_json_success(['html' => ob_get_clean()]);
}



function load_jquery_ui_from_cdn() {
    wp_enqueue_script('jquery');

    wp_deregister_script('jquery-ui-core');
    wp_deregister_script('jquery-ui-effects');
    
    // Load jQuery UI and Effects from CDN
    wp_enqueue_script(
        'jquery-ui-core',
        'https://code.jquery.com/ui/1.13.2/jquery-ui.min.js',
        array('jquery'),
        '1.13.2',
        true
    );

    wp_enqueue_style(
        'jquery-ui-css',
        'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css'
    );
}
add_action('wp_enqueue_scripts', 'load_jquery_ui_from_cdn');


add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('nouislider', 'https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.8.1/nouislider.min.js', [], null, true);
    wp_enqueue_style('nouislider-css', 'https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.8.1/nouislider.css');

    wp_enqueue_script('telson-menu', plugin_dir_url(__FILE__) . 'js/menu.js', ['jquery'], TA_VERSION, true);

    wp_enqueue_script('ta-product-search', plugin_dir_url(__FILE__) . 'js/ta-product-search.js', ['jquery'], TA_VERSION, true);


    wp_localize_script('ta-product-search', 'TAProductSearch', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);

    wp_enqueue_script('glight-init', plugin_dir_url(__FILE__) . 'js/glight.js', ['jquery'], TA_VERSION, true);
    wp_enqueue_script('telson-qty', plugin_dir_url(__FILE__) . 'js/custom-qty-buttons.js', ['jquery'], TA_VERSION, true);
    wp_enqueue_script('telson-tabs', plugin_dir_url(__FILE__) . 'js/product-tabs.js', ['jquery'], TA_VERSION, true);

    wp_enqueue_script('telson-filters', plugin_dir_url(__FILE__) . 'js/filters.js', ['nouislider'], TA_VERSION, true);
    wp_localize_script('telson-filters', 'telson_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('telson_filter_nonce'),
    ]);
   
    wp_enqueue_style('ta-search-style', TA_URL . 'css/search.css', [], TA_VERSION);
    wp_enqueue_style('ta-woocommerce-style', TA_URL . 'css/woocommerce.css', [], TA_VERSION);
    wp_enqueue_style('ta-warranty-style', TA_URL . 'css/warranty.css', [], TA_VERSION);
    wp_enqueue_style('ta-registration-style', TA_URL . 'css/registration.css', [], TA_VERSION);
    wp_enqueue_style('ta-filter-style', TA_URL . 'css/filters.css');
    wp_enqueue_style('ta-menu-style', TA_URL . 'css/menu.css');
	wp_enqueue_style('ta-responsive-style', TA_URL . 'css/responsive.css', [], TA_VERSION);
    wp_enqueue_script('ta-script', TA_URL . 'js/script.js', ['jquery'], TA_VERSION, true);
});




// Disable WooCommerce product image zoom, lightbox, and slider
add_action('after_setup_theme', 'disable_wc_gallery_features', 100);
function disable_wc_gallery_features() {
    remove_theme_support('wc-product-gallery-zoom');
    // Optional: remove the lightbox and slider too
    // remove_theme_support('wc-product-gallery-lightbox');
    // remove_theme_support('wc-product-gallery-slider');
}

if (is_search()) {
    wp_die();
 
}



require_once TA_DIR . 'includes/Menu/MegaMenuWalker.php';

require_once TA_DIR . 'includes/Menu/Shortcodes.php';

// require_once TA_DIR . 'includes/helper-functions.php';


// Initialize plugin classes
TA_Init::register();


function hide_admin_bar_on_mobile() {
    if (wp_is_mobile()) {
        add_filter('show_admin_bar', '__return_false');
    }
}
add_action('after_setup_theme', 'hide_admin_bar_on_mobile');

add_action('init', function () {
    if (is_admin()) {
        show_admin_bar(false);
    }
});




add_action('gform_after_submission_6', function ($entry, $form) {
    $user_id = get_current_user_id();
    if ($user_id) {
        update_user_meta($user_id, '_warranty_request_entry_id', $entry['id']);
    }
}, 10, 2);

function display_user_warranty_submission() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view your warranty request.</p>';
    }

    $user_id = get_current_user_id();
    $entry_id = get_user_meta($user_id, '_warranty_request_entry_id', true);

    if (!$entry_id) {
        return '<p class="warranty-notice">You have not submitted a warranty request yet.</p>';
    }

    $entry = GFAPI::get_entry($entry_id);
    if (is_wp_error($entry)) {
        return '<p class="warranty-notice">There was an error retrieving your warranty request.</p>';
    }

    $form = GFAPI::get_form(6);
    if (is_wp_error($form)) {
        return '<p class="warranty-notice">Form not found.</p>';
    }

    $output = '<div class="my-warranty">';
    $output .= '<h3>Your Submitted Warranty Request</h3><ul>';

    foreach ($form['fields'] as $field) {
        $field_id = $field->id;
        $label = $field->label;
        $value = rgar($entry, (string)$field_id);

        if (!empty($value)) {
            $output .= '<li><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</li>';
        }
    }

    $output .= '</ul></div>';
    return $output;
}
add_shortcode('my_warranty', 'display_user_warranty_submission');

function enqueue_glightbox_assets() {
    wp_enqueue_style('glightbox-css', 'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css');
    wp_enqueue_script('glightbox-js', 'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_glightbox_assets');



function custom_elementor_share_buttons_shortcode($atts = []) {
    // Set default attributes if needed
    $atts = shortcode_atts([], $atts, 'elementor_share_buttons');

    // Setup widget settings (match these with what Elementor expects)
    $settings = [
        'view' => 'icon-text', // icon, text, or icon-text
        'skin' => 'gradient', // gradient, framed, boxed, etc.
        'shape' => 'square', // square, circle
        'columns' => 4,
        'open_in_new_window' => 'yes',
        'alignment' => 'center',
        'gap' => 'default',
        'share_buttons' => [
            [
                '_id' => 'share_facebook',
                'button_type' => 'facebook',
            ],
            [
                '_id' => 'share_twitter',
                'button_type' => 'twitter',
            ],
            [
                '_id' => 'share_linkedin',
                'button_type' => 'linkedin',
            ],
        ],
    ];

    // Render the widget
    $widget = new \Elementor\Widget_Share_Buttons();
    $widget->set_settings($settings);

    ob_start();
    $widget->render();
    return ob_get_clean();
}
add_shortcode('elementor_share_buttons', 'custom_elementor_share_buttons_shortcode');


add_filter('acf/fields/post_object/query/name=recommended_products_list', function ($args, $field, $post_id) {
    $args['post_status'] = 'publish';
    return $args;
}, 10, 3);





add_action( 'wp_enqueue_scripts', function() {
    if ( is_product() ) {
        wp_enqueue_script( 'wc-add-to-cart-variation' );
    }
});





add_action('template_redirect', function () {
    if (
        is_user_logged_in() &&
        is_account_page() &&
        !is_wc_endpoint_url() &&
        !is_page('dashboard') &&
        $_SERVER['REQUEST_URI'] === '/my-account/'
    ) {
        wp_redirect(home_url('/my-account/dashboard'));
        exit;
    }
});




/* Shortcodes */

// Add to your theme's functions.php or a custom plugin

function my_custom_account_info_shortcode() {
    if ( ! is_user_logged_in() ) return 'Please log in to view your account information.';

    $user = wp_get_current_user();
    ob_start();
    ?>
    <div class="my-account-info">
        <div class="ma-contact">
            <h2>Contact Information</h2>
            <p><?php echo esc_html( $user->display_name ); ?></p>
            <p><?php echo esc_html( $user->user_email ); ?></p>
            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>" class="button">Edit</a>
            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>" class="button">Change Password</a>
        </div>

        <div class="ma-join">
            <h2>Join The Bold</h2>
            <p>You are subscribed to our core insider newsletter</p>
            <a href="<?php echo home_url( 'my-account/inside-newsletter/' ); ?>" class="button">Edit</a>
        </div>

        <div class="ma-logout">
            <a href="<?php echo esc_url( wc_logout_url() ); ?>" class="button logout-button">Log Out</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'my_account_info', 'my_custom_account_info_shortcode' );



function my_custom_address_info_shortcode() {
    if ( ! is_user_logged_in() ) return 'Please log in to view your address information.';

    $user_id = get_current_user_id();
    ob_start();

    $billing = wc_get_account_formatted_address( 'billing', $user_id );
    $shipping = wc_get_account_formatted_address( 'shipping', $user_id );
    ?>

    <div class="my-address-info">
        <span>The following addresses will be used on the checkout page by default.</span>

        <div class="ma-ad">
        <div class="ma-ad-left address-section">
            <strong>Billing address</strong>
            <p><?php echo $billing ? $billing : 'You have not set up this type of address yet.'; ?></p>
            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address/billing' ) ); ?>">Edit</a>
        </div>

        <div class="ma-ad-right address-section">
            <strong>Shipping address</strong>
            <p><?php echo $shipping ? $shipping : 'You have not set up this type of address yet.'; ?></p>
            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address/shipping' ) ); ?>">Add</a>
        </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode( 'my_address_info', 'my_custom_address_info_shortcode' );


add_action( 'template_redirect', function() {
    if (
        is_page('account-information') && // Change to your page slug or use is_page(ID)
        is_user_logged_in() &&
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['action'], $_POST['_wpnonce']) &&
        $_POST['action'] === 'save_account_details' &&
        wp_verify_nonce( $_POST['_wpnonce'], 'save_account_details' )
    ) {
        $user_id = get_current_user_id();

        $first_name   = sanitize_text_field( $_POST['account_first_name'] ?? '' );
        $last_name    = sanitize_text_field( $_POST['account_last_name'] ?? '' );
        $display_name = sanitize_text_field( $_POST['account_display_name'] ?? '' );
        $email        = sanitize_email( $_POST['account_email'] ?? '' );

        $errors = new WP_Error();

        // Validate email
        if ( ! is_email( $email ) ) {
            $errors->add( 'email', __( 'Please provide a valid email address.', 'woocommerce' ) );
        } elseif ( email_exists( $email ) && email_exists( $email ) !== $user_id ) {
            $errors->add( 'email_exists', __( 'This email address is already registered.', 'woocommerce' ) );
        }

        // Add more validation if needed

        if ( ! $errors->has_errors() ) {
            // Update user info
            wp_update_user( [
                'ID'           => $user_id,
                'user_email'   => $email,
                'display_name' => $display_name,
                'first_name'   => $first_name,
                'last_name'    => $last_name,
            ]);

            wc_add_notice( __( 'Account details changed successfully.', 'woocommerce' ), 'success' );

            // Redirect back to the same page to avoid resubmission
            wp_safe_redirect( get_permalink() );
            exit;
        } else {
            foreach ( $errors->get_error_messages() as $message ) {
                wc_add_notice( $message, 'error' );
            }
        }
    }
});

function my_custom_edit_account_shortcode() {
    if ( ! is_user_logged_in() ) {
        return 'Please log in to edit your account.';
    }

    $current_user = wp_get_current_user();

    ob_start();

    wc_print_notices();

    // Pass current user data to the template
    wc_get_template( 'myaccount/form-edit-account.php', array(
        'user' => $current_user,
    ) );

    return ob_get_clean();
}
add_shortcode( 'my_edit_account', 'my_custom_edit_account_shortcode' );


// function my_custom_edit_account_shortcode() {
//     if ( ! is_user_logged_in() ) {
//         return 'Please log in to edit your account.';
//     }

//     ob_start();

//     // Load the WooCommerce Edit Account form template
//     wc_get_template( 'myaccount/form-edit-account.php' );

//     return ob_get_clean();
// }
// add_shortcode( 'my_edit_account', 'my_custom_edit_account_shortcode' );


function my_account_order_history_shortcode() {
    if ( ! is_user_logged_in() ) {
        return 'Please log in to view your orders.';
    }

    ob_start();

    $current_user = wp_get_current_user();
    $customer_orders = wc_get_orders([
        'customer_id' => $current_user->ID,
        'limit'       => -1,
        'orderby'     => 'date',
        'order'       => 'DESC',
    ]);

    if ( empty( $customer_orders ) ) {
        echo '<div class="no-order"><p>No order has been made yet.</p><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">Browse products</a></div>';
    } else {
        echo '<table class="woocommerce-orders-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';

        foreach ( $customer_orders as $order ) {
            echo '<tr>';
            echo '<td><a href="' . esc_url( $order->get_view_order_url() ) . '">#' . esc_html( $order->get_order_number() ) . '</a></td>';
            echo '<td>' . esc_html( wc_format_datetime( $order->get_date_created() ) ) . '</td>';
            echo '<td>' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</td>';
            echo '<td>' . wp_kses_post( $order->get_formatted_order_total() ) . '</td>';
            echo '<td><a class="button" href="' . esc_url( $order->get_view_order_url() ) . '">View</a></td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    return ob_get_clean();
}
add_shortcode( 'my_order_history', 'my_account_order_history_shortcode' );



add_shortcode('custom_wishlist', 'render_custom_wishlist');

function render_custom_wishlist() {
    ob_start();
    ?>
    <div id="custom-wishlist">
        <div class="wishlist-products"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const wishlistContainer = document.querySelector('.wishlist-products');
        let wishlist = JSON.parse(localStorage.getItem('custom_wishlist')) || [];

        if (wishlist.length === 0) {
            wishlistContainer.innerHTML = '<p class="wishlist-note">Your wishlist is empty.</p>';
            return;
        }

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'get_wishlist_products',
                product_ids: wishlist.join(',')
            })
        })
        .then(res => res.text())
        .then(data => {
            wishlistContainer.innerHTML = data;

            // Attach remove button listeners
            document.querySelectorAll('.remove-wishlist-item').forEach(btn => {
                btn.addEventListener('click', function () {
                    const productId = this.dataset.productId;
                    wishlist = wishlist.filter(id => id != productId);
                    localStorage.setItem('custom_wishlist', JSON.stringify(wishlist));
                    location.reload();
                });
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_action('wp_ajax_get_wishlist_products', 'get_wishlist_products');
add_action('wp_ajax_nopriv_get_wishlist_products', 'get_wishlist_products');

function get_wishlist_products() {
    $ids = isset($_POST['product_ids']) ? explode(',', sanitize_text_field($_POST['product_ids'])) : [];

    if (empty($ids)) {
        wp_die();
    }

    foreach ($ids as $id) {
        $product = wc_get_product($id);
        if ($product) {
            echo '<div class="wishlist-item" style="border: 1px solid #ddd; margin: 10px; padding: 10px;">';
            echo '<button class="remove-wishlist-item" data-product-id="' . esc_attr($id) . '"><svg aria-hidden="true" class="e-font-icon-svg e-fas-trash-alt" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path></svg>Remove</button>';
            echo '<a href="' . get_permalink($id) . '">' . $product->get_image() . '<br>' . esc_html($product->get_name()) . '</a>';
            echo '<div style="margin-top: 10px;">';
            echo '</div>';
            echo '<div style="margin-top: 10px;">';
            echo do_shortcode('[add_to_cart id="' . esc_attr($id) . '"]');
            echo '</div>';
            echo '</div>';
        }
    }
    wp_die();
}


add_action('template_redirect', function() {
    if ( is_account_page() && is_wc_endpoint_url('edit-account') ) {
        wp_safe_redirect( site_url('/my-account/account-information/') );
        exit;
    }
});


function dc_show_new_products_first( $query ) {
    if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'product' ) ) {
        $query->set( 'orderby', 'date' );
        $query->set( 'order', 'DESC' );
    }
}
add_action( 'pre_get_posts', 'dc_show_new_products_first' );




// add_action('wp_ajax_nopriv_custom_create_account', 'custom_ajax_create_account');
// function custom_ajax_create_account() {
//     $email = sanitize_email($_POST['billing_email']);
//     $first = sanitize_text_field($_POST['billing_first_name']);
//     $last = sanitize_text_field($_POST['billing_last_name']);
//     $username = sanitize_user(current(explode('@', $email)));
//     $password = wp_generate_password();

//     if (email_exists($email)) {
//         wp_send_json(['success' => false, 'message' => 'Email already exists.']);
//     }

//     $user_id = wc_create_new_customer($email, $username, $password);

//     if (is_wp_error($user_id)) {
//         wp_send_json(['success' => false, 'message' => $user_id->get_error_message()]);
//     }

//     wp_set_current_user($user_id);
//     wc_set_customer_auth_cookie($user_id);
//     wp_send_json(['success' => true, 'message' => 'Account created and logged in!']);
// }



/* Warranty feature */

// 5. Dynamically Populate Product Types in Gravity Form
// add_filter('gform_pre_render_6', 'populate_product_types');
// add_filter('gform_pre_validation_6', 'populate_product_types');
// add_filter('gform_pre_submission_filter_6', 'populate_product_types');
// add_filter('gform_admin_pre_render_6', 'populate_product_types');

// function populate_product_types($form) {
//     foreach ($form['fields'] as &$field) {
//         if ($field->type !== 'select' || $field->inputName !== 'product_types') {
//             continue;
//         }

//         $field->choices = [
//             ['text' => 'Riflescope', 'value' => 'Riflescope'],
//             ['text' => 'Binocular', 'value' => 'Binocular'],
//             ['text' => 'Mount', 'value' => 'Mount'],
//             ['text' => 'Rangefinder', 'value' => 'Rangefinder'],
//         ];
//     }

//     return $form;
// }