<?php

namespace App\TelsonApp\Reviews;

if (!defined('ABSPATH')) {
    exit;
}

use StoutLogic\AcfBuilder\FieldsBuilder;

class TA_Reviews
{
    private const CPT_NAME = 'product_review';

    public function __construct()
    {
        add_action('init', [$this, 'registerCPT']);
        add_action('acf/init', [$this, 'registerACFFields']);
    }

    public function registerCPT(): void
    {
        register_post_type(self::CPT_NAME, [
            'label' => __('Product Reviews', 'telson-app'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-star-filled',
            'capability_type' => 'post',
            'supports' => ['title', 'custom-fields'],
            'has_archive' => false,
        ]);
    }

    public function registerACFFields(): void
    {
        $fields = new FieldsBuilder('product_review_fields');

        $fields
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
            ]);

        $fields->setLocation('post_type', '==', self::CPT_NAME);

        acf_add_local_field_group($fields->build());
    }
}
