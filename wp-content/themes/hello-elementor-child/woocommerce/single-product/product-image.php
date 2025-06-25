<?php
defined('ABSPATH') || exit;


if (!function_exists('wc_get_gallery_image_html')) {
    return;
}

global $product;

?>
<h2 class="main-product-title"><?php the_title();?> </h2>

<?php
$columns           = apply_filters('woocommerce_product_thumbnails_columns', 4);
$post_thumbnail_id = $product->get_image_id();
$attachment_ids    = $product->get_gallery_image_ids();

$wrapper_classes = apply_filters(
    'woocommerce_single_product_image_gallery_classes',
    array(
        'woocommerce-product-gallery',
        'woocommerce-product-gallery--' . ($post_thumbnail_id ? 'with-images' : 'without-images'),
        'woocommerce-product-gallery--columns-' . absint($columns),
        'images',
    )
);
?>


<div class="<?php echo esc_attr(implode(' ', array_map('sanitize_html_class', $wrapper_classes))); ?>" data-columns="<?php echo esc_attr($columns); ?>">
    <div class="swiper woocommerce-swiper-gallery">
        <div class="swiper-wrapper">
            <?php
// Main image
if ($post_thumbnail_id) {
    echo '<div class="swiper-slide">';
    echo '<img src="' . esc_url(wp_get_attachment_url($post_thumbnail_id)) . '" alt="">';
    echo '</div>';
}

// Gallery images
if ($attachment_ids && $product->get_image_id()) {
    foreach ($attachment_ids as $attachment_id) {
        echo '<div class="swiper-slide">';
        echo '<img src="' . esc_url(wp_get_attachment_url($attachment_id)) . '" alt="">';
        echo '</div>';
    }
}
?>

            
        </div>

        <!-- Swiper navigation and pagination -->
        <div class="swiper-button-prev"><svg xmlns="http://www.w3.org/2000/svg" width="66" height="66" viewBox="0 0 66 66" fill="none"><circle cx="33" cy="33" r="33" fill="#212121"></circle><path d="M17.1335 38.4087L42.8806 50.3036V43.6222L24.5866 35.7738L24.7936 36.1314V35.2656L24.5866 35.6232L42.8806 27.7749V21.0934L17.1335 32.9883V38.4087Z" fill="#AA9677"></path></svg></div>
        <div class="swiper-button-next"><svg xmlns="http://www.w3.org/2000/svg" width="66" height="66" viewBox="0 0 66 66" fill="none"><circle cx="33" cy="33" r="33" transform="rotate(180 33 33)" fill="#212121"></circle><path d="M48.8665 27.5913L23.1194 15.6964V22.3778L41.4134 30.2262L41.2064 29.8686V30.7344L41.4134 30.3768L23.1194 38.2251V44.9066L48.8665 33.0117V27.5913Z" fill="#AA9677"></path></svg></div>
    </div>
 

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    new Swiper('.woocommerce-swiper-gallery', {
        loop: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
    });
});
</script>
