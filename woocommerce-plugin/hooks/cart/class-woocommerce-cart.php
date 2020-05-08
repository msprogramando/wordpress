<?php

namespace Pro_Woocommerce\Hooks\Cart;

use Pro_Rest\Helpers\Rest\Backend_Request;
use Pro_Woocommerce\Woocommerce\Customer;

class Woocommerce_Cart {

  public function __construct()
  {
      add_action('woocommerce_billing_fields', [$this, 'add_cart_billing_fields'], 10, 1);
      add_filter('woocommerce_checkout_fields', [$this, 'reorder_billing_fields']);
  }

  /**
   * Adds some fields to the card during checkout
   */

  function add_cart_billing_fields($fields){
    $user_id = get_current_user_id();

    $job_title = get_user_meta($user_id, 'billing_job_title', true);
    $tax_id = get_user_meta($user_id, 'billing_company_tax_id', true);

    $fields['billing_job_title'] = array(
        'label' => __('Job Title', 'woocommerce'),
        'placeholder' => _x('', 'placeholder', 'woocommerce'),
        'required' => true,
        'clear' => false,
        'type' => 'text',
        // 'class' => ['form-row-last'],
        'priority' => 2,
        'default' => $job_title
    );

    $fields['billing_company_tax_id'] = array(
        'label' => __('Company Tax Id', 'woocommerce'),
        'placeholder' => _x('The tax number of your company', 'placeholder', 'woocommerce'),
        'required' => true,
        'clear' => false,
        'type' => 'text',
        'default' => $tax_id
    );

    return $fields;
  }

  function reorder_billing_fields( $checkout_fields ) {

    $checkout_fields['billing']['billing_title']['priority'] = 1;
    // $checkout_fields['billing']['billing_title']['class'] = ['form-row-first'];
    $checkout_fields['billing']['billing_title']['required'] = true;
    
    $checkout_fields['billing']['billing_job_title']['priority'] = 30;
      
    $checkout_fields['billing']['billing_company_tax_id']['priority'] = 30;
    return $checkout_fields;
  }
}