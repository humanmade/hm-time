<?php

add_action( 'show_user_profile', 'hm_time_user_profile_fields' );
add_action( 'edit_user_profile', 'hm_time_user_profile_fields' );

function hm_time_user_profile_fields(){
	global $user_id;
	$user_id = (int) $user_id;

	echo '<h3>'.__('Time Zone') .'</h3>
		  <table class="form-table">';

	/**
	 * %1$s - field id/name
	 * %2$s - label text
	 * %3$s - field value e.g. $profileuser->id
	 * %4$s - description of field  __('')
	 */
	$table_row = '	<tr>
						<th><label for="%1$s">%2$s</label></th>
						<td>%3$s
							<span class="description">%4$s</span>
						</td>
					</tr>';
	$input_text = '<input type="text" name="%1$s" id="%1$s" value="%2$s"/>';

	// Set Timezone Entry Method Fields
	$hm_tz_set_method_input = '<p><label><input type="radio" name="%1$s" value="%2$s" %3$s/> %4$s</label></p>';
	$hm_tz_set_method_value = get_user_meta($user_id, 'hm_tz_set_method', true);
	$hm_tz_set_method_array = array(
		'geoip' 	 => 'Geo IP',
		'foursquare' => 'Foursquare',
		'manual'	 => 'Manual'
	);

	$hm_tz_set_method = '';
	foreach($hm_tz_set_method_array as $sm_value => $sm_label){

		if(empty($hm_tz_set_method_value)){
			//TODO: turn into a GUI option
			$hm_tz_set_method_value = 'geoip';     // set the default value
		}

		$sm_saved = ( $hm_tz_set_method_value === $sm_value ? 'checked=checked' : '');

		$hm_tz_set_method .= sprintf($hm_tz_set_method_input, 'hm_tz_set_method', $sm_value , $sm_saved , $sm_label);
	}

	printf($table_row, 'hm_tz_set_method', __('Timezone - Set method'), $hm_tz_set_method , __('Please select how you want your timezone to be updated'));

	// Set Foursquare username input
	$hm_tz_foursquare_value = get_user_meta($user_id, 'hm_tz_foursquare_id', true);
	$hm_tz_foursquare = sprintf($input_text, 'hm_tz_foursquare_id', $hm_tz_foursquare_value);
	printf($table_row, 'hm_tz_foursquare_id', __('Foursquare Account'), $hm_tz_foursquare, __('Please enter your foursquare username'));

	// Set timezone manually
	$hm_tz_manual_value = get_user_meta($user_id, 'hm_tz_timezone', true);
	$locations = tz_locations();
	$hm_tz_manual_inputs = '';
	foreach($locations as $lkey => $lvalue){
		$hm_tz_manual_inputs .= '<optgroup label="'.$lkey.'">';
		if(is_array($lvalue)){
			foreach($lvalue as $value => $label){
				$selected = ($hm_tz_manual_value == $value ? 'selected="selected"' : '');

				$hm_tz_manual_inputs .= '<option value="'.$value.'" '.$selected.'>'.$label.'</option>';
			}
		}
		$hm_tz_manual_inputs .= '</optgroup>';
	}

	$hm_tz_manual = '<select name="hm_tz_timezone">'.$hm_tz_manual_inputs.'</select>';
	printf($table_row, 'hm_tz_timezone', __('Manual Selection'), $hm_tz_manual, __('Please select your timezone'));


	echo '</table>';
}

add_action( 'personal_options_update', 'hm_time_save_profile_fields' );
add_action( 'edit_user_profile_update', 'hm_time_save_profile_fields' );

function hm_time_save_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }

	update_user_meta( $user_id, 'hm_tz_foursquare_id', $_POST['hm_tz_foursquare_id'] );
	update_user_meta( $user_id, 'hm_tz_set_method', $_POST['hm_tz_set_method'] );

	if ( 'geoip' == $POST['hm_tz_set_method'] ) {
		$hm_tz_new_timezone = tz_ip_lookup('87.81.222.178'); // Test IP address
	}

	if(isset($_POST['hm_tz_timezone']) && 'manual' == $_POST['hm_tz_set_method']){
		$hm_tz_new_timezone = $_POST['hm_tz_timezone'];
	}
	update_user_meta( $user_id, 'hm_tz_timezone', $hm_tz_new_timezone );
}


function tz_locations(){
	$zones = timezone_identifiers_list();
	$locations = array();
	foreach ($zones as $zone)
	{
		$zone = explode('/', $zone); // 0 => Continent, 1 => City

		// Only use "friendly" continent names
		if ($zone[0] == 'Africa' || $zone[0] == 'America' || $zone[0] == 'Antarctica' || $zone[0] == 'Arctic' || $zone[0] == 'Asia' || $zone[0] == 'Atlantic' || $zone[0] == 'Australia' || $zone[0] == 'Europe' || $zone[0] == 'Indian' || $zone[0] == 'Pacific')
		{
			if (isset($zone[1]) != '')
			{
				$locations[$zone[0]][$zone[0]. '/' . $zone[1]] = str_replace('_', ' ', $zone[1]); // Creates array(DateTimeZone => 'Friendly name')
			}
		}
	}
	return $locations;
}

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