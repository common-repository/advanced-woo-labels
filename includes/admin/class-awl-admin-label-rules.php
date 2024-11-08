<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWL_Admin_Label_Rules' ) ) :

    /**
     * Class for admin condition rules
     */
    class AWL_Admin_Label_Rules {

        /**
         * @var array AWL_Admin_Label_Rules The array of rule parameters
         */
        private $rule;

        /**
         * @var string AWL_Admin_Label_Rules Group ID
         */
        private $group_id;

        /**
         * @var string AWL_Admin_Label_Rules Rule ID
         */
        private $rule_id;

        /**
         * @var array AWL_Admin_Label_Rules Values array
         */
        private $value;

        /**
         * @var string AWL_Admin_Label_Rules Field name
         */
        private $field_name = '';

        /*
         * Constructor
         */
        public function __construct( $rule, $group_id = 1, $rule_id = 1, $value = false ) {

            $this->rule = $rule;
            $this->group_id = $group_id;
            $this->rule_id = $rule_id;
            $this->value = $value;

        }

        /*
         * Get field html markup
         * @param $type string Field type
         * @return string
         */
        public function get_field( $type ) {

            $this->field_name = "awl_label_params[conditions][group_{$this->group_id}][rule_{$this->rule_id}][{$type}]";

            return call_user_func( array( $this, 'get_rule_' . $type ) );

        }

        /*
         * Rule html
         * @return string
         */
        public function get_rule() {

            $params_html = $this->get_field( 'param' );
            $operators_html = $this->get_field( 'operator' );
            $values_html = $this->get_field( 'value' );
            $suboptions_html = $this->get_field( 'suboption' );

            $class = $suboptions_html ? ' adv' : '';

            $rule_html = '';

            $rule_html .= '<tr class="awl-rule' . $class . '" data-awl-rule="' . esc_attr( $this->rule_id ) . '">';

                $rule_html .= '<td class="param">';
                    $rule_html .= $params_html . $suboptions_html;
                $rule_html .= '</td>';

                $rule_html .= '<td class="operator" data-awl-operator>';
                    $rule_html .= $operators_html;
                $rule_html .= '</td>';

                $rule_html .= '<td class="value" data-awl-value>';
                    $rule_html .= $values_html;
                $rule_html .= '</td>';

                $rule_html .= '<td class="add">';
                    $rule_html .= '<a href="#" title="' . __( 'Add new rule', 'advanced-woo-labels' ) . '" class="button add-rule" data-awl-add-rule>' . __( 'and', 'advanced-woo-labels' ) . '</a>';
                $rule_html .= '</td>';

                $rule_html .= '<td class="remove">';
                    $rule_html .= '<a href="#" title="' . __( 'Remove rule', 'advanced-woo-labels' ) . '" class="button remove-rule" data-awl-remove-rule>&#150;</a>';
                $rule_html .= '</td>';

            $rule_html .= '</tr>';

            return $rule_html;

        }

        /*
         * Rules params html
         * @param $rule array Rule
         * @return string
         */
        private function get_rule_param() {

            $rules = AWL_Admin_Options::include_rules();
            $val = ( $this->value && is_array( $this->value ) && isset( $this->value['param'] ) ) ? $this->value['param'] : '';

            $rules_html = '<select name="' . esc_attr( $this->field_name ) . '" class="param-val" data-awl-param>';

            foreach ( $rules as $rule_section => $section_rules ) {

                $section_label = AWL_Admin_Options::get_rule_section( $rule_section );

                $rules_html .= '<optgroup label="' . esc_html( $section_label ) . '">';

                foreach ( $section_rules as $section_rule ) {
                    $rules_html .= '<option ' . selected( $val, $section_rule['id'], false ) . ' value="'. esc_attr( $section_rule['id'] ) .'">'. esc_html( $section_rule['name'] ) .'</option>';
                }

                $rules_html .= '</optgroup>';

            }

            $rules_html .= '</select>';

            return $rules_html;

        }

        /*
         * Rules suboptions html
         * @return string
         */
        private function get_rule_suboption() {

            $val = ( $this->value && is_array( $this->value ) && isset( $this->value['suboption'] ) ) ? $this->value['suboption'] : '';
            $rules_html = '';

            if ( isset( $this->rule['suboption'] ) && isset( $this->rule['suboption']['callback'] ) && isset( $this->rule['suboption']['params'] ) ) {
                $values_callback = self::get_rule_callback_options( $this->rule['suboption']['callback'], $this->rule['suboption']['params'], $val );

                if ( $values_callback ) {
                    $rules_html = '<select name="' . esc_attr( $this->field_name ) . '" class="suboption-val" data-awl-suboption>' . $values_callback . '</select>';
                } else {
                    $rules_html = '<select name="' . esc_attr( $this->field_name ) . '" class="suboption-val" data-awl-suboption><option value=""></option></select>';
                }

            }

            return $rules_html;

        }

        /*
         * Rules values html markup
         * @return string
         */
        private function get_rule_value() {

            $val = ( $this->value && is_array( $this->value ) && isset( $this->value['value'] ) ) ? $this->value['value'] : '';
            $sub_val = ( $this->value && is_array( $this->value ) && isset( $this->value['suboption'] ) ) ? $this->value['suboption'] : '';
            $values = '';

            switch( $this->rule['type'] ) {

                case 'callback';

                    $callback_function = $this->rule['choices']['callback'];
                    $callback_params = $sub_val ? array( $sub_val ) : $this->rule['choices']['params'];

                    if ( isset( $this->rule['suboption'] ) && empty( $callback_params ) ) {
                        $values_suboption = call_user_func_array( $this->rule['suboption']['callback'], $this->rule['suboption']['params'] );
                        if ( $values_suboption && is_array( $values_suboption ) && ! empty( $values_suboption ) ) {
                            foreach ( $values_suboption as $values_val => $values_name ) {
                                if ( is_array( $values_name ) && isset( $values_name['value'] ) ) {
                                    $values_val = $values_name['value'];
                                }
                                $callback_params = array( $values_val );
                                break;
                            }
                        }
                    }

                    $values_callback = $this->get_rule_callback_options( $callback_function, $callback_params, $val );

                    if ( $values_callback ) {
                        if ( isset( $this->rule['suboption'] ) ) {
                            $values_callback = '<option value="awl_any">' . __( "Any", "advanced-woo-labels" ) . '</option>' . $values_callback;
                        }
                        $values = '<select name="' . esc_attr( $this->field_name ) . '" class="value-val">' . $values_callback . '</select>';
                    } else {
                        $values = '<select name="' . esc_attr( $this->field_name ) . '" class="value-val"><option value=""></option></select>';
                    }

                    break;

                case 'bool';

                    $values .= '<select name="' . esc_attr( $this->field_name ) . '" class="value-val">';
                    $values .= '<option ' . selected( $val, 'true', false ) . ' value="true">' . __( "Yes", "advanced-woo-labels" ) . '</option>';
                    $values .= '<option ' . selected( $val, 'false', false ) . ' value="false">' . __( "No", "advanced-woo-labels" ) . '</option>';
                    $values .= '</select>';

                    break;

                case 'number';

                    $step = isset( $this->rule['step'] ) ? 'step="' . esc_attr( $this->rule['step'] ) . '"' : '';
                    $values .= '<input type="number" name="' . esc_attr( $this->field_name ) . '" value="' . esc_attr( $val ) . '" class="value-val" min="0" '.$step.'>';

                    break;

                case 'text';

                    $placeholder = $this->rule['placeholder'] ? $this->rule['placeholder'] : '';
                    $values .= '<input type="text" name="' . esc_attr( $this->field_name ) . '" value="' . esc_attr( $val ) . '" class="value-val" placeholder="' . esc_html( $placeholder ) . '">';

                    break;

            }

            return $values;

        }

        /*
         * Rules operators html markup
         * @return string
         */
        private function get_rule_operator() {

            $val = ( $this->value && is_array( $this->value ) && isset( $this->value['operator'] ) ) ? $this->value['operator'] : '';
            $operators = AWL_Admin_Options::get_rule_operators( $this->rule['operators'] );

            $operators_html = '<select name="' . esc_attr( $this->field_name ) . '" class="operator-val">';

            foreach ( $operators as $operator ) {
                $operators_html .= '<option ' . selected( $val, $operator['id'], false ) . ' value="' . esc_attr( $operator['id'] ) . '">' . esc_html( $operator['name'] ) . '</option>';
            }

            $operators_html .= '</select>';

            return $operators_html;

        }

        /*
         * Rules callback options
         * @param $callback string Function name
         * @param $params array Function parameters
         * @return string
         */
        private function get_rule_callback_options( $callback, $params, $value = false ) {

            $values = '';
            $values_arr = call_user_func_array( $callback, $params );

            if ( $values_arr && is_array( $values_arr ) && ! empty( $values_arr ) ) {
                foreach ( $values_arr as $values_val => $values_name ) {
                    if ( is_array( $values_name ) && isset( $values_name['name'] ) && isset( $values_name['value'] ) ) {
                        $values_val = $values_name['value'];
                        $values_name = $values_name['name'];
                    }
                    $values .= '<option ' . selected( $value, $values_val, false ) . '  value="' . esc_attr( $values_val ) . '">' . esc_html( $values_name ) . '</option>';
                }
            }

            return $values;

        }

    }

endif;