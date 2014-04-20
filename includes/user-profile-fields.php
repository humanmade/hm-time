<?php

add_action( 'show_user_profile', 'hm_time_user_profile_fields' );
add_action( 'edit_user_profile', 'hm_time_user_profile_fields' );

function hm_time_user_profile_fields(){
	echo '<h3>'.__('Time Info') .'</h3>
		  <table class="form-table">';

	/**
	 * %1$s - field id/name
	 * %2$s - label text
	 * %3$s - field value e.g. $profileuser->id
	 * %4$s - description of field  _e('')
	 */
	$table_row = '	<tr>
						<th><label for="%1$s">%2$s</label></th>
						<td>%3$s
							<span class="description">%4$s</span>
						</td>
					</tr>';
	$input_text = '<input type="text" name="%1$s" id="%1$s" value="%2$s"/>';

	$hm_tz_set_method_input = '<p><label><input type="radio" name="%1$s" value="%2$s" %3$s/> %4$s</label></p>';
//	$profileuser->hm_tz_set_method
	$hm_tz_set_method = '';
	$hm_tz_set_method .= sprintf($hm_tz_set_method_input, 'hm_tz_set_method', 'geoip','' , 'Geo IP');
	$hm_tz_set_method .= sprintf($hm_tz_set_method_input, 'hm_tz_set_method', 'foursquare','' , 'Foursquare');
	$hm_tz_set_method .= sprintf($hm_tz_set_method_input, 'hm_tz_set_method', 'manual','' , 'Manual');

	printf($table_row, 'hm_tz_set_method', __('Timezone - Set method'), $hm_tz_set_method , __('Please select how you want your timezone to be updated'));

	$hm_tz_foursquare = sprintf($input_text, 'hm_tz_foursquare_id', $profileuser->hm_tz_foursquare_id);
	printf($table_row, 'hm_tz_foursquare_id', __('Foursquare Account'), $hm_tz_foursquare, __('Please enter your foursquare username'));

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
	$hm_tz_manual_inputs = '';
	foreach($locations as $lkey => $lvalue){
		$hm_tz_manual_inputs .= '<optgroup label="'.$lkey.'">';
		if(is_array($lvalue)){
			foreach($lvalue as $value => $label){
				$hm_tz_manual_inputs .= '<option value="'.$value.'">'.$label.'</option>';
			}
		}
		$hm_tz_manual_inputs .= '</optgroup>';
	}

	$hm_tz_manual = '<select name="hm_timezone">'.$hm_tz_manual_inputs.'</select>';
	printf($table_row, 'hm_timezone', __('Manual Selection'), $hm_tz_manual, __('Please select your timezone'));

	echo '</table>';
}

add_action( 'personal_options_update', 'hm_time_save_profile_fields' );
add_action( 'edit_user_profile_update', 'hm_time_save_profile_fields' );

function hm_time_save_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }

	update_user_meta( $user_id, 'address', $_POST['address'] );
	update_user_meta( $user_id, 'city', $_POST['city'] );
	update_user_meta( $user_id, 'province', $_POST['province'] );
	update_user_meta( $user_id, 'postalcode', $_POST['postalcode'] );
}