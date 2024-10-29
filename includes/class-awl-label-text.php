<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWL_Label_Text' ) ) :

    /**
     * AWL Conditions check class
     */
    class AWL_Label_Text {

        protected $text = '';

        protected $matches = '';

        protected $replacement = '';

        protected $variables = array(
            '/{PRICE}/i' => 'price',
            '/{SALE_PRICE}/i' => 'sale_price',
            '/{SAVE_PERCENT\s*\|*\s*([\d]*)\s*}/i' => 'save_percent',
            '/{SAVE_AMOUNT\s*\|*\s*([\d]*)\s*}/i' => 'save_amount',
            '/{SALE_ENDS}/i' => 'sale_ends',
            '/{SYMBOL}/i' => 'currency_symbol',
            '/{SKU}/i' => 'sku',
            '/{QTY}/i' => 'quantity',
            '/{BR}/i' => 'br',
        );

        /*
         * Constructor
         */
        public function __construct( $text ) {

            $this->text = $text;

            /**
             * Filter labels text vars
             * @since 1.00
             * @param array $this->variables Array of text variables
             */
            $this->variables = apply_filters( 'awl_labels_text_vars', $this->variables );

        }

        /*
         * Get label text
         */
        public function text() {

            if ( ! isset( $GLOBALS['product'] ) ) {
                return $this->text;
            }

            foreach ( $this->variables as $rule => $replacement_f ) {
                if ( preg_match( $rule, $this->text ) ) {
                    $this->replacement = is_array( $replacement_f ) ? $replacement_f['func'] : array( $this, $replacement_f );
                    $this->text = preg_replace_callback( $rule, array( $this, 'replace'), $this->text );
                }
            }

            return $this->text;

        }

        /*
         * Replace callback
         */
        private function replace( $matches ) {
            if ( isset( $matches[1] ) && trim( $matches[1] ) === '' ) {
                unset( $matches[1] );
            }
            $this->matches = $matches;
            return call_user_func( $this->replacement );
        }

        /*
         * Get price
         */
        private function price() {
            global $product;
            $price = $product->get_price() ? get_woocommerce_currency_symbol() . $product->get_price() : '';
            return $price;
        }

        /*
         * Get sale price
         */
        private function sale_price() {
            global $product;
            $sale_price = ( $product->is_on_sale() && $product->get_sale_price() ) ? get_woocommerce_currency_symbol() . $product->get_sale_price() : '';
            return $sale_price;
        }

        /*
         * Get discount percentage
         */
        private function save_percent() {
            global $product;
            $round = isset( $this->matches[1] ) ? intval( $this->matches[1] ) : 0;
            $save_percents = ( $product->is_on_sale() && $product->get_sale_price() ) ? round( ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100, $round ) : '';
            return $save_percents;
        }

        /*
         * Get discount amount
         */
        private function save_amount() {
            global $product;
            $round = isset( $this->matches[1] ) ? intval( $this->matches[1] ) : 0;
            $save_amount = ( $product->is_on_sale() && $product->get_sale_price() ) ? round( $product->get_regular_price() - $product->get_sale_price(), $round ) : '';
            return $save_amount;
        }

        /*
         * Get sale end date in days
         */
        private function sale_ends() {
            global $product;
            $sale_ends = ( $product->is_on_sale() && $product->get_sale_price() && method_exists( $product, 'get_date_on_sale_to' ) && $product->get_date_on_sale_to() ) ? round( ( strtotime( $product->get_date_on_sale_to() ) - time() ) / ( 60 * 60 * 24 ) ) : '';
            return $sale_ends;
        }

        /*
         * Get currency symbol
         */
        private function currency_symbol() {
            $symbol = get_woocommerce_currency_symbol();
            return $symbol;
        }

        /*
         * Get product SKU
         */
        private function sku() {
            global $product;
            $sku = $product->get_sku() ? $product->get_sku() : '';
            return $sku;
        }

        /*
         * Get product quantity
         */
        private function quantity() {
            global $product;
            $value = AWL_Product_Data::get_quantity( $product );
            $quantity = $value ? $value : '';
            return $quantity;
        }

        /*
        * Replace br
        */
        private function br() {
            return '<br>';
        }

    }

endif;