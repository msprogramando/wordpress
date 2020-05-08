<?php
/*
Plugin Name: Pro WooCommerce
Description: Classes and helpers for managing shop data
Version: 0.0.1
Author: programando.de
Author URI: https://programando.de
Text Domain: pro-woocommerce
Domain Path: /languages
*/

namespace Pro_Woocommerce;

use Pro_Woocommerce\Hooks\Theme\Theme_Hooks;
use Pro_Woocommerce\Hooks\User\Registration_Form;
use Pro_Woocommerce\Hooks\User\Woocommerce_Form;
use Pro_Woocommerce\Woocommerce\Variant_Picker;
use Pro_Woocommerce\Hooks\Cart\Woocommerce_Cart;

require( trailingslashit( dirname( __FILE__ ) ) . 'inc/autoloader.php' );

add_action('plugins_loaded', 'Pro_Woocommerce\woocommerce_init');

function woocommerce_init(){
    new Registration_Form();
    new Woocommerce_Form();
    new Theme_Hooks();
    new Variant_Picker();
    new Woocommerce_Cart();
}