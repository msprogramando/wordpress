<?php

namespace Pro_Woocommerce\Woocommerce;

use WC_Product_Factory;
use WC_Product_Variable;

class Variant_Picker {

    private $product_id;
    private static $COMBINATION_TRANSIENT_KEY = 'pro_product_combinations_';
    private static $VARIATION_TRANSIENT_KEY = 'pro_product_variations_';

    public function __construct(){
        $this->add_actions();
//        add_action( 'wp_loaded', [$this, 'fetch_combinations'] );
    }

    public function add_actions(){
        add_action( 'wp_ajax_reduce-variations', [$this, 'reduce'] );
        add_action( 'wp_ajax_nopriv_reduce-variations', [$this, 'reduce'] );

        add_action( 'wp_ajax_reset-variations', [$this, 'reset'] );
        add_action( 'wp_ajax_nopriv_reset-variations', [$this, 'reset'] );

        add_action( 'wp_enqueue_scripts', [$this, 'add_scripts'] );
    }

    public function add_scripts(){
        wp_register_script('pro-variation-picker', plugins_url() . '/pro-woocommerce/assets/variation-picker.js', ['jquery']);
        wp_enqueue_script('pro-variation-picker');
        wp_localize_script( 'pro-variation-picker', 'pro', [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
    }

    public function set_product_id($id){
        $this->product_id = $id;
    }

    /**
     * Ajax call for resetting the varitation options
     */

    public function reset()
    {
        $product_id = $_POST['product_id'];
        if (!$product_id) {
            echo json_encode('no product id provided');
            wp_die();
        }

        $cache = get_transient(self::$VARIATION_TRANSIENT_KEY . $product_id);
        if (!$cache) {
            $cache = self::store_variation_transient($product_id);
        }

        echo json_encode($cache);
        wp_die();
    }
    /**
     * Fetch the Variation array from db and store it in the db
     *
     * @param $product_id
     * @return array
     */

    public static function store_variation_transient($product_id){
        $variations = self::fetch_variations($product_id);
        set_transient(self::$VARIATION_TRANSIENT_KEY. $product_id, $variations);
        return $variations;
    }

    /**
     * Creates an array of all variation paramters
     *
     * @param $product_id
     * @return array
     */

    public function fetch_variations($product_id){
        $product = wc_get_product( $product_id );
        $variations = $product->get_available_variations();
        $attributes = [];

        if($variations){
            foreach ($variations as $variation) {
                $attribute_values = $variation['attributes'];

                foreach ($attribute_values as $key => $attribute) {
                    $attribute = (int) str_replace('mm', '', $attribute);

                    if(! array_key_exists( $key ,$attributes )){
                        $attributes[$key] = [];
                    }

                    if(!in_array($attribute, $attributes[$key])){
                        $attributes[$key][] = $attribute;
                    }
                }
            }

            foreach ($attributes as $key => $attribute){
                sort($attributes[$key]);
                $attributes[$key] = array_map(function($a) {
                    return $a . ' mm';
                }, $attributes[$key]);
            }

            return $attributes;
        }
    }

    /**
     * Ajax call for reducing the varitation options
     */

    public function reduce(){
        $product_id = $_POST['product_id'];
        if(!$product_id) {
            echo json_encode('no product id provided');
            wp_die();
        }

        $cache = get_transient(self::$COMBINATION_TRANSIENT_KEY.$product_id);
        if(!$cache){
            $cache = self::store_combination_transient($product_id);
        }

        echo json_encode($cache);
        wp_die();
    }

    /**
     * Fetch the Combination array from db and store it in the db
     *
     * @param $product_id
     * @return array
     */

    public function store_combination_transient($product_id){
        $combinations = self::fetch_combinations($product_id);
        set_transient(self::$COMBINATION_TRANSIENT_KEY . $product_id, $combinations);
        return $combinations;
    }

    /**
     * Creates an array of all possible combinations
     *
     * @param $product_id
     * @return array
     */

    public function fetch_combinations($product_id){
//        $product_id = 4237;
        $product = wc_get_product( $product_id );
        $variations = $product->get_available_variations();
        $combinations = [];

        if($variations){
            foreach ($variations as $variation) {

                $attribute_keys = array_keys($variation['attributes']);
                $attribute_values= $variation['attributes'];

                foreach ($attribute_keys as $attribute_key) {

                    $length = count($attribute_keys) - 1;
                    $attribute_value = $attribute_values[$attribute_key];

                    for ($i=0; $i<=$length; $i++){

                        $attribute_sub_key = $attribute_keys[$i];
                        $attribute_sub_value = $attribute_values[$attribute_sub_key];

                        if($attribute_key == $attribute_sub_key && !array_key_exists($attribute_key, $combinations)){
                            $combinations[$attribute_key][$attribute_value] = [];
                        }

                        if($attribute_key !== $attribute_sub_key){
                            $combinations[$attribute_key][$attribute_value][$attribute_sub_key][] = $attribute_sub_value;
                        }

                    }

                }

            }

            foreach ($combinations as $attribute_key => $attribute_values) {
                foreach ($attribute_values as $attribute_value => $attribute_combinations) {
                    foreach ($attribute_combinations as $attribute_combinations_key => $attribute_combinations_value) {

                        $new_values = array_map(function($value){
                            return (int) str_replace(' mm', '', $value);
                        }, $attribute_combinations_value );

                        sort($new_values);
                        $new_values = array_values(array_unique($new_values));

                        $new_values = array_map(function($value){
                            return $value . ' mm';
                        }, $new_values );

                        $combinations[$attribute_key][$attribute_value][$attribute_combinations_key] = $new_values;
                    }
                }
            }

            return $combinations;
        }
    }
}