<?php

namespace App\TelsonApp\Shop;

if (!defined('ABSPATH')) {
    exit;
}

use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Class TA_ShopPage
 */
class TA_Shop
{
    public function __construct()
    {
        add_action('acf/init', [$this, 'acfFieldsShopPage']);
    }

    public function acfFieldsShopPage(): void
    {
        $shopPage = new FieldsBuilder('shop_page_content');

        $shopPage
            ->addImage('shop_banner', [
                'label' => 'Shop Banner',
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
                'wrapper' => ['width' => 100],
            ])
			 ->addWysiwyg( 'shop_content', [
				'label'        => 'Banner Content',
				'media_upload' => true,
				'tabs'         => 'all',
				'wrapper'      => [ 'width' => 100 ],
			] )
            ->addRepeater('reviews', [
                'label' => 'Reviews',
                'layout' => 'block',
                'button_label' => 'Add Review',
            ])
                ->addText('reviewer_name', [
                    'label' => 'Reviewer Name',
                ])
                ->addImage('profile', [
                    'label' => 'Profile',
                    'return_format' => 'array',
                    'preview_size' => 'thumbnail',
                ])
                ->addText('review_title', [
                    'label' => 'Review Title',
                ])
                ->addNumber('star_review', [
                    'label' => 'Star Review',
                    'min' => 1,
                    'max' => 5,
                    'step' => 1,
                ])
                ->addTextarea('review_content', [
                    'label' => 'Review',
                    'rows' => 5,
                ])
            ->endRepeater()

            ->addImage('wa_left', [
                'label' => 'Image',
                'return_format' => 'url',
                'preview_size' => 'thumbnail',
            ])
             ->addWysiwyg( 'wa_right', [
				'label'        => 'Content',
				'media_upload' => true,
				'tabs'         => 'all',
				'wrapper'      => [ 'width' => 100 ],
			] );

        // Get the Shop page ID set in WooCommerce settings
        $shop_page_id = (int) get_option('woocommerce_shop_page_id');

        if ($shop_page_id) {
            $shopPage->setLocation('page', '==', $shop_page_id);

            acf_add_local_field_group($shopPage->build());
        }
    }
}
