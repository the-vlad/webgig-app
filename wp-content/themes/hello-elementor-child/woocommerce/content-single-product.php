<?php
defined('ABSPATH') || exit;

global $product;

do_action('woocommerce_before_single_product');

if (post_password_required()) {
    echo get_the_password_form();
    return;
}

// Enqueue required variation script
wp_enqueue_script('wc-add-to-cart-variation');
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>
    <div class="product-page-layout">

        <div class="product-gallery">
            <?php do_action('woocommerce_before_single_product_summary'); ?>
            <?php echo do_shortcode('[acf_share_buttons]'); ?>
        </div>

        <div class="product-summary2 summary entry-summary">
            <h1 class="product_title entry-title"><?php the_title(); ?></h1>

            <div class="woocommerce-product-details__short-description">
                <?php echo apply_filters('woocommerce_short_description', get_the_excerpt()); ?>
            </div>

            <div class="price-rating-wrapper" style="display: flex; align-items: center; gap: 1rem; margin-top: 1rem;">
                <p class="price" style="font-size: 2rem; font-weight: bold;">
                    <?php wc_get_template('single-product/price.php'); ?>
                </p>
                <div class="woocommerce-product-rating">
                    <?php if (wc_review_ratings_enabled() && $product->get_rating_count() > 0) : ?>
                        <?php echo wc_get_rating_html($product->get_average_rating()); ?>
                        <span class="count">(<?php echo $product->get_rating_count(); ?> reviews)</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="promo-title">
                <?php echo get_field('promotion_title'); ?>
            </div>

            <hr style="margin: 2rem 0;">

            <?php
            if ($product && $product->is_type('variable')) {
                do_action('woocommerce_before_add_to_cart_form');
                ?>

                <form class="variations_form cart"
                      method="post"
                      enctype="multipart/form-data"
                      data-product_id="<?php echo esc_attr($product->get_id()); ?>"
                      data-product_variations="<?php echo esc_attr(json_encode($product->get_available_variations())); ?>">
                    <?php
                    wc_get_template('single-product/add-to-cart/variable.php', [
                        'available_variations' => $product->get_available_variations(),
                        'attributes'           => $product->get_variation_attributes(),
                        'selected_attributes'  => $product->get_default_attributes(),
                    ]);
                    ?>
                </form>

                <?php
                do_action('woocommerce_after_add_to_cart_form');
            } elseif ($product->is_purchasable() && $product->is_in_stock()) {
                echo wc_get_stock_html($product);
                do_action('woocommerce_before_add_to_cart_form');
                ?>
                <form class="cart custom-qty-wrapper" method="post" enctype="multipart/form-data">
                    <div class="quantity-wrapper2" style="display: flex; align-items: center; gap: 10px;">
                        <div class="inputs-row">
                            <?php
                            do_action('woocommerce_before_add_to_cart_button');
                            do_action('woocommerce_before_add_to_cart_quantity');
                            ?>
                            <button type="button" class="qty-btn minus">&#65123;</button>
                            <?php
                            woocommerce_quantity_input([
                                'min_value'   => $product->get_min_purchase_quantity(),
                                'max_value'   => $product->get_max_purchase_quantity(),
                                'input_value' => $product->get_min_purchase_quantity(),
                            ]);
                            do_action('woocommerce_after_add_to_cart_quantity');
                            ?>
                            <button type="button" class="qty-btn plus">&#65122;</button>
                            <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>"
                                    id="simple-btn" class="single_add_to_cart_button button alt">
                                <?php echo esc_html($product->single_add_to_cart_text()); ?>
                            </button>
                        </div>
                    </div>
                    <?php do_action('woocommerce_after_add_to_cart_button'); ?>
                </form>
                <?php
                do_action('woocommerce_after_add_to_cart_form');
            }
            ?>

            <div class="key-features">
                <h2>Key features</h2>
                <div class="key-left"><?php echo get_field('description_left_content'); ?></div>
                <div class="key-right"><?php echo get_field('descripton_right_content'); ?></div>
            </div>

            <div class="key-buttons">
                <div id="wishlist-button-container">
                    <a href="#" class="save4later btn-key">Save for later</a>
                </div>
                <a href="#specification" class="btn-key">See Specification</a>
            </div>

            <?php
            $badges = get_field('product_badges', 'option');
            $badge_images = $badges[0] ?? [];
            $warranty_image = $badge_images['warranty'] ?? null;
            $show_warranty = get_field('warranty1');
            ?>

            <?php if ($warranty_image && $show_warranty) : ?>
                <div class="product-warranty-info">
                    <img src="<?php echo esc_url($warranty_image['url']); ?>" alt="<?php echo esc_attr($warranty_image['alt'] ?? 'Warranty'); ?>" />
                </div>
            <?php endif; ?>

            <div class="faq-section">
                <?php echo get_field('have_a_question', 'option'); ?>
            </div>

            <div class="badges">
                <div class="badges-row">
                    <?php
                    $badge_map = [
                        'argon1' => 'argon',
                        'ed_glass1' => 'ed_glass',
                        'megnification1' => 'magnification',
                        'ip671' => 'ip67',
                        'ballistic_turret_alert1' => 'ballistic_turret_alert',
                    ];

                    foreach ($badge_map as $acf_key => $img_key) {
                        if (get_field($acf_key) && !empty($badge_images[$img_key]['url'])) {
                            echo '<img src="' . esc_url($badge_images[$img_key]['url']) . '" alt="' . esc_attr($badge_images[$img_key]['alt'] ?? '') . '" class="badge-icon">';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // INIT variation form (important fix!)
    const variationForms = jQuery('.variations_form');
    if (variationForms.length) {
        variationForms.each(function () {
            jQuery(this).wc_variation_form();
        });
    }

    const productId = <?php echo get_the_ID(); ?>;
    const wishlist = JSON.parse(localStorage.getItem('custom_wishlist')) || [];
    const container = document.getElementById('wishlist-button-container');

    if (!container) return;

    if (wishlist.includes(productId)) {
        container.innerHTML = `
            <a href="<?php echo site_url('/my-account/saved-items/'); ?>" class="btn-key view-saved-item">
                <svg aria-hidden="true" class="e-font-icon-svg e-fas-bookmark" viewBox="0 0 384 512" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 512V48C0 21.49 21.49 0 48 0h288c26.51 0 48 21.49 48 48v464L192 400 0 512z"></path>
                </svg>
                View saved item
            </a>
        `;
    } else {
        const saveButton = container.querySelector('.save4later');
        if (saveButton) {
            saveButton.addEventListener('click', function (e) {
                e.preventDefault();
                wishlist.push(productId);
                localStorage.setItem('custom_wishlist', JSON.stringify(wishlist));
                container.innerHTML = `
                    <a href="<?php echo site_url('/my-account/saved-items/'); ?>" class="btn-key view-saved-item">
                        <svg aria-hidden="true" class="e-font-icon-svg e-fas-bookmark" viewBox="0 0 384 512" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 512V48C0 21.49 21.49 0 48 0h288c26.51 0 48 21.49 48 48v464L192 400 0 512z"></path>
                        </svg>
                        View saved item
                    </a>
                `;
            });
        }
    }
});
</script>

<?php do_action('woocommerce_after_single_product'); ?>
