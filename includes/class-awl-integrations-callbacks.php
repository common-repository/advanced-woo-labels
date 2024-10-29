<?php
/**
 * AWL plugin callbacks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AWL_Integrations_Callbacks' ) ) :

    /**
     * Class for plugin callbacks
     */
    class AWL_Integrations_Callbacks {
        
        public static function post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size ) {
            if ( $size === 'shop_catalog' ) {
                return $html . AWL_Label_Display::instance()->show_label( 'on_image' );
            }
            return $html;
        }

        public static function betheme_woocommerce_placeholder_img( $image_html, $size, $dimensions ) {
            if ( $size === 'shop_catalog' ) {
                return $image_html . AWL_Label_Display::instance()->show_label( 'on_image' );
            }
            return $image_html;
        }

        public static function woocommerce_product_get_image( $image, $obj, $size ) {
            global $wp_current_filter;
            if ( in_array( 'woocommerce_before_shop_loop_item_title', $wp_current_filter ) ) {
                if ( strpos( $image, '<div' ) === false && strpos( $image, '<span' ) === false ) {
                    return '<div style="position:relative;">' . $image . AWL_Label_Display::instance()->show_label( 'on_image' ) . '</div>';
                }
            }
            return $image;
        }

        public static function before_loop_title( $title ) {
            global $wp_current_filter;
            if ( in_array( 'woocommerce_before_shop_loop_item_title', $wp_current_filter ) || in_array( 'woocommerce_shop_loop_item_title', $wp_current_filter ) || in_array( 'woocommerce_before_shop_loop_item', $wp_current_filter ) ) {
                return AWL_Label_Display::instance()->show_label( 'before_title' ) . $title;
            }
            return $title;
        }

    }

endif;