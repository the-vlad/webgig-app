<?php

namespace App\TelsonApp\ShareButtons;

if (!defined('ABSPATH')) {
    exit;
}

use StoutLogic\AcfBuilder\FieldsBuilder;






class TA_ShareButtons{
	public function __construct() {
		add_action( 'acf/init', [ $this, 'registerOptionsPage' ] );
		add_action( 'acf/init', [ $this, 'registerFields' ] );
		add_shortcode( 'acf_share_buttons', [ $this, 'renderShareButtons' ] );
	}

	public function registerOptionsPage() {
		if ( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page( [
				'page_title' => 'Share Social',
				'menu_title' => 'Telson Socials',
				'menu_slug'  => 'share-social',
				'capability' => 'manage_options',
				'position'   => 26,
				'icon_url'   => 'dashicons-share',
				'redirect'   => false,
			] );
		}
	}

	public function registerFields() {
		$share = new FieldsBuilder( 'share_social' );

		$share
			->addText( 'share_title', [
				'label' => 'Section Title',
				'default_value' => 'Share On',
			] )
			->addRepeater( 'share_buttons', [
				'label' => 'Share Buttons',
				'button_label' => 'Add Social Button',
			] )
				->addSelect( 'platform', [
					'label' => 'Platform',
					'choices' => [
						'facebook' => 'Facebook',
						'x-twitter' => 'X (Twitter)',
						'reddit' => 'Reddit',
						'telegram' => 'Telegram',
					],
					'return_format' => 'value',
				] )
			
			->endRepeater();

		$share->setLocation( 'options_page', '==', 'share-social' );

		acf_add_local_field_group( $share->build() );
	}


    // public function renderShareButtons(): string {
    //     $title = get_field( 'share_title', 'option' );
    //     $buttons = get_field( 'share_buttons', 'option' );
    
    //     echo '<pre>';
    //     var_dump($title, $buttons);
    //     echo '</pre>';
    
    //     // ...
    // }
    

    public function renderShareButtons(): string {
        ob_start();
    
        $title = get_field('share_title', 'option');
        $buttons = get_field('share_buttons', 'option');
    
        if (!$buttons) {
            return '';
        }
        ?>
        <div id="ta-share-buttons" class="elementor-element e-con-full e-flex e-con e-child">
            <?php if ($title): ?>
                <div class="elementor-element elementor-widget elementor-widget-heading">
                    <div class="elementor-widget-container">
                        <div class="elementor-heading-title elementor-size-default">
                            <?php echo esc_html($title); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="elementor-element elementor-share-buttons--view-icon elementor-share-buttons--skin-framed elementor-share-buttons--align-right elementor-share-buttons--color-custom elementor-share-buttons--shape-square elementor-grid-0 elementor-widget elementor-widget-share-buttons">
                <div class="elementor-widget-container">
                    <div class="elementor-grid">
                        <?php foreach ($buttons as $btn):
                            $platform = $btn['platform'];
                            global $post;
                            $permalink = urlencode(get_permalink($post->ID));
                            $title     = urlencode(get_the_title($post->ID));
                            
                            switch ($platform) {
                                case 'facebook':
                                    $url = "https://www.facebook.com/sharer/sharer.php?u={$permalink}";
                                    break;
                                case 'x-twitter':
                                    $url = "https://twitter.com/intent/tweet?text={$title}&url={$permalink}";
                                    break;
                                case 'reddit':
                                    $url = "https://www.reddit.com/submit?url={$permalink}&title={$title}";
                                    break;
                                case 'telegram':
                                    $url = "https://t.me/share/url?url={$permalink}&text={$title}";
                                    break;
                                default:
                                    $url = $permalink;
                                    break;
                            }
                                
                            // Inline SVG icons
                            $svg_icons = [
                                'x-twitter' => '<svg class="e-font-icon-svg e-fab-x-twitter" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg>',
                                'reddit'    => '<svg class="e-font-icon-svg e-fab-reddit" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M201.5 305.5c-13.8 0-24.9-11.1-24.9-24.6 0-13.8 11.1-24.9 24.9-24.9 13.6 0 24.6 11.1 24.6 24.9 0 13.6-11.1 24.6-24.6 24.6zM504 256c0 137-111 248-248 248S8 393 8 256 119 8 256 8s248 111 248 248zm-132.3-41.2c-9.4 0-17.7 3.9-23.8 10-22.4-15.5-52.6-25.5-86.1-26.6l17.4-78.3 55.4 12.5c0 13.6 11.1 24.6 24.6 24.6 13.8 0 24.9-11.3 24.9-24.9s-11.1-24.9-24.9-24.9c-9.7 0-18 5.8-22.1 13.8l-61.2-13.6c-3-.8-6.1 1.4-6.9 4.4l-19.1 86.4c-33.2 1.4-63.1 11.3-85.5 26.8-6.1-6.4-14.7-10.2-24.1-10.2-34.9 0-46.3 46.9-14.4 62.8-1.1 5-1.7 10.2-1.7 15.5 0 52.6 59.2 95.2 132 95.2 73.1 0 132.3-42.6 132.3-95.2 0-5.3-.6-10.8-1.9-15.8 31.3-16 19.8-62.5-14.9-62.5zM302.8 331c-18.2 18.2-76.1 17.9-93.6 0-2.2-2.2-6.1-2.2-8.3 0-2.5 2.5-2.5 6.4 0 8.6 22.8 22.8 87.3 22.8 110.2 0 2.5-2.2 2.5-6.1 0-8.6-2.2-2.2-6.1-2.2-8.3 0zm7.7-75c-13.6 0-24.6 11.1-24.6 24.9 0 13.6 11.1 24.6 24.6 24.6 13.8 0 24.9-11.1 24.9-24.6 0-13.8-11-24.9-24.9-24.9z"></path></svg>',
                                'facebook'  => '<svg class="e-font-icon-svg e-fab-facebook" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"></path></svg>',
                                'telegram'  => '<svg class="e-font-icon-svg e-fab-telegram" viewBox="0 0 496 512" xmlns="http://www.w3.org/2000/svg"><path d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm121.8 169.9l-40.7 191.8c-3 13.6-11.1 16.9-22.4 10.5l-62-45.7-29.9 28.8c-3.3 3.3-6.1 6.1-12.5 6.1l4.4-63.1 114.9-103.8c5-4.4-1.1-6.9-7.7-2.5l-142 89.4-61.2-19.1c-13.3-4.2-13.6-13.3 2.8-19.7l239.1-92.2c11.1-4 20.8 2.7 17.2 19.5z"></path></svg>',
                            ];
    
                            $svg = $svg_icons[$platform] ?? '';
                        ?>
                            <div class="elementor-grid-item">
                                <a class="elementor-share-btn elementor-share-btn_<?php echo esc_attr($platform); ?>"
                                   href="<?php echo $url; ?>"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   role="button"
                                   aria-label="Share on <?php echo esc_attr($platform); ?>">
                                    <span class="elementor-share-btn__icon">
                                        <?php echo $svg; ?>
                                    </span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    
        return ob_get_clean();
    }
    
}
