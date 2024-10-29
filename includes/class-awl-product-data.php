<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWL_Product_Data' ) ) :

    /**
     * Class for plugin help methods
     */
    class AWL_Product_Data {
        
        /**
         * Get product sales based on date query
         * @since 1.0
         * @param  string $query Date query
         * @param  object $product Product
         * @return integer
         */
        static public function get_sales_count( $query, $product ) {
            global $woocommerce;

            $value = 0;

            if ( $query === 'all' ) {

                $value = method_exists( $product, 'get_total_sales' ) ? $product->get_total_sales() : get_post_meta( $product->get_id(), 'total_sales', true );

            } else {

                include_once( $woocommerce->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php' );
                $wc_report = new WC_Admin_Report();

                $data = $wc_report->get_order_report_data(
                    array(
                        'data'         => array(
                            '_product_id' => array(
                                'type'            => 'order_item_meta',
                                'order_item_type' => 'line_item',
                                'function'        => '',
                                'name'            => 'product_id',
                            ),
                            '_qty'     => array(
                                'type'            => 'order_item_meta',
                                'order_item_type' => 'line_item',
                                'function'        => 'SUM',
                                'name'            => 'sales',
                            ),
                            'post_date'   => array(
                                'type'     => 'post_data',
                                'function' => '',
                                'name'     => 'post_date',
                            ),
                        ),
                        'where'        => array(
                            array(
                                'key'      => 'post_date',
                                'value'    => date( 'Y-m-d', strtotime( $query, current_time( 'timestamp' ) ) ),
                                'operator' => '>',
                            ),
                            array(
                                'key'      => 'order_item_meta__product_id.meta_value',
                                'value'    => $product->get_id(),
                                'operator' => '=',
                            ),
                        ),
                        'group_by'     => 'product_id',
                        'query_type'   => 'get_results',
                        'filter_range' => false,
                    )
                );

                if ( $data && is_array( $data ) ) {
                    $value = $data[0]->sales;
                }

            }

            return $value;

        }

        /**
         * Get product quantity
         * @since 1.0
         * @param  object $product Product
         * @return integer
         */
        static public function get_quantity( $product ) {

            $stock_levels = array();

            if ( $product->is_type( 'variable' ) ) {
                foreach ( $product->get_children() as $variation ) {
                    $var = wc_get_product( $variation );
                    $stock_levels[] = $var->get_stock_quantity();
                }
            } else {
                $stock_levels[] = $product->get_stock_quantity();
            }

            return max( $stock_levels );

        }

        /**
         * Get product reviews count
         * @since 1.0
         * @param  string $query Date query
         * @param  object $product Product
         * @return integer
         */
        static public function get_reviews_count( $query, $product ) {

            if ( $query === 'all' ) {

                $value = $product->get_review_count();

            } else {

                $value = get_comments( array(
                    'post_id'    => $product->get_id(),
                    'count'      => true,
                    'date_query' => $query
                ));

            }

            return $value;

        }

    }

endif;