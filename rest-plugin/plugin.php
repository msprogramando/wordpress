<?php
/*
Plugin Name: REST Plugin
Description: Provides REST Endpoints
Version: 0.0.1
Author: programando.de
Author URI: https://programando.de
Text Domain: pro-rest
Domain Path: /languages
*/

namespace Pro_Rest;

use Pro_Rest\Endpoints\Order\Update_Status;
use Pro_Rest\Endpoints\User\Update_User;

use Pro_Rest\Hooks\Order\Order_Processed;
use Pro_Rest\Hooks\User\User_Registration;
use Pro_Rest\Hooks\User\User_Status_Column;

require( trailingslashit( dirname( __FILE__ ) ) . 'inc/autoloader.php' );

add_action('plugins_loaded', 'Bieglo_Rest\endpoints_init');

function endpoints_init(){
    new Update_Product();
    new Update_Stocks();
    new Create_Product();
    new Update_Variation();
    new User_Registration();
    new Update_User();
    new User_Status_Column();
    new Order_Processed();
    new Update_Status();
}

