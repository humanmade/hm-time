<?php

add_filter('hm_tz_set_method_array', 'hm_tz_geoip_options', 10, 1 );

function hm_tz_geoip_options($hm_tz_set_method_array){
	$hm_tz_set_method_array['geoip'] = 'Geo IP';
}

add_filter( 'hm_tz_save_options', 'hm_tz_geoip_save', 10, 1 );

function hm_tz_geoip_save($user_id, $_POST){
	if ( 'geoip' == $POST['hm_tz_set_method'] ) {
		$hm_tz_new_timezone = tz_ip_lookup('87.81.222.178'); // Test IP address
	}

	return $hm_tz_new_timezone;
}

function hm_tz_geoip_lookup($user_id = null, $hostname = null){

	if(empty($user_id)){
		$user_id = get_current_user_id();
	}

	if(empty($hostname)){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$hostname = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //to check ip is pass from proxy
			$hostname = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$hostname = $_SERVER['REMOTE_ADDR'];
		}
	}

	$country_code = geoip_country_code_by_name($hostname);
	$region_code = geoip_region_by_name($hostname);

	$timezone = geoip_time_zone_by_country_and_region($country_code, $region_code);

	return $timezone;
}