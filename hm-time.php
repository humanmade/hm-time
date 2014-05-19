<?php
/**
 * Plugin Name: Time
 * Description: Define working hours / timezones for users
 * Author: Jenny Wong
 * Author URI: http://jwong.co.uk/
 * Version: 0.1.0
 * Plugin URI: https://github.com/humanmade/hm-time
 * License: GPLv2 or later
 */

/*  Copyright 2014

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ){
	exit;
}

define('PLUGIN_URL', plugin_dir_url(__FILE__));


register_activation_hook(__FILE__, 'hm_time_install');
register_deactivation_hook(__FILE__, 'hm_time_uninstall');

function hm_time_install(){
	$hm_tz_options = array(
		'default_set_method' => 'manual',
		'geoip_user_id' 	 => '',
		'geoip_license_key'	=> '',
		'foursquare_client_id'	=> '',
		'foursquare_client_secret' => '',
		'google_timezone_api_key' => ''
	);
	// future > add in filter hook here to be able to extend
	update_option('hm_time_options', $hm_tz_options);
}

function hm_time_uninstall(){

}

require_once('includes/user-profile-fields.php');
require_once('settings.php');

$geoip_user_id = hm_time_options('geoip_user_id');
if(!empty($geoip_user_id)){
	require_once ('vendor/autoload.php');
	require_once ('includes/geoip.php');
}

$foursquare_client_id = hm_time_options('foursquare_client_id');
if(!empty($foursquare_client_id)){
	require_once ('includes/foursquare.php');
}

function hm_time_options($key = null, $format = null){
	$options = get_option('hm_time_options');

	if(!empty($key) && array_key_exists($key, $options)){
		return $options[$key];
	} else {
		switch($format){
			case 'string':
				return '';
			case 'boolean':
				return false;
			default:
				return null;
		}
	}

	return $options;

}

require_once ('includes/api-users.php');


