<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>




<?php
$shop_page_id = get_option('woocommerce_shop_page_id');
$shop_banner = get_field('shop_banner', $shop_page_id);
$shop_banner_content = get_field('shop_content', $shop_page_id);
if ($shop_banner) : ?>
    <div class="shop-banner">
        <img src="<?php echo esc_url($shop_banner); ?>" alt="Shop Banner">
		<div class="banner-wrapper">
			<?php echo $shop_banner_content; ?>
		</div>
    </div>
<?php endif; ?>



<?php
/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );

/**
 * Hook: woocommerce_shop_loop_header.
 *
 * @since 8.6.0
 *
 * @hooked woocommerce_product_taxonomy_archive_header - 10
 */
do_action( 'woocommerce_shop_loop_header' );

?>


<section class="prod-main">
    
<?php
if ( woocommerce_product_loop() ) {

	/**
	 * Hook: woocommerce_before_shop_loop.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	 */
	do_action( 'woocommerce_before_shop_loop' );

	woocommerce_product_loop_start();

	if ( wc_get_loop_prop( 'total' ) ) {
		while ( have_posts() ) {
			the_post();

			/**
			 * Hook: woocommerce_shop_loop.
			 */
			do_action( 'woocommerce_shop_loop' );

			wc_get_template_part( 'content', 'product' );
		}
	}

	woocommerce_product_loop_end();

	/**
	 * Hook: woocommerce_after_shop_loop.
	 *
	 * @hooked woocommerce_pagination - 10
	 */
	do_action( 'woocommerce_after_shop_loop' );
} else {
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	do_action( 'woocommerce_no_products_found' );
}

?>

</section>
<?php



/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );




$shop_page_id = (int) get_option('woocommerce_shop_page_id');

if (have_rows('reviews', $shop_page_id)) : ?>
<section class="main-reviews">
    <h2>PRODUCT REVIEWS</h2>
    <div class="shop-reviews">
        <?php while (have_rows('reviews', $shop_page_id)) : the_row(); 
            $reviewer_name = get_sub_field('reviewer_name');
            $profile = get_sub_field('profile'); // This returns an array since you set 'return_format' => 'array'
            $review_title = get_sub_field('review_title');
            $star_review = get_sub_field('star_review');
            $review_content = get_sub_field('review_content');
        ?>
            <div class="review">
                <div class="review-img">
                <?php if ($profile): ?>
                    <img src="<?= esc_url($profile['url']); ?>" alt="<?= esc_attr($reviewer_name); ?>" width="80" height="80">
                <?php endif; ?>
                </div>
                <div class="review-content">
                <div class="review-wrap">
                <h3><?= esc_html($review_title); ?></h3>
            
                <p><?= esc_html($review_content); ?></p>
                <p class="revi-stars"><?= str_repeat('⭐', (int)$star_review); ?></p>
                <strong><?= esc_html($reviewer_name); ?></strong>
                </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>
<?php endif; 


?>

<section class="warranty-public">

    <div class="wa-row">
        <div class="wa-left">
            <img src="<?php echo get_field('wa_left', $shop_page_id); ?>"/>
        </div>

         <div class="wa-right">
                <?php echo get_field('wa_right', $shop_page_id);?>
        </div>
     </div>
</section>


<?php


/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
