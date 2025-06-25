<?php

namespace App\TelsonApp\SingleProduct;

if (!defined('ABSPATH')) {
    exit;
}

use StoutLogic\AcfBuilder\FieldsBuilder;

class TA_SingleProduct
{
    private $fieldsDefinition = null;

    public function __construct()
    {
        add_action('acf/init', [$this, 'acfFieldsSingleProduct']);
    }

    public function acfFieldsSingleProduct(): void
    {
        $singleProduct = new FieldsBuilder('single_product_content');

        // === Static field: Tag ===
        $singleProduct->addSelect('product_label', [
            'label' => 'Tag - Rifle Scopes Section (Home Page):',
            'choices' => [
                ''         => 'Select Label',
                'new'      => 'New',
                'sold_out' => 'Sold Out',
            ],
            'default_value' => '',
            'return_format' => 'label',
            'wrapper' => ['width' => 100],
        ]);

        // === 🔁 Dynamically generated selects ===
        $attribute_posts = get_posts([
            'post_type' => 'product_attribute',
            'numberposts' => -1,
        ]);

        foreach ($attribute_posts as $attribute_post) {
            $field_name  = sanitize_title($attribute_post->post_title);
            $field_label = get_field('attribute_label', $attribute_post->ID) ?: $attribute_post->post_title;
        
            $choices = [];
            $rows = get_field('attribute_choices_group', $attribute_post->ID);
        
            if ($rows) {
                foreach ($rows as $row) {
                    // $value = $row['value'] ?? '';
                    $label = $row['label'];
                    $value = $label;
                    if ($value) {
                        $choices[$value] = $label;
                    }
                }
            }
        
            $singleProduct->addSelect($field_name, [
                'label' => $field_label,
                'choices' => $choices,
                'default_value' => null,
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'wrapper' => ['width' => 20],
            ]);
        }
        

        // === Static True/False + Text fields ===
        $singleProduct
            ->addText('promotion_title', [
                'label' => 'Promotion Title',
                'wrapper' => ['width' => 100],
            ])
            ->addTrueFalse('argon1', [
                'label' => 'Argon',
                'ui' => 1,
                'default_value' => 0,
                'wrapper' => ['width' => 33],
            ])
            ->addTrueFalse('ed_glass1', [
                'label' => 'ED Glass',
                'ui' => 1,
                'default_value' => 0,
                'wrapper' => ['width' => 33],
            ])
            ->addTrueFalse('megnification1', [
                'label' => 'Magnification',
                'ui' => 1,
                'default_value' => 0,
                'wrapper' => ['width' => 33],
            ])
            ->addTrueFalse('ip671', [
                'label' => 'IP67',
                'ui' => 1,
                'default_value' => 0,
                'wrapper' => ['width' => 33],
            ])
            ->addTrueFalse('warranty1', [
                'label' => 'Warranty',
                'ui' => 1,
                'default_value' => 0,
                'wrapper' => ['width' => 33],
            ])
            ->addTrueFalse('ballistic_turret_alert1', [
                'label' => 'Ballistic Turret Alert',
                'ui' => 1,
                'default_value' => 0,
                'wrapper' => ['width' => 33],
            ]);

        // === Description Tab ===
        $singleProduct
            ->addTab('Description', ['placement' => 'top'])
            ->addImage('description_image', [
                'label' => 'Image',
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
                'wrapper' => ['width' => 100],
            ])
            ->addWysiwyg('description_left_content', [
                'label' => 'Left Content',
                'media_upload' => true,
                'tabs' => 'all',
                'wrapper' => ['width' => 50],
            ])
            ->addWysiwyg('descripton_right_content', [
                'label' => 'Right Content',
                'media_upload' => true,
                'tabs' => 'all',
                'wrapper' => ['width' => 50],
            ]);

        // === Specification Tab ===
        $singleProduct
            ->addTab('Specification', ['placement' => 'top'])
            ->addImage('specification_image', [
                'label' => 'Image',
                'name' => 'specification_image',
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
                'wrapper' => ['width' => 100],
            ])
            ->addWysiwyg('left_specification', [
                'label' => 'Left Specification',
                'media_upload' => true,
                'tabs' => 'all',
                'wrapper' => ['width' => 50],
            ])
            ->addWysiwyg('right_specification', [
                'label' => 'Right Specification',
                'media_upload' => true,
                'tabs' => 'all',
                'wrapper' => ['width' => 50],
            ]);

        // === Reticle Tab ===
        $singleProduct
            ->addTab('Reticle', ['placement' => 'top'])
            ->addImage('reticle_image', [
                'label' => 'Reticle Image',
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
                'wrapper' => ['width' => 50],
            ])
            ->addGallery('reticle_gallery', [
                'label' => 'Gallery',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
                'wrapper' => ['width' => 50],
            ])
            ->addFile('reticle_documents', [
                'label' => 'Documents',
                'return_format' => 'array',
                'library' => 'all',
                'wrapper' => ['width' => 100],
            ])
            ->addWysiwyg('reticle_details', [
                'label' => 'Details',
                'media_upload' => true,
                'tabs' => 'all',
                'wrapper' => ['width' => 100],
            ]);

        $singleProduct->setLocation('post_type', '==', 'product');

        $this->fieldsDefinition = $singleProduct;
        acf_add_local_field_group($singleProduct->build());
    }
}
