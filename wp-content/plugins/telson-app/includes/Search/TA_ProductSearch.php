<?php

namespace App\TelsonApp\Search;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TA_ProductSearch
 */
class TA_ProductSearch
{
    public function __construct()
    {
        add_action('wp_ajax_ta_product_search', [$this, 'handleAjaxSearch']);
        add_action('wp_ajax_nopriv_ta_product_search', [$this, 'handleAjaxSearch']);
        add_shortcode('ta_product_search', [$this, 'renderSearchShortcode']);
    }


    public function renderSearchShortcode(): string
    {
        ob_start(); ?>
        <div class="ta-product-search-wrapper" style="display: flex;">
            <input type="text" id="ta-product-search" placeholder="Search..." autocomplete="off" style="flex: 1;">
            <button id="ta-search-button" style="background: #b29a73; border: none; padding: 0 16px; display: flex; align-items: center; justify-content: center;">
            <svg aria-hidden="true" class="e-font-icon-svg e-fas-search" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"></path></svg>            </button>
            <ul id="ta-product-results" style="position: absolute; background: white; list-style: none; padding: 0; margin: 0; width: 100%; z-index: 999;"></ul>
        </div>
        <?php return ob_get_clean();
    }
    

    public function handleAjaxSearch(): void
    {
        $term = sanitize_text_field($_GET['term'] ?? '');
        if (!$term) {
            wp_send_json(['products' => [], 'posts' => []]);
        }
    
        // --- Products ---
        $product_args = [
            'post_type' => 'product',
            's' => $term,
            'posts_per_page' => 10,
        ];
        $product_query = new \WP_Query($product_args);
    
        $products = [];
        foreach ($product_query->posts as $post) {
            $product = wc_get_product($post->ID);
            $products[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'url' => get_permalink($post->ID),
                'image' => get_the_post_thumbnail_url($post->ID, 'thumbnail') ?: wc_placeholder_img_src(),
                'price' => $product ? $product->get_price_html() : '',
            ];
        }
    
        // --- Posts ---
        $post_args = [
            'post_type' => 'post',
            's' => $term,
            'posts_per_page' => 10,
        ];
        $post_query = new \WP_Query($post_args);
    
        $posts = [];
        foreach ($post_query->posts as $post) {
            $posts[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'url' => get_permalink($post->ID),
                'image' => get_the_post_thumbnail_url($post->ID, 'thumbnail') ?: 'https://via.placeholder.com/50',
                'price' => '',
            ];
        }
    
        wp_send_json([
            'products' => $products,
            'posts' => $posts,
        ]);
    }
    
    
    
}
