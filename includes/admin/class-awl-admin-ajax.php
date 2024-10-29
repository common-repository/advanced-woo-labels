<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWL_Admin_Ajax' ) ) :

    /**
     * Class for plugin admin ajax hooks
     */
    class AWL_Admin_Ajax {

        /*
         * Constructor
         */
        public function __construct() {

            add_action( 'wp_ajax_awl-getRuleGroup', array( $this, 'get_rule_group' ) );

            add_action( 'wp_ajax_awl-getSuboptionValues', array( $this, 'get_suboption_values' ) );

        }

        /*
         * Ajax hook for rule groups
         */
        public function get_rule_group() {

            check_ajax_referer( 'awl_admin_ajax_nonce' );

            $name = sanitize_text_field( $_POST['name'] );
            $group_id = sanitize_text_field( $_POST['groupID'] );
            $rule_id = sanitize_text_field( $_POST['ruleID'] );

            $rules = AWL_Admin_Options::include_rules();
            $html = array();

            foreach ( $rules as $rule_section => $section_rules ) {
                foreach ( $section_rules as $rule ) {
                    if ( $rule['id'] === $name ) {

                        $rule_obj = new AWL_Admin_Label_Rules( $rule, $group_id, $rule_id );

                        $html['aoperators'] = $rule_obj->get_field( 'operator' );

                        if ( isset( $rule['suboption'] ) ) {
                            $html['asuboptions'] = $rule_obj->get_field( 'suboption' );
                        }

                        $html['avalues'] = $rule_obj->get_field( 'value' );

                        break;

                    }
                }
            }

            echo json_encode( $html );

            die;

        }

        /*
         * Ajax hook for suboption values
         */
        public function get_suboption_values() {

            check_ajax_referer( 'awl_admin_ajax_nonce' );

            $param = sanitize_text_field( $_POST['param'] );
            $suboption = sanitize_text_field( $_POST['suboption'] );
            $group_id = sanitize_text_field( $_POST['groupID'] );
            $rule_id = sanitize_text_field( $_POST['ruleID'] );

            $rules = AWL_Admin_Options::include_rules();
            $html = array();

            foreach ( $rules as $rule_section => $section_rules ) {
                foreach ( $section_rules as $rule ) {
                    if ( $rule['id'] === $param ) {

                        $rule['choices']['params'] = array( $suboption );

                        $rule_obj = new AWL_Admin_Label_Rules( $rule, $group_id, $rule_id );

                        $html = $rule_obj->get_field( 'value' );

                        break;

                    }
                }
            }

            echo json_encode( $html );

            die;

        }

    }

endif;


new AWL_Admin_Ajax();