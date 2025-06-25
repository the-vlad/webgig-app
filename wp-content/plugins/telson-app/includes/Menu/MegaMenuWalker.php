<?php

class Walker_Mega_Menu extends Walker_Nav_Menu {
    private $in_parent_row = false;
    private $submenu_class_names = '';
    private $h6_item_count = 0; // Counter for h6 items

    function start_lvl( &$output, $depth = 0, $args = null ) {
        $indent = str_repeat("\t", $depth);

        if ( $depth === 0 ) {
            $output .= "\n$indent<ul class=\"submenu {$this->submenu_class_names}\">\n";
            $this->submenu_class_names = '';
        } else {
            $output .= "\n$indent<ul class=\"subsubmenu\">\n";
        }
    }

    function end_lvl( &$output, $depth = 0, $args = null ) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";

        if ( $depth === 1 && $this->in_parent_row ) {
            $output .= "</div>\n";
            $this->in_parent_row = false;
        }
    }

    function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $classes = empty( $item->classes ) ? [] : (array) $item->classes;
        $has_children = in_array( 'menu-item-has-children', $classes );

        if ( $depth === 0 && $has_children ) {
            $custom_classes = array_filter($classes, function($class) {
                return !preg_match('/^menu-item[-_]/', $class);
            });
            $this->submenu_class_names = esc_attr(implode(' ', $custom_classes));
        }

        $filtered_li_classes = array_filter($classes, function($class) {
            return preg_match('/^menu-item[-_]/', $class);
        });

        // Add the-item{n} class only for h6-level items (depth 0)
        $additional_class = '';
        if ( $depth === 0 ) {
            $this->h6_item_count++;
            $additional_class = ' the-item' . $this->h6_item_count;
        }

        $class_names = join( ' ', array_filter( $filtered_li_classes ) );
        $class_attribute = $class_names ? ' class="main-menu-item ' . esc_attr( $class_names ) . $additional_class . '"' : ' class="main-menu-item' . $additional_class . '"';

        if ( $depth === 1 && $has_children ) {
            $output .= '<div class="parent-row">' . "\n";
            $this->in_parent_row = true;
        }

        $output .= '<li' . $class_attribute . '>';

        $dropdown_icon = '<svg class="e-font-icon-svg e-fas-angle-down" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg"><path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z"></path></svg>';

        if ( $depth === 0 ) {
            $output .= '<a href="' . esc_url( $item->url ) . '"><h6>' . esc_html( $item->title );
            if ( $has_children ) {
                $output .= ' ' . $dropdown_icon;
            }
            $output .= '</h6></a>';
        } else {
            $output .= '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a>';
        }

        $output .= '</li>' . "\n";
    }

    function end_el( &$output, $item, $depth = 0, $args = null ) {
        // Nothing to do
    }
}
