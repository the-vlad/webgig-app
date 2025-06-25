<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
// wp_die();
get_header( 'shop' ); ?>

	<?php

		/**
		 * woocommerce_before_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		do_action( 'woocommerce_before_main_content' );
	?>
	<?php  ?>

		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>

			<?php wc_get_template_part( 'content', 'single-product' ); ?>

		<?php endwhile; // end of the loop. ?>
		<?php 
// echo do_shortcode('[acf_share_buttons]'); 

?>

	<?php
	
		/**
		 * woocommerce_after_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'woocommerce_after_main_content' );
	?>

	<?php
		/**
		 * woocommerce_sidebar hook.
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		do_action( 'woocommerce_sidebar' );
	?>

<section id="specification" class="product-tabs-section">

    <div class="container">
        <!-- Tab Buttons -->
        <div class="tab-buttons">
            <button class="tab-btn active" data-tab="specification">Specification</button>
            <button class="tab-btn" data-tab="reticle">Reticle</button>
            <button class="tab-btn" data-tab="description">Description</button>

			<div class="prod-divider"></div>
        </div>

        <!-- Tab Contents -->

        <div class="tab-content active" id="tab-specification">
            <?php
            $specification_image = get_field('specification_image');
            $left_content = get_field('left_specification');
            $right_content = get_field('right_specification');

            ?>

            <?php if ($specification_image || $left_content || $right_content): ?>
                <div class="specification-content">
                    <?php if (!empty($left_content)): ?>
                        <div class="left-content"><?php echo wp_kses_post($left_content); ?></div>
                    <?php endif; ?>

        
                        <div class="specification-image">
						<?php if ($specification_image): ?> <img src="<?php echo $specification_image; ?>" alt="" />
						<?php endif; ?>

						</div>

                    <?php if (!empty($right_content)): ?>
                        <div class="right-content"><?php echo wp_kses_post($right_content); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

		
		<div class="tab-content" id="tab-reticle">
    <?php
    $reticle_gallery = get_field('reticle_gallery');
    $reticle_document = get_field('reticle_documents');
    $reticle_details = get_field('reticle_details');
    ?>

<?php if (!empty($reticle_gallery)): ?>
    <div class="reticle-gallery" id="reticle-gallery">
        <?php foreach ($reticle_gallery as $image): ?>
            <a href="<?php echo esc_url($image['url']); ?>" 
               class="glightbox" 
               data-gallery="reticle"
               title="<?php echo esc_attr($image['alt']); ?>">
                <img src="<?php echo esc_url($image['url']); ?>" 
                     alt="<?php echo esc_attr($image['alt']); ?>" />
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>



    <?php if (!empty($reticle_document)): ?>
        <div class="reticle-document-link">
            <a href="<?php echo esc_url($reticle_document['url']); ?>" target="_blank" rel="noopener noreferrer">
                Click here to view a PDF document
            </a>
        </div>
    <?php endif; ?>

    <?php if (!empty($reticle_details)): ?>
        <div class="reticle-details">
            <?php echo wp_kses_post($reticle_details); ?>
        </div>
    <?php endif; ?>
</div>



		<div class="tab-content" id="tab-description">
			<?php
			$description_image = get_field('description_image');
			$description_left = get_field('description_left_content');
			$description_right = get_field('descripton_right_content'); // Note the typo in your field name: "descripton" (make sure it's consistent)
			?>

			<?php if ($description_image || $description_left || $description_right): ?>
				<div class="description-content">
					<?php if (!empty($description_left)): ?>
						<div class="left-content"><?php echo wp_kses_post($description_left); ?></div>
					<?php endif; ?>

	
						<div class="description-image">
						<?php if ($description_image): ?>
							<img src="<?php echo esc_url($description_image); ?>" />
							<?php endif; ?>
						</div>
				

					<?php if (!empty($description_right)): ?>
						<div class="right-content"><?php echo wp_kses_post($description_right); ?></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

    </div>
</section>

<!-- Reviews -->

<section class="reviews-ta">
<?php comments_template(); ?>
</section>


<!-- CTA -->

<section class="cta-ta">
	<div class="cta-row">
	<div class="cta-left">
		<h3><?php echo get_field('left_help_title', 'option'); ?></h3>
	</div>
	<div class="cta-mid">
	   <div class="cta-divider"></div>
	   <?php echo get_field('need_help', 'option'); ?>
	   <!-- <h3>need a moment?</h3>
	   <p>We understand! You can click the button below to save this product </br> to your account and access it at a later time.</p> -->
	
	   <a href="#" class="cta-save ">
		<svg aria-hidden="true" class="e-font-icon-svg e-far-bookmark" viewBox="0 0 384 512" xmlns="http://www.w3.org/2000/svg"><path d="M336 0H48C21.49 0 0 21.49 0 48v464l192-112 192 112V48c0-26.51-21.49-48-48-48zm0 428.43l-144-84-144 84V54a6 6 0 0 1 6-6h276c3.314 0 6 2.683 6 5.996V428.43z"></path></svg> 
		Save for Later
	</a>

	</div>
	<div class="cta-right">
			<div class="cta-shipping">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="37" height="36" viewBox="0 0 37 36" fill="none"><mask id="mask0_833_919" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="37" height="36"><rect x="0.369141" width="36" height="36" fill="url(#pattern0_833_919)"></rect></mask><g mask="url(#mask0_833_919)"><rect x="-60.5842" y="-7.80615" width="109.106" height="47.9586" fill="#AA9677"></rect></g><defs><pattern id="pattern0_833_919" patternContentUnits="objectBoundingBox" width="1" height="1"><use xlink:href="#image0_833_919" transform="scale(0.00444444)"></use></pattern><image id="image0_833_919" width="225" height="225" xlink:href="data:image/png;base64,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"></image></defs></svg>
			<h6><?php echo get_field('right_help_title', 'option');?></h6>		
		</div>
		
		<a href="https://staging12.telsonoptics.com/contact/" class="cta-btn">Need Help?</a>	
	</div>
</section>


<!-- Recommended products -->
<section class="ta-recommendations">
	<div class="ta-recommendations-wrapper">
	<h2>Recommended Products</h2>
	<?php echo do_shortcode('[recommended_products]'); ?>
	</div>
</section>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const productId = <?php echo get_the_ID(); ?>;
    let wishlist = JSON.parse(localStorage.getItem('custom_wishlist')) || [];

    const wishlistLinkHTML = `
        <a href="<?php echo site_url('/my-account/saved-items/'); ?>" class="btn-key view-saved-item">
            <svg aria-hidden="true" class="e-font-icon-svg e-fas-bookmark" viewBox="0 0 384 512" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 512V48C0 21.49 21.49 0 48 0h288c26.51 0 48 21.49 48 48v464L192 400 0 512z"></path>
            </svg>
            View saved item
        </a>
    `;

    // Handle .save4later button container
    const container = document.getElementById('wishlist-button-container');
    if (container) {
        if (wishlist.includes(productId)) {
            container.innerHTML = wishlistLinkHTML;
        } else {
            const saveButton = container.querySelector('.save4later');
            if (saveButton) {
                saveButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    wishlist.push(productId);
                    localStorage.setItem('custom_wishlist', JSON.stringify(wishlist));
                    container.innerHTML = wishlistLinkHTML;
                    updateCtaButton(); // sync cta button too
                });
            }
        }
    }

    // Handle .cta-save button separately
    const ctaSave = document.querySelector('.cta-save');
    function updateCtaButton() {
        if (!ctaSave) return;
        if (wishlist.includes(productId)) {
            ctaSave.outerHTML = `
                <a href="<?php echo site_url('/my-account/saved-items/'); ?>" class="cta-save">
                    <svg aria-hidden="true" class="e-font-icon-svg e-fas-bookmark" viewBox="0 0 384 512" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 512V48C0 21.49 21.49 0 48 0h288c26.51 0 48 21.49 48 48v464L192 400 0 512z"></path>
                    </svg> 
                    View saved item
                </a>
            `;
        } else {
            ctaSave.addEventListener('click', function (e) {
                e.preventDefault();
                wishlist.push(productId);
                localStorage.setItem('custom_wishlist', JSON.stringify(wishlist));
                updateCtaButton();
                if (container) container.innerHTML = wishlistLinkHTML; // sync other button
            });
        }
    }

    updateCtaButton();
});
</script>


        <script>
		document.addEventListener('DOMContentLoaded', function () {
			new Swiper('.recommended-products-swiper', {
				slidesPerView: 1,
				spaceBetween: 0,
				autoplay: {
					delay: 2400, // time between slides in ms
					disableOnInteraction: false, // keeps autoplay active after user interaction
				},
				loop:true,
				pagination: {
					el: '.swiper-pagination',
					clickable: true,
				},
				navigation: {
					nextEl: '.ta-recommendations .swiper-button-next',
					prevEl: '.ta-recommendations .swiper-button-prev',
				},
				breakpoints: {
					640: { slidesPerView: 1 },
					768: { slidesPerView: 2 },
					1024: { slidesPerView: 4 }
				}
			});
		});
        </script>


<?php
get_footer( 'shop' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
