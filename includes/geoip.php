<?php
use GeoIp2\WebService\Client;
add_filter('hm_tz_set_method_array', 'hm_tz_geoip_options', 10, 1 );

function hm_tz_geoip_options($hm_tz_set_method_array){
	$hm_tz_set_method_array['geoip'] = 'Geo IP';
	return $hm_tz_set_method_array;
}

add_filter( 'hm_tz_timezone_filter', 'hm_tz_geoip_set_timezone', 10, 3 );

function hm_tz_geoip_set_timezone($user_id, $timezone, $posted_data){
	if ( 'geoip' != $posted_data['hm_tz_set_method'] ) {
		return $timezone;
	}

	return hm_tz_geoip_lookup_timezone('2.101.84.98');
}

add_filter( 'hm_tz_pre_save_options', 'hm_tz_geoip_set_location', 10, 1 );

function hm_tz_geoip_set_location($user_id){
	if ( 'geoip' == $POST['hm_tz_set_method'] ) {
		$data = hm_tz_geoip_lookup();
		$hm_tz_new_location = $data->city->names['en']   ;
	}
	return $hm_tz_new_location;
}                            https://github.com/humanmade/Salty-WordPress.git

function 	hm_tz_geoip_lookup($hostname = null){

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

	return $record;

}

function hm_tz_geoip_lookup_timezone($hostname = null){

	$data = hm_tz_geoip_lookup($hostname);
	$timezone = $data->location->timeZone;

	return $timezone;
}