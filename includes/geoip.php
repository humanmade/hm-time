<?php
use GeoIp2\WebService\Client;

add_filter('hm_time_set_method_array', 'hm_time_geoip_options', 10, 1 );
function hm_time_geoip_options($hm_time_set_method_array){
	$hm_time_set_method_array['geoip'] = 'Geo IP';
	return $hm_time_set_method_array;
}

add_filter( 'hm_time_timezone_filter', 'hm_time_geoip_set_timezone', 10, 2 );
function hm_time_geoip_set_timezone($data, $posted_data){
	if ( 'geoip' != $posted_data['hm_time_set_method'] ) {
		return $data;
	}

	$found_timezone = hm_time_geoip_lookup_timezone('87.113.98.128');

	if(empty($found_timezone)){
		// need to add in error saying that  geoip is not working because of exceptions. eg reserved IPs.
		return $data;
	}

	return $found_timezone;
}

add_filter( 'hm_time_location_filter', 'hm_time_geoip_set_location', 10, 2 );
function hm_time_geoip_set_location($data, $posted_data){
	if ( 'geoip' != $posted_data['hm_time_set_method'] ) {
		return $data;
	}

	$found_data = hm_time_geoip_lookup('87.113.98.128');

	$city = $found_data->city->names['en'];
	$country = $found_data->country->names['en'];

	$hm_time_new_location = '';

	if(!empty($city)){
		$hm_time_new_location .= $city;
	}

	if(!empty($country)){
		if(!empty($city)){
			$hm_time_new_location .= ', ';
		}
		$hm_time_new_location .= $country;
	}

	if(empty($hm_time_new_location)){
		$hm_time_new_location = $data;
	}

	return $hm_time_new_location;
}

function 	hm_time_geoip_lookup($hostname = null){

	if(empty($hostname)){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$hostname = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //to check ip is pass from proxy
			$hostname = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$hostname = $_SERVER['REMOTE_ADDR'];
		}
	}

	$maxmind_user_id = hm_time_options('geoip_user_id', 'string');
	$maxmind_user_id = (int)$maxmind_user_id;
	$maxmind_license_key = hm_time_options('geoip_license_key');
	$client = new Client($maxmind_user_id, $maxmind_license_key);

	try{
		$record = $client->omni($hostname);
	} catch(GeoIp2\Exception\AddressNotFoundException $e){
		return false;
	}


	return $record;

}

function hm_time_geoip_lookup_timezone($hostname = null){

	$data = hm_time_geoip_lookup($hostname);

	if(empty($data)){
		return null;
	}

	$timezone = $data->location->timeZone;

	return $timezone;
}