<?php

/*
Plugin Name: Advanced Woo Labels
Description: Advance WooCommerce product labels plugin
Version: 1.00
Author: ILLID
Author URI: https://advanced-woo-labels.com/
Text Domain: advanced-woo-labels
WC requires at least: 3.0.0
WC tested up to: 4.3.0
*/


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AWL_VERSION', '1.00' );


define( 'AWL_DIR', dirname( __FILE__ ) );
define( 'AWL_URL', plugins_url( '', __FILE__ ) );
define( 'AWL_IMG', AWL_URL . '/assets/img/' );

if ( ! class_exists( 'AWL_Main' ) ) :

/**
 * Main plugin class
 *
 * @class AWL_Main
 */
final class AWL_Main {

    /**
     * @var AWL_Main The single instance of the class
     */
    protected static $_instance = null;

    /**
     * @var AWL_Main Array of all plugin data $data
     */
    private $data = array();

    /**
     * Main AWL_Main Instance
     *
     * Ensures only one instance of AWL_Main is loaded or can be loaded.
     *
     * @static
     * @return AWL_Main - Main instance
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

        // Check for pro version
        if ( defined( 'AWL_PRO_VERSION' ) ) {
            return;
        }

        $this->data['settings'] = get_option( 'awl_settings' );

        add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

        add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 10, 2 );

        load_plugin_textdomain( 'advanced-woo-labels', false, dirname( plugin_basename( __FILE__ ) ). '/languages/' );

        $this->includes();

        add_action( 'init', array( $this, 'init' ), 0 );

    }

    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes() {

        include_once( 'includes/class-awl-versions.php' );
        include_once( 'includes/class-awl-taxonomy.php' );
        include_once( 'includes/class-awl-hooks.php' );
        include_once( 'includes/class-awl-integrations.php' );
        include_once( 'includes/class-awl-integrations-callbacks.php' );
        include_once( 'includes/class-awl-conditions.php' );
        include_once( 'includes/class-awl-label-view.php' );
        include_once( 'includes/class-awl-label-text.php' );
        include_once( 'includes/class-awl-helpers.php' );
        include_once( 'includes/class-awl-product-data.php' );
        include_once( 'includes/class-awl-label-display.php' );

        // Admin
        if ( is_admin() ) {
            include_once( 'includes/admin/class-awl-admin-duplicate-labels.php' );
            include_once( 'includes/admin/class-awl-admin-ajax.php' );
            include_once( 'includes/admin/class-awl-admin-options.php' );
            include_once( 'includes/admin/class-awl-admin-helpers.php' );
            include_once( 'includes/admin/class-awl-admin-meta-boxes.php' );
            include_once( 'includes/admin/class-awl-admin-page.php' );
            include_once( 'includes/admin/class-awl-admin-page-fields.php' );
            include_once( 'includes/admin/class-awl-admin-label-rules.php' );
            include_once( 'includes/admin/class-awl-admin-label-settings.php' );
            include_once( 'includes/admin/class-awl-admin-hooks-table.php' );
            include_once( 'includes/admin/class-awl-admin.php' );
        }

    }

    /*
     * Add settings link to plugins
     */
    public function add_settings_link( $links, $file ) {
        $plugin_base = plugin_basename( __FILE__ );

        if ( $file == $plugin_base ) {
            $setting_link = '<a href="' . admin_url( 'edit.php?post_type=awl-labels&page=awl-options' ) . '">' . esc_html__( 'Settings', 'advanced-woo-labels' ) . '</a>';
            array_unshift( $links, $setting_link );
        }

        return $links;
    }

    /*
     * Init plugin classes
     */
    public function init() {

        AWL_Taxonomy::instance();
        AWL_Hooks::instance();
        AWL_Integrations::instance();
        AWL_Label_Display::instance();

        if ( is_admin() ) {
            AWL_Admin_Duplicate_Labels::instance();
        }

    }

    /*
	 * Load assets for search form
	 */
    public function load_scripts() {
    }

    /*
    * Get plugin settings
    */
    public function get_settings( $name = false ) {
        $plugin_options = $this->data['settings'];
        $return_value = ! $name ? $plugin_options : ( isset( $plugin_options[ $name ] ) ? $plugin_options[ $name ] : false );
        return $return_value;
    }

}

endif;


/**
 * Returns the main instance of AWL_Main
 *
 * @return AWL_Main
 */
function AWL() {
    return AWL_Main::instance();
}


/*
 * Check if pro version of plugin is active
 */
register_activation_hook( __FILE__, 'awl_activation_check' );
function awl_activation_check() {
    if ( awl_is_plugin_active( 'advanced-woo-labels-pro/advanced-woo-labels-pro.php' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'Advanced Woo Labels plugin can\'t be activated because you already activate PRO plugin version.', 'advanced-woo-labels' ) );
    }
}


/*
 * Check if WooCommerce is active
 */
if ( awl_is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
    awl_init();
} else {
    add_action( 'admin_notices', 'awl_install_woocommerce_admin_notice' );
}


/*
 * Check whether the plugin is active by checking the active_plugins list.
 */
function awl_is_plugin_active( $plugin ) {
    return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || awl_is_plugin_active_for_network( $plugin );
}


/*
 * Check whether the plugin is active for the entire network
 */
function awl_is_plugin_active_for_network( $plugin ) {
    if ( !is_multisite() )
        return false;

    $plugins = get_site_option( 'active_sitewide_plugins' );
    if ( isset($plugins[$plugin]) )
        return true;

    return false;
}


/*
 * Error notice if WooCommerce plugin is not active
 */
function awl_install_woocommerce_admin_notice() {
    ?>
    <div class="error">
        <p><?php esc_html_e( 'Advanced Woo Labels plugin is enabled but not effective. It requires WooCommerce in order to work.', 'advanced-woo-labels' ); ?></p>
    </div>
    <?php
}


/*
 * Init AWL plugin
 */
function awl_init() {
    AWL();
}