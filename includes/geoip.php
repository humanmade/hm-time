<?php

if(empty($hm_tz_set_method_value)){
	//TODO: turn into a GUI option
	$hm_tz_set_method_value = 'geoip';     // set the default value
}
$hm_tz_set_method_array = array(
	'geoip' 	 => 'Geo IP',
	'foursquare' => 'Foursquare',
	'manual'	 => 'Manual'
);


function tz_ip_lookup($hostname = null){

	if(empty($hostname)){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$hostname = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //to check ip is pass from proxy
			$hostname = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$hostname = $_SERVER['REMOTE_ADDR'];
		}
	}

	if(function_exisit('geoip_country_code_by_name')){
		//	$country_code = geoip_country_code_by_name($hostname);
		//	$region_code = geoip_region_by_name($hostname);
		//
		//	$timezone = geoip_time_zone_by_country_and_region($country_code, $region_code);
	} else {
		$url = 'https://freegeoip.net/json/'.$hostname;
		$data = json_decode(file_get_contents($url));
		echo '<pre>';
		var_dump($data);
		echo '</pre>';

		// need  geoip installing on the server.  gotta be a better way.
	}


	return $hostname;
}