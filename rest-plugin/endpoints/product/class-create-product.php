<?php

namespace Pro_Rest\Endpoints\Product;

use Pro_Woocommerce\Woocommerce\Product;
use Pro_Woocommerce\Woocommerce\Variant;

class Create_Product extends \WP_REST_Controller {

    public function __construct(){
        add_action( 'rest_api_init', array( $this, 'route' ));
    }

    public function route(){
        register_rest_route( 'pro/v1', '/product', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'callback' )
        ));
    }

    public function callback( $request ){
        $params = $request->get_params();
        $product = $params['product'];
        $variants = $params['variants'];

        /**
         * Inset the Post and Post Meta
         */

        $new_product = new Product();
        $response = $new_product->create($product);

        /**
         * Then insert the product with its variations
         */

        if($variants){

            $new_variant = new Variant();
            $new_variant->createMany($variants, $response['insert_id']);

        }

        return new \WP_REST_Response( $response , 200 );
    }
}