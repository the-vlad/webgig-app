<?php

namespace App\TelsonApp\Recommended;

if (!defined('ABSPATH')) {
    exit;
}

use StoutLogic\AcfBuilder\FieldsBuilder;

class TA_RecommendedProducts {

    public function __construct() {
        add_action('acf/init', [$this, 'registerOptionsPage']);
        add_action('acf/init', [$this, 'registerFields']);
        add_shortcode('recommended_products', [$this, 'renderRecommendedProducts']);
        add_action('wp_enqueue_scripts', [$this, 'conditionallyEnqueueSwiper']);
    }

    /**
     * Register the ACF Options Page
     */
    public function registerOptionsPage(): void {
        if (!function_exists('acf_add_options_page')) {
            return;
        }

        acf_add_options_page([
            'page_title' => 'Recommended Products Settings',
            'menu_title' => 'Recommended Products',
            'menu_slug'  => 'recommended-products',
            'capability' => 'manage_options',
            'position'   => 25,
            'icon_url'   => 'dashicons-products',
            'redirect'   => false,
        ]);
    }

    /**
     * Register ACF Fields using StoutLogic ACF Builder
     */
    public function registerFields(): void {
        $recommended = new FieldsBuilder('recommended_products');

        $recommended
            ->addPostObject('recommended_products_list', [
                'label'         => 'Recommended Products',
                'post_type'     => ['product'],
                'multiple'      => true,
                'return_format' => 'id',
                'ui'            => true,
            ])
		 ->addTrueFalse('isNew', [
            'label' => 'Is New',
            'ui'    => true,
        ])
            ->setLocation('options_page', '==', 'recommended-products');

        acf_add_local_field_group($recommended->build());
    }

    /**
     * Shortcode handler: [recommended_products]
     */
    public function renderRecommendedProducts(): string {
        if (!function_exists('get_field')) {
            return '';
        }

        $all_products = get_field('recommended_products_list', 'option');
        if (empty($all_products) || !is_array($all_products)) {
            return '<p>No recommended products set.</p>';
        }

        $current_id = get_the_ID();
        $filtered = array_filter($all_products, function ($id) use ($current_id) {
            return (int) $id !== (int) $current_id;
        });

        if (empty($filtered)) {
            return '<p>No recommended products to display.</p>';
        }

        ob_start();
        ?>
        <div class="swiper recommended-products-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($filtered as $product_id): ?>
                    <?php
                    $product = wc_get_product($product_id);
                    if (!$product) continue;
                    $thumb = get_the_post_thumbnail_url($product_id, 'medium');
                    ?>
                    <div class="swiper-slide" style="text-align: center;">
                    <div class="recommended-item">
				
                        <div class="recimg">
									<?php if (get_field('isNew', 'option')) : ?>
							<span class="label-new">New</span>
						<?php endif; ?>
                    <?php if ($thumb): ?>
                        <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" style="width: 100%; height: auto;">
                    <?php endif; ?>
                    </div>
                    <h4><?php echo esc_html($product->get_name()); ?></h4>
                    </div>
                    <div class="recbottom" style="margin-top: 10px;">
                        <span id="pricerec"><?php echo $product->get_price_html(); ?></span>
                        <a href="<?php echo get_permalink($product_id); ?>" class="shop-now-button" style="display: inline-block; padding: 8px 16px; background-color: #000; color: #fff; text-decoration: none; border-radius: 4px;">
                            Shop Now
                        </a>
                    </div>
                    
                </div>
                <?php endforeach; ?>
            </div>
        
        </div>
        <div class="swiper-button-prev"><svg width="180" height="180" viewBox="0 0 180 180" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M119 47.3166C119 48.185 118.668 48.9532 118.003 49.6212L78.8385 89L118.003 128.379C118.668 129.047 119 129.815 119 130.683C119 131.552 118.668 132.32 118.003 132.988L113.021 137.998C112.356 138.666 111.592 139 110.729 139C109.865 139 109.101 138.666 108.436 137.998L61.9966 91.3046C61.3322 90.6366 61 89.8684 61 89C61 88.1316 61.3322 87.3634 61.9966 86.6954L108.436 40.002C109.101 39.334 109.865 39 110.729 39C111.592 39 112.356 39.334 113.021 40.002L118.003 45.012C118.668 45.68 119 46.4482 119 47.3166Z" fill="black"></path></svg></div>
            <div class="swiper-button-next"><svg width="180" height="180" viewBox="0 0 180 180" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M119 47.3166C119 48.185 118.668 48.9532 118.003 49.6212L78.8385 89L118.003 128.379C118.668 129.047 119 129.815 119 130.683C119 131.552 118.668 132.32 118.003 132.988L113.021 137.998C112.356 138.666 111.592 139 110.729 139C109.865 139 109.101 138.666 108.436 137.998L61.9966 91.3046C61.3322 90.6366 61 89.8684 61 89C61 88.1316 61.3322 87.3634 61.9966 86.6954L108.436 40.002C109.101 39.334 109.865 39 110.729 39C111.592 39 112.356 39.334 113.021 40.002L118.003 45.012C118.668 45.68 119 46.4482 119 47.3166Z" fill="black"></path></svg></div>
        <?php
        return ob_get_clean();
    }

    /**
     * Enqueue Swiper only if shortcode is present
     */
    public function conditionallyEnqueueSwiper() {
        global $post;

        if (!is_a($post, 'WP_Post')) return;

        if (has_shortcode($post->post_content, 'recommended_products')) {
            wp_enqueue_style('swiper-css', 'https://unpkg.com/swiper/swiper-bundle.min.css');
            wp_enqueue_script('swiper-js', 'https://unpkg.com/swiper/swiper-bundle.min.js', [], null, true);
        }
    }
}
