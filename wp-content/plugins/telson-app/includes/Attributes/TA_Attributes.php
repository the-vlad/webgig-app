<?php

namespace App\TelsonApp\Attributes;

if (!defined('ABSPATH')) {
    exit;
}

use StoutLogic\AcfBuilder\FieldsBuilder;

class TA_Attributes
{
    private const CPT_NAME = 'product_attribute';

    public function __construct()
    {
        add_action('init', [$this, 'registerCPT']);
        add_action('acf/init', [$this, 'registerACFFields']);
    }

    public function registerCPT(): void
    {
        register_post_type(self::CPT_NAME, [
            'label' => __('Product Attributes', 'telson-app'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 30,
            'menu_icon' => 'dashicons-list-view',
            'supports' => ['title', 'custom-fields'],
            'has_archive' => false,
            'capability_type' => 'post',

        ]);
    }

    public function registerACFFields(): void
    {
        $fields = new FieldsBuilder('product_attribute_fields_group');

        $fields
            ->addText('attribute_label', [
                'label' => 'Attribute Label',
            ])

            ->addRepeater( 'attribute_choices_group', [
				'label' => 'Choices',
				'button_label' => 'Add Choice',
			] )

            // ->addText('value', ['label' => 'Value'])
            ->addText('label', ['label' => 'Label'])
			
			->endRepeater();

        $fields->setLocation('post_type', '==', self::CPT_NAME);

        acf_add_local_field_group($fields->build());
    }
}
