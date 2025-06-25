<?php

namespace App\TelsonApp\Filters;

if (!defined('ABSPATH')) {
    exit;
}

use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Class FA_SmartFilter
 */
class TA_Filters
{
    private const CPT_NAME = 'custom_filters';

    public function __construct()
    {
        add_action('init', [$this, 'registerCptSmartFilters']);
        add_action('acf/init', [$this, 'acfFieldsSmartFilters']);
    }

    public function registerCptSmartFilters(): void
    {
        $args = [
            'label' => __('Telson Filters', 'telson-app'),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => ['title', 'custom-fields'],
            'has_archive' => false,
            'rewrite' => [
                'slug' => self::CPT_NAME,
                'with_front' => false,
            ],
            'menu_icon' => 'dashicons-filter',
        ];

        register_post_type(self::CPT_NAME, $args);
    }

    public function acfFieldsSmartFilters(): void
    {
        $filter = new FieldsBuilder('smart_filter_fields');

        $filter
            ->addSelect('filter_type', [
                'label' => 'Filter Type',
                'choices' => [
                    'price' => 'Price Range',
                    'category' => 'Product Category',
                ],
            ])

            // Price Range Fields
            ->addNumber('step', [
                'label' => 'Step',
                'instructions' => 'Step size for the price slider (e.g., 1, 10, 0.5)',
            ])->conditional('filter_type', '==', 'price')

            ->addText('currency_prefix', [
                'label' => 'Values Prefix',
            ])->conditional('filter_type', '==', 'price')

            ->addText('min_price2', [
                'label' => 'Min Price',
            ])->conditional('filter_type', '==', 'price')

            ->addText('max_price2', [
                'label' => 'Max Price',
            ])->conditional('filter_type', '==', 'price')

            ->addText('currency_suffix', [
                'label' => 'Values Suffix',
            ])->conditional('filter_type', '==', 'price')

            ->addTrueFalse('inputs_enabled', [
                'label' => 'Inputs Enabled',
                'ui' => 1,
            ])->conditional('filter_type', '==', 'price')

            ->addNumber('decimals', [
                'label' => 'Number of Decimals',
                'default_value' => 0,
            ])->conditional('filter_type', '==', 'price')

            ->addText('decimal_separator', [
                'label' => 'Decimal Separator',
                'default_value' => '.',
            ])->conditional('filter_type', '==', 'price')

            ->addText('thousands_separator', [
                'label' => 'Thousands Separator',
                'default_value' => ',',
            ])->conditional('filter_type', '==', 'price')

            // Category Filter Fields
            ->addSelect('data_source', [
                'label' => 'Data Source',
                'choices' => [
                    'taxonomies' => 'Taxonomies',
                ],
                'default_value' => 'taxonomies',
            ])->conditional('filter_type', '==', 'category')

            ->addTaxonomy('taxonomy', [
                'label' => 'Taxonomies',
                'taxonomy' => 'product_cat', // This is a required param, but we'll allow all via JS
                'field_type' => 'multi_select',
                'add_term' => 0,
                'return_format' => 'object',
                'multiple' => true,
            ])->conditional('filter_type', '==', 'category')

            ->addSelect('terms_order_by', [
                'label' => 'Terms Order By',
                'choices' => [
                    'name' => 'Name',
                    'count' => 'Count',
                    'slug' => 'Slug',
                ],
                'default_value' => 'name',
            ])->conditional('filter_type', '==', 'category')

            ->addSelect('terms_order', [
                'label' => 'Terms Order',
                'choices' => [
                    'ASC' => 'Ascending',
                    'DESC' => 'Descending',
                ],
                'default_value' => 'ASC',
            ])->conditional('filter_type', '==', 'category')

            ->addSelect('relational_operator', [
                'label' => 'Relational Operator',
                'choices' => [
                    'union' => 'Union',
                    'intersect' => 'Intersect',
                    'exclude' => 'Exclude',
                ],
                'default_value' => 'union',
            ])->conditional('filter_type', '==', 'category');

        $filter->setLocation('post_type', '==', self::CPT_NAME);

        acf_add_local_field_group($filter->build());
    }
}
