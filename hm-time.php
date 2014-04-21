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
register_activation_hook(__FILE__, 'hm_time_install');
register_deactivation_hook(__FILE__, 'hm_time_uninstall');

function hm_time_install(){
	$hm_tz_options = array(
		'default_set_method' => 'manual';
	);

	update_option('hm_tz_options', $hm_tz_options);
}

function hm_time_uninstall(){

}

include('includes/user-profile-fields.php');