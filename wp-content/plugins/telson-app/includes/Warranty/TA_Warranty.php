<?php

namespace App\TelsonApp\Warranty;

if (!defined('ABSPATH')) {
    exit;
}

use StoutLogic\AcfBuilder\FieldsBuilder;

class TA_Warranty
{
    private const CPT_NAME = 'warranty';
    private const DEFAULT_POST_SLUG = 'warranty-instructions';

    public function __construct()
    {
        add_action('init', [$this, 'registerCPT']);
        add_action('admin_menu', [$this, 'removeAddNewButton']);
        add_action('init', [$this, 'ensureDefaultPost']);
        add_action('acf/init', [$this, 'registerACFFields']);
        add_action('init', [$this, 'registerShortcodes']);
    }

    public function registerCPT(): void
    {
        register_post_type(self::CPT_NAME, [
            'label' => __('Telson Warranty', 'telson-app'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'capability_type' => 'post',
            'supports' => ['title', 'custom-fields'],
            'menu_icon' => 'dashicons-admin-tools',
            'has_archive' => false,
        ]);
    }

    public function removeAddNewButton(): void
    {
        global $submenu;

        if (isset($submenu['edit.php?post_type=' . self::CPT_NAME])) {
            foreach ($submenu['edit.php?post_type=' . self::CPT_NAME] as $index => $item) {
                if (in_array('post-new.php?post_type=' . self::CPT_NAME, $item)) {
                    unset($submenu['edit.php?post_type=' . self::CPT_NAME][$index]);
                }
            }
        }

        add_action('admin_head', function () {
            $screen = get_current_screen();
            if ($screen && $screen->post_type === self::CPT_NAME) {
                echo '<style>.page-title-action { display: none; }</style>';
            }
        });
    }

    public function ensureDefaultPost(): void
    {
        $existing = get_page_by_path(self::DEFAULT_POST_SLUG, OBJECT, self::CPT_NAME);
        if (!$existing) {
            wp_insert_post([
                'post_title'  => 'Warranty Instructions',
                'post_name'   => self::DEFAULT_POST_SLUG,
                'post_type'   => self::CPT_NAME,
                'post_status' => 'publish',
            ]);
        }
    }

    public function registerACFFields(): void
    {
        $fields = new FieldsBuilder('warranty_instructions_fields');

        $fields->addWysiwyg('instructions', [
            'label' => 'Instructions',
            'instructions' => 'Enter warranty instructions content using the editor.',
            'tabs' => 'all',
            'media_upload' => true,
            'delay' => 0,
        ]);

        $fields->setLocation('post_type', '==', self::CPT_NAME);

        acf_add_local_field_group($fields->build());
    }

    public function registerShortcodes(): void
    {
        add_shortcode('warranty_instructions', [$this, 'renderShortcode']);
    }

    public function renderShortcode($atts): string
    {
        $post = get_page_by_path(self::DEFAULT_POST_SLUG, OBJECT, self::CPT_NAME);
    
        if (!$post) {
            return '';
        }
    
        $content = get_field('instructions', $post->ID);
    
        if (empty($content)) {
            return '';
        }
    
        // Wrap content in a div with a custom class
        return '<div class="warranty-instructions-content">' . apply_filters('the_content', $content) . '</div>';
    }
    
}
