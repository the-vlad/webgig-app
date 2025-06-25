<?php

namespace App\TelsonApp\Testimonials;

if (!defined('ABSPATH')) {
    exit;
}

use StoutLogic\AcfBuilder\FieldsBuilder;

class TA_Testimonials
{
    public function __construct()
    {
        add_action('acf/init', [$this, 'registerOptionsPage']);
        add_action('acf/init', [$this, 'registerTestimonialsFields']);
        add_action('init', [$this, 'registerShortcode']);
    }

    public function registerOptionsPage(): void
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title' => 'Testimonials',
                'menu_title' => 'Telson Testimonials',
                'menu_slug'  => 'testimonials-settings',
                'capability' => 'edit_posts',
                'position'   => 26,
                'redirect'   => false,
                
            ]);
        }
    }

    public function registerTestimonialsFields(): void
    {
        $fields = new FieldsBuilder('frontpage_testimonials');

        $fields
            ->addRepeater('testimonials', [
                'label' => 'Front Page Testimonials',
                'button_label' => 'Add Testimonial',
            ])
                ->addTextarea('content', [
                    'label' => 'Testimonial Content',
                    'rows' => 4,
                ])
                ->addText('name', [
                    'label' => 'Name',
                ])
                ->addText('position', [
                    'label' => 'Position',
                ])
            ->endRepeater();

        $fields->setLocation('options_page', '==', 'testimonials-settings');

        acf_add_local_field_group($fields->build());
    }

    public function registerShortcode(): void
    {
        add_shortcode('frontpage_testimonials', [$this, 'renderTestimonials']);
    }

    public function renderTestimonials(): string
{
    $testimonials = get_field('testimonials', 'option');

    if (!$testimonials) {
        return '';
    }

    ob_start();
    ?>
    <div class="swiper frontpage-testimonials-swiper">
        <div class="swiper-wrapper">
            <?php foreach ($testimonials as $testimonial) : ?>
                <div class="swiper-slide">
                    <div class="testimonial">
                        <blockquote class="testimonial-content"><?php echo esc_html($testimonial['content']); ?></blockquote>
                        <p class="testimonial-name"><strong><?php echo esc_html($testimonial['name']); ?></strong></p>
                        <p class="testimonial-position"><?php echo esc_html($testimonial['position']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-pagination"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        new Swiper('.frontpage-testimonials-swiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                }
            }
        });
    });
</script>

    <?php
    return ob_get_clean();
}

}
