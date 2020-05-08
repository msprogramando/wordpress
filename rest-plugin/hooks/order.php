<?php

namespace Pro_Rest\Hooks\Order;

use Pro_Rest\Helpers\Rest\Backend_Request;
use Pro_Woocommerce\Woocommerce\Customer;

class Order_Processed{

    public function __construct() {
        add_action('woocommerce_checkout_order_processed', [$this, 'woocommerce_checkout_order_processed']);
    }

    function woocommerce_checkout_order_processed( $order_id ){

        $order = wc_get_order( $order_id );
        $user_id = $order->get_user_id();
        $customer = new Customer($user_id);

        $data = $order->get_data();
        $data['billing']['job_title'] = get_user_meta($user_id, 'billing_job_title', true);
        $data['billing']['company_tax_id'] = get_user_meta($user_id, 'billing_company_tax_id', true);

        $body = [
            'website_url' => get_site_url(),
            'order_id' => $order_id,
            'date' => $order->order_date,
            'billing' => $data['billing'],
            'shipping' => $data['shipping'],
            'shipping_lines' => $this->fetch_shipping_lines($data['shipping_lines']),
            'payment_method' => $data['payment_method'],
            'status' => $data['status'],
            'client' => $customer->to_array(),
            'items' => $this->get_items($order)
        ];

        $request = new Backend_Request();

        if(!is_wp_error($request->token)){
            $request->method = 'POST';
            $request->type = 'order registration';

            if(defined('ENVIRONMENT') && ENVIRONMENT === 'local'){
                $request->url = 'http://admin.laravel.docker/api/v1/orders';
            } else {
                $request->url = 'https://admin.laravel.com/api/v1/orders';
            }

            $request->data = $body;
            $request->send();
        }
        else {
            wp_mail('martin@....de', 'REST Error during checkout', '...');
        }
    }

    private function get_items($order){
        $items = [];
        if($order->get_items()) {
            foreach ( $order->get_items() as $item_id => $item ) {

                $data = $item->get_data();
                $data['sku'] = get_post_meta($data['variation_id'], '_sku', true);
                unset($data['meta_data']);

                $items[] = $data;
            }
        }
        return $items;
    }

    private function fetch_shipping_lines($shipping_lines){
        $data = [];
        if($shipping_lines){
            $data = [];
            foreach ($shipping_lines as $shipping_line) {
                $sl = $shipping_line->get_data();
                $data[] = [
                    'total' => $sl['total'],
                    'total_tax' => $sl['total_tax'],
                    'method_title' => $sl['method_title']
                ];
            }
        }
        return $data;
    }
}