<?php
/**
 * Versions capability
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AWL_Versions' ) ) :

    /**
     * Class for plugin search
     */
    class AWL_Versions {

        /**
         * Return a singleton instance of the current class
         *
         * @return object
         */
        public static function factory() {
            static $instance = false;

            if ( ! $instance ) {
                $instance = new self();
                $instance->setup();
            }

            return $instance;
        }

        /**
         * Placeholder
         */
        public function __construct() {}

        /**
         * Setup actions and filters for all things settings
         */
        public function setup() {

            $current_version = get_option( 'awl_plugin_ver' );
            
            if ( $current_version ) {

                // Do some versions change
                
            }
            
            update_option( 'awl_plugin_ver', AWL_VERSION );

        }
        

    }


endif;

add_action( 'admin_init', 'AWL_Versions::factory' );