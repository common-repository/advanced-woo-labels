<?php
/**
 * AWL plugin integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AWL_Integrations' ) ) :

    /**
     * Class for main plugin functions
     */
    class AWL_Integrations {
        
        /**
         * @var AWL_Integrations The single instance of the class
         */
        protected static $_instance = null;

        /**
         * @var AWL_Integrations Current theme name
         */
        public $current_theme = '';

        /**
         * @var AWL_Integrations Init theme name
         */
        public $child_theme = '';

        /**
         * Main AWL_Integrations Instance
         *
         * @static
         * @return AWL_Integrations - Main instance
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Constructor
         */
        public function __construct() {

            $theme = function_exists( 'wp_get_theme' ) ? wp_get_theme() : false;

            if ( $theme ) {
                $this->current_theme = $theme->name;
                $this->child_theme = $theme->name;
                if ( $theme->parent() ) {
                    $this->current_theme = $theme->parent();
                }
            }

            add_filter( 'awl_labels_hooks', array( $this, 'awl_labels_hooks' ), 1 );

            add_filter( 'awl_label_container_styles', array( $this, 'awl_label_container_styles' ), 10, 3 );

            add_action( 'wp_head', array( $this, 'wp_head_styles' ) );

        }

        /*
         * Change display hooks
         */
        public function awl_labels_hooks( $hooks ) {

            $hooks = array(
                'on_image' => array(
                    'archive' => array(
                        'woocommerce_before_shop_loop_item_title' => array( 'priority' => 10 ),
                        'woocommerce_product_get_image' => array( 'priority' => 10, 'type' => 'filter', 'callback' => 'AWL_Integrations_Callbacks::woocommerce_product_get_image', 'args' => 3 )
                    ),
                    'single' => array(
                        'woocommerce_product_thumbnails' => array( 'priority' => 10 )
                    ),
                ),
                'before_title' => array(
                    'archive' => array(
                        //'woocommerce_before_shop_loop_item_title' => array( 'priority' => 20 ),
                        'woocommerce_shop_loop_item_title' => array( 'priority' => 9 ),
                        //'woocommerce_after_shop_loop_item_title' => array( 'priority' => 1 ),
                    ),
                    'single' => array(
                        //'woocommerce_before_single_product_summary' => array( 'priority' => 25 ),
                        'woocommerce_single_product_summary' => array( 'priority' => 4 )
                    ),
                ),
            );

            if ( is_singular( 'product' ) ) {
                if ( get_post_meta( get_queried_object_id(), '_product_image_gallery', true ) ) {
                    $hooks['on_image']['single'] = array( 'woocommerce_product_thumbnails' => array( 'priority' => 10, 'js' =>  array( '.woocommerce-product-gallery .flex-viewport, .woocommerce-product-gallery__wrapper', 'append' ) ) );
                }
            }

            switch ( $this->current_theme ) {

                case 'Aurum':
                    $hooks['on_image']['archive'] = array( 'get_template_part_tpls/woocommerce-item-thumbnail' => array( 'priority' => 10 ) );
                    $hooks['before_title']['archive'] = array( 'aurum_before_shop_loop_item_title' => array( 'priority' => 10 ) );
                    $hooks['on_image']['single'] = array( 'woocommerce_before_single_product_summary' => array( 'priority' => 25, 'js' =>  array( '.product-images-container .product-images--main', 'append' ) ) );
                    break;

                case 'Betheme':
                    $hooks['on_image']['archive'] = array(
                        'post_thumbnail_html' => array( 'priority' => 10, 'type' => 'filter', 'callback' => 'AWL_Integrations_Callbacks::post_thumbnail_html', 'args' => 4 ),
                        'woocommerce_placeholder_img' => array( 'priority' => 10, 'type' => 'filter', 'callback' => 'AWL_Integrations_Callbacks::betheme_woocommerce_placeholder_img', 'args' => 3 )
                    );
                    $hooks['before_title']['archive'] = array( 'woocommerce_after_shop_loop_item_title' => array( 'priority' => 10 ) );
                    break;

                case 'Flatsome':
                    $hooks['on_image']['single'] = array( 'woocommerce_before_single_product' => array( 'priority' => 10, 'js' => array( '.product-gallery-slider', 'append' ) ) );
                    $hooks['on_image']['archive'] = array( 'flatsome_woocommerce_shop_loop_images' => array( 'priority' => 10 ) );
                    break;

                case 'Porto':
                    $hooks['on_image']['single'] = array( 'woocommerce_single_product_image_html' => array( 'priority' => 10, 'type' => 'filter'  ) );
                    break;

                case 'Devita':
                    $hooks['on_image']['archive'] = array( 'woocommerce_before_shop_loop_item' => array( 'priority' => 10 ) );
                    break;

                case 'Electro':
                    $hooks['on_image']['archive'] = array( 'electro_template_loop_product_thumbnail' => array( 'priority' => 10, 'type' => 'filter' ) );
                    break;

                case 'firezy':
                    $hooks['before_title']['archive'] = array( 'woocommerce_after_shop_loop_item_title' => array( 'priority' => 10 ) );
                    break;

                case 'GreenMart':
                    $hooks['before_title']['archive'] = array( 'woocommerce_before_shop_loop_item_title' => array( 'priority' => 20 ) );
                    break;

                case 'HandMade':
                    $hooks['before_title']['archive'] = array( 'woocommerce_after_shop_loop_item_title' => array( 'priority' => 1 ) );
                    $hooks['on_image']['single'] = array( 'woocommerce_single_product_image_html' => array( 'priority' => 10, 'type' => 'filter' ) );
                    break;

                case 'Jupiter':
                    $hooks['on_image']['archive'] = array( 'woocommerce_after_shop_loop_item' => array( 'priority' => 10 ) );
                    $hooks['before_title']['archive'] = array( 'woocommerce_before_shop_loop_item' => array( 'priority' => 10 ) );
                    break;

                case 'MetroStore':
                    $hooks['on_image']['archive'] = array( 'post_thumbnail_html' => array( 'priority' => 10, 'type' => 'filter', 'callback' => 'AWL_Integrations_Callbacks::post_thumbnail_html', 'args' => 4 ) );
                    break;

                case 'Kallyas':
                    $hooks['on_image']['archive'] = array( 'woocommerce_before_shop_loop_item' => array( 'priority' => 10, 'js' => array( '.kw-prodimage', 'append' ) ) );
                    break;

                case 'OceanWP';
                    $hooks['on_image']['archive'] = array( 'ocean_before_archive_product_image' => array( 'priority' => 10 ) );
                    $hooks['before_title']['archive'] = array( 'ocean_before_archive_product_categories' => array( 'priority' => 1 ), 'ocean_before_archive_product_title' => array( 'priority' => 1 ) );
                    break;

                case 'Shopkeeper';
                    $hooks['on_image']['archive'] = array( 'woocommerce_shop_loop_item_thumbnail' => array( 'priority' => 1 ) );
                    $hooks['before_title']['archive'] = array( 'woocommerce_shop_loop_item_thumbnail' => array( 'priority' => 10 ) );
                    $hooks['before_title']['single'] = array( 'woocommerce_single_product_summary_single_title' => array( 'priority' => 1 ) );
                    break;

                case 'Orchid Store':
                    $hooks['on_image']['archive'] = array( 'orchid_store_product_thumbnail' => array( 'priority' => 1 ) );
                    $hooks['before_title']['archive'] = array( 'orchid_store_shop_loop_item_title' => array( 'priority' => 5 ) );
                    break;

            }

            // Divi builder
            if ( defined( 'ET_BUILDER_PLUGIN_DIR' ) &&
                ( ( is_shop() && $GLOBALS && isset( $GLOBALS['et_builder_used_in_wc_shop'] ) && $GLOBALS['et_builder_used_in_wc_shop'] ) ||
                  ( get_post_meta( get_queried_object_id(), '_et_pb_use_builder', true ) === 'on' )
                )
            ) {
                $hooks['on_image']['archive'] = array(
                    'woocommerce_product_get_image' => array( 'priority' => 10, 'type' => 'filter', 'callback' => 'AWL_Integrations_Callbacks::woocommerce_product_get_image', 'args' => 3 ),
                    'woocommerce_before_shop_loop_item' => array( 'priority' => 10, 'js' => array( '.et_shop_image', 'append' ) )
                );
            }

            return $hooks;

        }

        /*
         * Change labels container styles
         */
        public function awl_label_container_styles( $styles, $position_type, $labels ) {

            global $wp_current_filter;
            $current_filter = array_slice( $wp_current_filter, -2, 1 );
            $current_filter = isset( $current_filter[0] ) ? $current_filter[0] : false;

            if ( $current_filter ) {
                $hooks = AWL_Helpers::get_hooks();
                if ( is_array( $hooks ) && ! empty( $hooks ) ) {
                    foreach( $hooks as $position => $hooks_list_type ) {
                        foreach ( $hooks_list_type as $hooks_display => $hooks_list ) {
                            foreach ( $hooks_list as $hook_name => $hook_vars ) {
                                if ( $hook_name === $current_filter && isset( $hook_vars['js'] ) ) {
                                    $styles['display'] = 'none';
                                    break 3;
                                }
                            }
                        }
                    }
                }

            }

            if ( 'Flatsome' === $this->current_theme && $position_type === 'on_image' && in_array( 'flatsome_woocommerce_shop_loop_images', $wp_current_filter ) ) {
                $styles['z-index'] = '0';
            }

            if ( 'Avada' === $this->current_theme ) {
                $styles['z-index'] = '99';
            }

            return $styles;

        }

        /*
         * Add custom styles
         */
        public function wp_head_styles() {

            $output = '';

            echo $output;

        }

    }

endif;