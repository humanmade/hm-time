<?php

add_action( 'show_user_profile', 'hm_tz_user_profile_fields' );
add_action( 'edit_user_profile', 'hm_tz_user_profile_fields' );
add_action('admin_enqueue_scripts', 'hm_time_admin_js');

function hm_time_admin_js($hook){
	if( 'profile.php' != $hook ){
		return;
	}

	wp_enqueue_script( 'hm_time_script', PLUGIN_URL . 'js/hm_time.js', 'jquery', false, true );
}


function hm_tz_user_profile_fields(){
	global $user_id;
	$user_id = (int) $user_id;

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

	if(function_exists('hm_tz_geoip_lookup')){
//		$ip = '84.92.84.163';      //
//		$ip = '58.96.33.217';      // ryan
//		$ip = '54.252.87.81';      // theo
//		$ip = '54.229.166.87';      // mgdm
//		$ip = '2.101.84.98';      // matt
//		echo('<pre>');
//		echo '<h3>'.$ip.'</h3>';
//		    $d = hm_tz_geoip_lookup($ip);
//		    $t = $d->location->timeZone   ;
//		    $l = $d;
//			var_dump($l->city->names['en']);
//		echo('</pre>');
	}

	hm_tz_timezone_settings($user_id, $table_row, $input_text);
	hm_tz_location_settings($user_id, $table_row, $input_text);
	hm_tz_workhours_settings($user_id, $table_row, $input_text);
}

function hm_tz_timezone_options(){
	$hm_tz_set_method_array = array(
		'manual' => 'Manual'
	);

	$hm_tz_set_method_array = apply_filters( 'hm_tz_set_method_array', $hm_tz_set_method_array );

	return $hm_tz_set_method_array;
}

function hm_tz_timezone_settings($user_id, $table_row, $input_text){
	echo '<h3>'.__('Time Zone') .'</h3>
		  <table class="form-table">';

	$hm_tz_set_method_array = hm_tz_timezone_options();

	// only need to display set method if there is more than one option
	if(1 < count($hm_tz_set_method_array)){
		$hm_tz_set_method_input = '<p><label><input type="radio" name="%1$s" value="%2$s" %3$s/> %4$s</label></p>';
		$hm_tz_set_method_value = get_user_meta($user_id, 'hm_tz_set_method', true);
		$hm_tz_set_method = '';

		foreach ( $hm_tz_set_method_array as $sm_value => $sm_label ) {

			if( empty( $hm_tz_set_method_value ) ){
				$hm_tz_set_method_value = 'manual';
				$default_set_method = hm_time_options('default_set_method');

				if(!empty($default_set_method)){
					$hm_tz_set_method_value = $default_set_method;     // set the default value
				}
			}

			$sm_saved = ( $hm_tz_set_method_value === $sm_value ? 'checked=checked' : '');

			$hm_tz_set_method .= sprintf($hm_tz_set_method_input, 'hm_tz_set_method', $sm_value , $sm_saved , $sm_label);
		}

		printf($table_row, 'hm_tz_set_method', __('Set method'), $hm_tz_set_method , __('Please select how you want your timezone to be updated'));
	} else {
		update_user_meta( $user_id, 'hm_tz_set_method', 'manual');
	}

	do_action( 'hm_tz_add_options', $user_id );

	// Set timezone manually
	$hm_tz_manual_value = get_user_meta($user_id, 'hm_tz_timezone', true);
	$locations = hm_tz_locations();
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

function hm_tz_location_settings($user_id, $table_row, $input_text){
	echo '<h3>'.__('Location') .'</h3>
		  <table class="form-table">';
	$hm_tz_location_value = get_user_meta($user_id, 'hm_tz_location', true);
	$hm_tz_location_input = '<input type="text" name="hm_tz_location" value="'.$hm_tz_location_value.'"/>';

	printf($table_row, 'hm_tz_location', __('City/ Country'), $hm_tz_location_input , '');
	echo '</table>';
}

function hm_tz_workhours_settings($user_id, $table_row, $input_text){
	echo '<h3>'.__('Work Hours') .'</h3>
		  <p>Shown in 24 hour clock</p>
		  <table id="work_hours" class="form-table">
			<tr><th>Start</th><th>End</th></tr>
		  ';

//	delete_user_meta($user_id, 'hm_tz_workhours');
	$hm_wh_values = get_user_meta($user_id, 'hm_tz_workhours', true);

	$wh_row = '<tr class="tr_clone">
				<td><input type="time" value="%2$s" name="hm_tz_workhours[%1$s][start]" data-rownum="%1$s" class="start"></td>
				<td><input type="time" value="%3$s" name="hm_tz_workhours[%1$s][end]" data-rownum="%1$s" class="end"></td>
			   </tr>';
	$wh_count = 0;
	if(is_array($hm_wh_values)){
		foreach($hm_wh_values as $row => $wh_times){
			if(empty($wh_times['start'])){
				continue;
			}
			printf($wh_row, $wh_count, $wh_times['start'], $wh_times['end']);
			$wh_count++;
		}
	}

	printf($wh_row, $wh_count, '', '');
	echo '</table><input type="button" name="add" value="Add New Row" class="tr_clone_add">';
}


add_action( 'personal_options_update', 'hm_time_save_profile_fields' );
add_action( 'edit_user_profile_update', 'hm_time_save_profile_fields' );

function hm_time_save_profile_fields( $user_id ) {

	$hm_tz_new_set_method = $_POST['hm_tz_set_method'];
	$hm_tz_new_timezone  = $_POST['hm_tz_timezone'];
	$hm_tz_new_location  = $_POST['hm_tz_location'];
	$hm_tz_new_workhours =  $_POST['hm_tz_workhours'];
	// Validate and Sanitize

	//Set method validation
	$valid_set_methods = hm_tz_timezone_options();
	if(!empty($hm_tz_new_set_method) && in_array($hm_tz_new_set_method, $valid_set_methods)){
		update_user_meta( $user_id, 'hm_tz_set_method', $hm_tz_new_set_method );
	}

	// Timezone validation
	$valid_timezones = timezone_identifiers_list();

	if(!empty($hm_tz_new_timezone) && in_array($hm_tz_new_timezone, $valid_timezones)){
		$hm_tz_new_timezone = apply_filters( 'hm_tz_timezone_filter', $hm_tz_new_timezone, $_POST );
		update_user_meta( $user_id, 'hm_tz_timezone', $hm_tz_new_timezone );
	};

	// Location validation
	$hm_tz_new_location = sanitize_text_field($hm_tz_new_location);

	$hm_tz_new_location = apply_filters( 'hm_tz_location_filter', $hm_tz_new_location, $_POST );
	update_user_meta( $user_id, 'hm_tz_location', $hm_tz_new_location );


	// Work hours validation
	$valid_workhours =  array_walk_recursive($hm_tz_new_workhours, 'validate_workhours');
	if($valid_workhours){
		$hm_tz_new_workhours = apply_filters( 'hm_tz_workhours_filter', $hm_tz_new_workhours, $_POST );

		update_user_meta( $user_id, 'hm_tz_workhours', $hm_tz_new_workhours );
	}

}


function hm_tz_locations(){
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

function validate_workhours(&$item, $key){
	$checked_item = preg_match('/^[0-2][0-9]:[0-5][0-9]$/', $item);

	if(!$checked_item){
		$item = '';
	}

	return $item;

}
