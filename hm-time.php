<?php
/**
 * Plugin Name: Time
 * Description: Define working hours / timezones for users
 * Author: Jenny Wong
 * Author URI: http://jwong.co.uk/
 * Version: 0.1.0
 * Plugin URI: https://github.com/humanmade/hm-time
 */
if ( ! defined( 'ABSPATH' ) ){
	exit;
}
register_activation_hook(__FILE__, 'hm_time_install');
register_deactivation_hook(__FILE__, 'hm_time_uninstall');

function hm_time_install(){

}

function hm_time_uninstall(){

}

include('includes/user-profile-fields.php');