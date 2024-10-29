<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! class_exists( 'AWL_Admin_Meta_Boxes' ) ) :

    /**
     * Class for plugin admin panel
     */
    class AWL_Admin_Meta_Boxes {

        /*
         * Get content for label status meta box
         * @return string
         */
        static public function get_status_meta_box( $label ) {

            $label_value = '1';
            if ( $label && ! empty( $label ) && isset( $label['awl_label_status'] ) ) {
                $label_value = $label['awl_label_status']['status'];
            }

            $html = '';

            $html .= '<div class="awl-label-status-box">';
                $html .= '<label class="awl-toggle-label">';
                    $html .= '<input id="awl-label-status" type="checkbox" name="awl_label_params[awl_label_status][status]" value="1" ' . checked( $label_value, '1', false ) . '>';
                    $html .= '<span class="awl-toggle">';
                        $html .= '<span class="awl-toggle--active">' . __( "Active", "advanced-woo-labels" ) . '</span>';
                        $html .= '<span class="awl-toggle--inactive">' . __( "Inactive", "advanced-woo-labels" ) . '</span>';
                    $html .= '</span>';
                $html .= '</label>';
            $html .= '</div>';

            return $html;

        }

        /*
         * Get content for label priority meta box
         * @return string
         */
        static public function get_priority_meta_box( $label ) {

            $label_value = '0';
            $label_priority = get_post_meta( $label, '_awl_label_priority', true );
            if ( $label_priority ) {
                $label_value = $label_priority;
            }

            $html = '';

            $html .= '<div class="awl-label-priority-box">';
                $html .= '<input id="awl-label-priority" type="number" name="awl_label_params[awl_label_priority][priority]" value="' . esc_attr( $label_value ) . '" style="max-width: 70px;">';
            $html .= '</div>';

            return $html;

        }

        /*
         * Get content for label rules meta box
         * @return string
         */
        static public function get_rules_meta_box( $label ) {

            $rules = AWL_Admin_Options::include_rules();
            $default_rule = new AWL_Admin_Label_Rules( $rules['attributes'][0] );
            $html = '';

            $html .= '<div class="awl-rules">';

                $html .= '<script id="awlRulesTemplate" type="text/html">';

                    $html .= $default_rule->get_rule();

                $html .= '</script>';

                $html .= '<div class="awl-rules-desc">';
                    $html .= __( 'To display the label product must match all of the following conditions.', 'advanced-woo-labels' );
                $html .= '</div>';

                if ( $label && ! empty( $label ) && isset( $label['conditions'] ) ) {

                    foreach( $label['conditions'] as $group_id => $group_rules ) {

                        $group_id = is_string( $group_id ) ? str_replace( 'group_', '', $group_id ) : $group_id;

                        $html .= '<table class="awl-rules-table" data-awl-group="' . esc_attr( $group_id ) . '">';
                            $html .= '<tbody>';

                            foreach( $group_rules as $rule_id => $rule_values ) {

                                $rule_id = is_string( $rule_id ) ? str_replace( 'rule_', '', $rule_id ) : $rule_id;

                                if ( isset( $rule_values['param'] ) ) {
                                    $current_rule = new AWL_Admin_Label_Rules( AWL_Admin_Options::include_rule_by_id( $rule_values['param'] ), $group_id, $rule_id, $rule_values );
                                    $html .= $current_rule->get_rule();
                                }

                            }

                            $html .= '</tbody>';
                        $html .= '</table>';

                    }

                } else {

                    $html .= '<table class="awl-rules-table" data-awl-group="1">';
                        $html .= '<tbody>';
                            $html .= $default_rule->get_rule();
                        $html .= '</tbody>';
                    $html .= '</table>';

                }

                $html .= '<a href="#" class="button add-rule-group" data-awl-add-group>' . __( "Add 'or' group", "advanced-woo-labels" ) . '</a>';

            $html .= '</div>';

            return $html;

        }

        /*
         * Get content for label settings meta box
         * @return string
         */
        static public function get_settings_meta_box( $label ) {

            $settings_array = AWL_Admin_Options::include_label_settings();
            $settings_obg = new AWL_Admin_Label_Settings( $settings_array, $label );
            $html = '';

            $html .= '<div class="awl-label-settings awl-first-init">';

                $html .= '<div class="awl-column-table">';

                    $html .= $settings_obg->generate_fields();

                $html .= '</div>';

                $html .= '<div class="awl-column-preview">';

                    $html .= '<div id="awl-preview">';

                        $html .= '<h5 class="title">' . __( "Preview", "advanced-woo-labels" ) . '</h5>';

                        $html .= '<div class="awl-preview-container">';

                            $html .= '<div class="image-wrapper">';

                                $html .= '<div class="advanced-woo-labels awl-align-left-top awl-position-type-image" style="top:0;left:0;">';

                                    $html .= '<div class="awl-label-wrap">';

                                        $html .= '<span class="awl-product-label awl-type-label">';

                                            $html .= '<span class="awl-label-before">';
                                                $html .= '<svg viewBox="0 0 100 100" preserveAspectRatio="none">';

                                                $html .= '</svg>';
                                            $html .= '</span>';

                                            $html .= '<span class="awl-label-text">';
                                                $html .= '<span class="awl-inner-text"></span>';
                                            $html .= '</span>';

                                            $html .= '<span class="awl-label-after">';
                                                $html .= '<svg viewBox="0 0 100 100" preserveAspectRatio="none">';

                                                 $html .= '<g class="awl-triangle">';
                                                    $html .= '<polygon vector-effect="non-scaling-stroke" points="0,0 0,100 97,50" style="stroke:none;" />';
                                                    $html .= '<line vector-effect="non-scaling-stroke" x1="0" y1="0" x2="97" y2="50" />';
                                                    $html .= '<line vector-effect="non-scaling-stroke" x1="97" y1="50" x2="0" y2="100" />';
                                                 $html .= '</g>';

                                                 $html .= '<g class="awl-right-angle">';
                                                    $html .= '<polygon vector-effect="non-scaling-stroke" points="0,0 97,0 0,100" style="stroke:none;" />';
                                                    $html .= '<line class="d-stroke" vector-effect="non-scaling-stroke" x1="0" y1="0" x2="97" y2="0" />';
                                                    $html .= '<line vector-effect="non-scaling-stroke" x1="97" y1="0" x2="0" y2="100" />';
                                                 $html .= '</g>';

                                                $html .= '</svg>';
                                            $html .= '</span>';

                                            $html .= '<span class="awl-label-image"><img src=""></span>';

                                        $html .= '</span>';

                                    $html .= '</div>';

                                $html .= '</div>';

                                $html .= '<img src="' .  esc_url( AWL_URL . '/assets/img/preview.png' ) . '">';

                            $html .= '</div>';

                            $html .= '<h4 class="product-name">' . __( "Product name", "advanced-woo-labels" ) . '</h4>';
                            $html .= '<h5 class="product-price"><del>$100</del>$90</h5>';

                        $html .= '</div>';

                    $html .= '</div>';

                $html .= '</div>';

            $html .= '</div>';
            
            return $html;

        }


    }

endif;