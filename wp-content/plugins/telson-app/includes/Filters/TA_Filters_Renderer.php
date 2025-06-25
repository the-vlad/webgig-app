<?php
namespace App\TelsonApp\Filters;

use WP_Query;

class TA_Filters_Renderer
{
    public function __construct()
    {
        add_action('woocommerce_before_shop_loop', [$this, 'renderFilters'], 5);
    }



    public function renderFilters(): void
    {
        static $has_rendered = false;
    
        if ($has_rendered) return;
        $has_rendered = true;
    
        $filters = new \WP_Query([
            'post_type' => 'custom_filters',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ]);
    
        if (!$filters->have_posts()) return;
    
        echo '<div class="telson-filters-wrapper">';
            echo '<div id="close-filters">Close X</div>';
        while ($filters->have_posts()) {
            $filters->the_post();
    
            $filter_type = get_field('filter_type');
    
            switch ($filter_type) {
                case 'price':
                    $this->renderPriceFilter();
                    break;
                case 'category':
                    $this->renderCategoryFilter();
                    break;
        
            }
        }
        $this->renderSelectFilters();

        echo '</div>';
        echo     '<button id="toggle-price-filter" type="button" class="button">Filter</button>';
        wp_reset_postdata();
    }
    

    private function renderPriceFilter(): void
    {
        $step = get_field('step') ?: 1;
        $prefix = get_field('currency_prefix') ?: '$';
        $suffix = get_field('currency_suffix') ?: '';
        $decimals = get_field('decimals') ?: 0;
        $inputs_enabled = get_field('inputs_enabled');
    
        $min = (int) get_field('min_price2');
     
        $max = (int) get_field('max_price2');    

        $selected_min =  isset($_GET['min_price']) ? (int)$_GET['min_price'] : $min;
        $selected_max = isset($_GET['max_price']) ? (int)$_GET['max_price'] : $max;
        ?>
    
 <div class="telson-price-filter">
<div style="background:green;display:flex;height:fit-content;" class="products-new"></div>

    <form id="telson-price-form" method="get" style="display: none;">
    <h4>Product Filter..</h4>

        <div id="slider"
             data-step="<?= esc_attr($step); ?>"
             data-min="<?= esc_attr($min); ?>"
             data-max="<?= esc_attr($max); ?>"
             data-decimals="<?= esc_attr($decimals); ?>"
             data-prefix="<?= esc_attr($prefix); ?>"
             data-suffix="<?= esc_attr($suffix); ?>"
             data-selected-min="<?= esc_attr($selected_min); ?>"
             data-selected-max="<?= esc_attr($selected_max); ?>">
        </div>

        <div class="telson-price-values">
            <span id="ta-price-min"><?= $prefix . $selected_min . $suffix ?></span>
            —
            <span id="ta-price-max"><?= $prefix . $selected_max . $suffix ?></span>
        </div>

        <!-- Hidden inputs to submit actual values -->
        <input type="hidden" name="min_price" id="ta-hidden-min" value="<?= esc_attr($selected_min); ?>">
        <input type="hidden" name="max_price" id="ta-hidden-max" value="<?= esc_attr($selected_max); ?>">
    
    
            <!-- For Categories -->
            <!-- <input type="checkbox" name="filter_product_cat[]" value="123"> 
            <input type="checkbox" name="filter_product_cat[]" value="124"> -->
    </form>



</div>

        <?php
    }
    
    private function getDynamicAttributeFields(): array {
        $attribute_posts = get_posts([
            'post_type' => 'product_attribute',
            'numberposts' => -1,
        ]);
    
        $fields = [];
    
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
    
            $fields[$field_name] = [
                'label' => $field_label,
                'choices' => $choices,
                'allow_null' => true,
                'multiple' => true, // or false, depending on your design
            ];
        }
    
        return $fields;
    }
    
    
    
private function renderSelectFilters(): void
{
    $fields = $this->getDynamicAttributeFields(); // Use the dynamic ones

    echo '<div class="cast-filters">';

    foreach ($fields as $acf_key => $field) {
        $is_multiple = !empty($field['multiple']);
        $select_name = 'acf_filter_' . $acf_key . ($is_multiple ? '[]' : '');
        $multiple_attr = $is_multiple ? ' multiple' : '';

        $selected_values = isset($_GET['acf_filter_' . $acf_key])
            ? (array) $_GET['acf_filter_' . $acf_key]
            : [];

        echo '<div class="filter-select-wrapper">';
        echo '<select name="' . esc_attr($select_name) . '" id="' . esc_attr($acf_key) . '" class="telson-select-filter" data-placeholder="' . esc_attr($field['label']) . '"' . $multiple_attr . '>';

        // if (!empty($field['allow_null'])) {
        //     echo '<option value="">' . esc_html($field['label']) . '</option>';
        // }

        foreach ($field['choices'] as $choice_value => $choice_label) {
            $selected = in_array($choice_value, $selected_values) ? ' selected' : '';
            echo '<option value="' . esc_attr($choice_value) . '"' . $selected . '>' . esc_html($choice_label) . '</option>';
        }

        echo '</select>';
        echo '</div>';
    }

    echo '</div>';
}

        
    
        // return ob_get_clean();
    



    private function renderCategoryFilter(): void
    {
        $term_ids = get_field('taxonomy'); // These are term IDs selected in ACF
        if (empty($term_ids) || !is_array($term_ids)) {
            return;
        }
    
        // Group selected terms by taxonomy
        $grouped_terms = [];
    
        foreach ($term_ids as $term_id) {
            $term = get_term($term_id);
            if (!$term || is_wp_error($term)) {
                continue;
            }
    
            $taxonomy = $term->taxonomy;
    
            if (!isset($grouped_terms[$taxonomy])) {
                $grouped_terms[$taxonomy] = [];
            }
    
            $grouped_terms[$taxonomy][] = $term;
        }
    
        // Render each taxonomy group
        foreach ($grouped_terms as $taxonomy => $terms) {
            $taxonomy_label = get_taxonomy($taxonomy)->labels->name ?? $taxonomy;
         
            echo '<div class="custom-category-dropdown"><span class="cat-arr">&#9660;</span>';
            echo '<div class="custom-category-label">Category</div>';
            echo '<div class="custom-category-body" style="display:none;">';
            echo '<fieldset class="custom-category-list-wrapper">';
    
            foreach ($terms as $term) {
                echo '<label class="custom-category-item">';
                echo '<input type="checkbox" 
                             name="filter_' . esc_attr($taxonomy) . '[]" 
                             value="' . esc_attr($term->term_id) . '"
                             data-parent="' . esc_attr($term->parent) . '"
                             data-term-id="' . esc_attr($term->term_id) . '"
                             data-label="' . esc_attr($term->name) . '">';
                echo '<span class="custom-category-label-text">' . esc_html($term->name) . '</span>';
                echo '</label>';
            }
            
    
            echo '</fieldset>';
            echo '</div>';
            echo '</div>';
            
        }

       
    }





    
}
