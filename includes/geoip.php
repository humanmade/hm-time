<?php
use GeoIp2\WebService\Client;
add_filter('hm_tz_set_method_array', 'hm_tz_geoip_options', 10, 1 );

function hm_tz_geoip_options($hm_tz_set_method_array){
	$hm_tz_set_method_array['geoip'] = 'Geo IP';
//	var_dump($hm_tz_set_method_array);
	return $hm_tz_set_method_array;
}

add_filter( 'hm_tz_timezone_filter', 'hm_tz_geoip_set_timezone', 10, 2 );

function hm_tz_geoip_set_timezone($user_id, $posted_data){
	var_dump($posted_data)         ;
	if ( 'geoip' == $posted_data['hm_tz_set_method'] ) {
		$data = hm_tz_geoip_lookup('2.101.84.98');
		$hm_tz_new_timezone = $data->location->timeZone   ;
	}

	var_dump($hm_tz_new_timezone)        ;
	return $hm_tz_new_timezone;
}

add_filter( 'hm_tz_pre_save_options', 'hm_tz_geoip_set_location', 10, 1 );

function hm_tz_geoip_set_location($user_id){
	if ( 'geoip' == $POST['hm_tz_set_method'] ) {
		$data = hm_tz_geoip_lookup();
		$hm_tz_new_location = $data->city->names['en']   ;
	}
	return $hm_tz_new_location;
}

function hm_tz_geoip_lookup($hostname = null){

	if(empty($hostname)){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$hostname = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //to check ip is pass from proxy
			$hostname = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$hostname = $_SERVER['REMOTE_ADDR'];
		}
	}


	$maxmind_user_id = (int)88817;
	$maxmind_license_key = 'mlsiaHuzCBYF';
	$client = new Client($maxmind_user_id, $maxmind_license_key);

	$record = $client->omni($hostname);

//	return $record->location->timeZone;
	return $record;

//	$country_code = geoip_country_code_by_name($hostname);
//	$region_code = geoip_region_by_name($hostname);
//
//	$timezone = geoip_time_zone_by_country_and_region($country_code, $region_code);
//
//	if(empty($timezone)){
//		if(empty($region_code)){
//			// put in proper error codes
//			return 'No Region Code available';
//		}
//	}
//	return $timezone;
}