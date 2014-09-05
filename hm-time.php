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

define ( 'PLUGIN_URL', plugin_dir_url ( __FILE__ ) );

register_activation_hook ( __FILE__, 'hm_time_install' );
register_deactivation_hook ( __FILE__, 'hm_time_uninstall' );

function hm_time_install (){
	$hm_time_options = array (
		'default_set_method' 		=> 'manual',
		'geoip_user_id' 			 => '',
		'geoip_license_key'			=> '',
		'foursquare_client_id'		=> '',
		'foursquare_client_secret' 	=> '',
		'foursquare_redirect_uri' 	=> '',
		'foursquare_push_secret'	=> '',
		'foursquare_push_url'		=> '',
		'foursquare_push_version'	=> '',
		'google_timezone_api_key' 	=> ''
	);

	$hm_time_options = apply_filters ( 'add_hm_time_options', $hm_time_options );

	update_option ( 'hm_time_options', $hm_time_options );
}

function hm_time_uninstall (){

}

require_once ( 'includes/settings.php' );
require_once ( 'includes/user-profile-fields.php' );
require_once ( 'includes/api-users.php' );

$options = hm_time_options();
$geoip_user_id = $options['geoip_user_id'];
$geoip_license_key = $options['geoip_license_key'];

if ( ! empty ( $geoip_user_id ) && ! empty ( $geoip_license_key ) ){
	require_once ( 'vendor/autoload.php' );
	require_once ( 'includes/geoip.php' );
}

$foursquare = array ();
$foursquare['foursquare_client_id'] 	= $options['foursquare_client_id'];
$foursquare['foursquare_client_secret'] = $options['foursquare_client_secret'];
$foursquare['foursquare_redirect_uri'] 	= $options['foursquare_redirect_uri'];
$foursquare['foursquare_push_secret'] 	= $options['foursquare_push_secret'];
$foursquare['foursquare_push_url'] 		= $options['foursquare_push_url'];
$foursquare['foursquare_push_version'] 	= $options['foursquare_push_version'];
$foursquare['google_timezone_api_key'] 	= $options['google_timezone_api_key'];

foreach ( $foursquare as $key => $value ){
	$foursquare[$key] = trim ( $value );
}

if( !in_array ( '', $foursquare ) ){

	require_once ('includes/foursquare.php');
	require_once ('includes/api-foursquare.php');

}

function hm_time_options ( $key = null, $format = null ){

	$options = get_option ( 'hm_time_options' );

	if ( !empty ( $key ) && array_key_exists ( $key, $options ) ){

		return $options[$key];

	}

	switch ( $format ){
		case 'string':
			return '';
			break;
		case 'boolean':
			return false;
			break;
		default:
			return $options;
			break;
	}

}

function hm_time_timezone_options () {

	$hm_time_set_method_array = array (
		'manual' => 'Manual'
	);

	$hm_time_set_method_array = apply_filters( 'hm_time_set_method_array', $hm_time_set_method_array );

	return $hm_time_set_method_array;
}

