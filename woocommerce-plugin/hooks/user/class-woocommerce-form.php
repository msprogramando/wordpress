<?php

namespace Pro_Woocommerce\Hooks\User;

use Pro_Rest\Helpers\Rest\Backend_Request;
use Pro_Woocommerce\Woocommerce\Customer;

class Woocommerce_Form {

  public function __construct()
  {
      add_action( 'woocommerce_register_form_start', [$this, 'add_fields'] );
      add_action( 'woocommerce_register_post', [$this, 'wooc_validate_extra_register_fields'], 10, 3 );
      add_action( 'woocommerce_created_customer', [$this, 'wooc_save_extra_register_fields'],  10, 1);
      add_action( 'user_register', [$this, 'auto_login_after_registration']);
  }


  function wooc_save_extra_register_fields( $customer_id ) {
      if ( isset( $_POST['username'] ) ) {
          update_user_meta( $customer_id, 'username', sanitize_text_field( $_POST['username'] ) );
      }
      if ( isset( $_POST['company'] ) ) {
          update_user_meta( $customer_id, 'billing_company', sanitize_text_field( $_POST['company'] ) );
      }
      if ( isset( $_POST['website'] ) ) {
          update_user_meta( $customer_id, 'website', sanitize_text_field( $_POST['website'] ) );
      }

      update_user_meta($customer_id, 'status', 'pending');

      $customer = new Customer($customer_id);
      $request = new Backend_Request();

      if(!is_wp_error($request->token)){
          $request->method = 'POST';
          $request->type = 'register user';

          if(defined('ENVIRONMENT') && ENVIRONMENT === 'local'){
            $request->url = 'http://admin.laravel.docker/api/v1/clients';
          } else {
            $request->url = 'https://admin.laravel.com/api/v1/clients';
          }

          $request->data = $customer->to_array();
          $result = $request->send();

          if($result->status === 'confirmed'){
              update_user_meta($customer_id, 'status', 'confirmed');
          }
      }
      else {
          update_option('token_error', $request->token);
      }

      wp_redirect( home_url() );
      exit;
  }

  /**
   * Varlidate the Registration Form
   *
   * @param $username
   * @param $email
   * @param $validation_errors
   * @return mixed
   */

  public function wooc_validate_extra_register_fields( $username, $email, $validation_errors ) {
    if ( isset( $_POST['username'] ) && empty( $_POST['username'] ) ) {
        $validation_errors->add( 'username', __( '<strong>Error</strong>: Username is required!', 'woocommerce' ) );
    }
    if ( isset( $_POST['company'] ) && empty( $_POST['company'] ) ) {
        $validation_errors->add( 'company', __( '<strong>Error</strong>: Company name is required!', 'woocommerce' ) );
    }
    if ( isset( $_POST['website'] ) && empty( $_POST['website'] ) ) {
          $validation_errors->add( 'website', __( '<strong>Error</strong>: Website Url is required!.', 'woocommerce' ) );
    }
    if($_POST['website'] === 'https://' || $_POST['website'] === 'http://'){
          $validation_errors->add( 'website', __( '<strong>Error</strong>: Website Url is required!.', 'woocommerce' ) );
    }
    return $validation_errors;
  }

  /**
   * Add additonal fields to the woocommerce registration from
   */

  public function add_fields() { ?>
    <p class="form-row form-row-first">
      <label for="username">
        <?php esc_html_e( 'Username', 'pro' ) ?>
        <span class="required" required="required">*</span>
      </label>
      <input type="text" id="username" name="username"  class="woocommerce-Input woocommerce-Input--text input-text" />
    </p>
    <p class="form-row form-row-last">
      <label for="company">
        <?php esc_html_e( 'Company Name', 'pro' ) ?>
        <span class="required" required="required">*</span>
      </label>
      <input type="text" id="company" name="company"  class="woocommerce-Input woocommerce-Input--text input-text" />
    </p>
    <p class="form-row form-row-wide">
      <label for="website">
        <?php esc_html_e( 'Website', 'pro' ) ?>
        <span class="required" required="required">*</span>
      </label>
      <input type="url" id="website" name="website" class="woocommerce-Input woocommerce-Input--text input-text" value="https://" />
    </p>
    <div class="clear"></div>
      <?php
  }


  function auto_login_after_registration( $user_id ) {
      wp_set_auth_cookie($user_id);
      wp_set_current_user($user_id);
  }
}