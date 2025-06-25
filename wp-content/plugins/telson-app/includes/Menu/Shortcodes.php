<?php



function my_dynamic_mega_menu_shortcode() {
    ob_start();
    echo '<div class="mega-menu-wrapper">';
    wp_nav_menu([
        'theme_location'  => 'mega-menu',
        'container'       => false,
        'menu_class'      => 'mega-menu',
        'walker'          => new Walker_Mega_Menu(),
        'fallback_cb'     => false,
    ]);
    echo '</div>';
    echo'<div class="hamburger-icon">';
    echo'<svg aria-hidden="true" role="img" focusable="false" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="svg-icon"><path d="M5 15H15V13H5V15ZM5 5V7H15V5H5ZM5 11H15V9H5V11Z" fill="currentColor"></path></svg>';
    echo'</div>';

    return ob_get_clean();
}

add_shortcode('mega_menu', 'my_dynamic_mega_menu_shortcode');


function my_dynamic_mobile_menu_shortcode() {
    ob_start();
    echo '<div class="the-close"><svg viewBox="0 0 352 512" xmlns="http://www.w3.org/2000/svg" class="e-font-icon-svg e-fas-times"><path d="M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"></path></svg></div>';

    echo '<div class="mobile-menu-wrapper">';
    wp_nav_menu([
        'theme_location'  => 'mega-menu',
        'container'       => false,
        'menu_class'      => 'mega-menu',
        'walker'          => new Walker_Mega_Menu(),
        'fallback_cb'     => false,
    ]);
    echo '</div>';


    return ob_get_clean();
}

add_shortcode('mobile_menu', 'my_dynamic_mobile_menu_shortcode');

add_action('wp_footer', function () {
    // Output the shortcode content in the admin footer
    echo do_shortcode('[mobile_menu]');
});