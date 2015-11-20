<?php

function hm_time_user_profile_fields( $user ) {

	/**
	 * %1$s - field id/name
	 * %2$s - label text
	 * %3$s - field value e.g. $profileuser->id
	 * %4$s - description of field  __('')
	 */
	$table_row  = '	<tr>
						<th><label for="%1$s">%2$s</label></th>
						<td>%3$s
							<p class="description">%4$s</p>
						</td>
					</tr>';
	$input_text = '<input type="text" name="%1$s" id="%1$s" value="%2$s"/>';


	hm_time_timezone_settings( $user->ID, $table_row, $input_text );
	hm_time_location_settings( $user->ID, $table_row, $input_text );
	hm_time_workhours_settings( $user->ID, $table_row, $input_text );
}

add_action( 'show_user_profile', 'hm_time_user_profile_fields' );
add_action( 'edit_user_profile', 'hm_time_user_profile_fields' );

function hm_time_admin_js( $hook ) {
	if ( 'profile.php' != $hook ) {
		return;
	}

	wp_enqueue_script( 'hm_time_script', PLUGIN_URL . 'js/hm_time.js', 'jquery', false, true );

}

add_action( 'admin_enqueue_scripts', 'hm_time_admin_js' );

function hm_time_timezone_settings( $user_id, $table_row, $input_text ) {
	echo '<h3>' . __( 'Time Zone' ) . '</h3>
		  <table class="form-table">';

	$hm_time_set_method_array = hm_time_timezone_options();

	// only need to display set method if there is more than one option
	if ( 1 < count( $hm_time_set_method_array ) ) {
		$hm_time_set_method_input = '<p><label><input type="radio" name="%1$s" value="%2$s" %3$s/> %4$s</label></p>';
		$hm_time_set_method_value = get_user_meta( $user_id, 'hm_time_set_method', true );
		$hm_time_set_method       = '';

		foreach ( $hm_time_set_method_array as $sm_value => $sm_label ) {

			if ( empty ( $hm_time_set_method_value ) ) {
				$hm_time_set_method_value = 'manual';
				$default_set_method       = hm_time_options( 'default_set_method' );

				if ( ! empty ( $default_set_method ) ) {
					$hm_time_set_method_value = $default_set_method;     // set the default value
				}
			}

			$sm_saved = ( $hm_time_set_method_value === $sm_value ? 'checked=checked' : '' );

			$hm_time_set_method .= sprintf( $hm_time_set_method_input, 'hm_time_set_method', $sm_value, $sm_saved, $sm_label );
		}

		printf( $table_row, 'hm_time_set_method', __( 'Set method' ), $hm_time_set_method, __( 'Please select how you want your timezone to be updated' ) );

	} else {

		update_user_meta( $user_id, 'hm_time_set_method', 'manual' );

	}

	do_action( 'hm_time_add_options', $user_id, $table_row, $input_text );

	// Set timezone manually
	$hm_time_manual_value  = get_user_meta( $user_id, 'hm_time_timezone', true );
	$locations             = hm_time_locations();
	$hm_time_manual_inputs = '';
	foreach ( $locations as $lkey => $lvalue ) {
		$hm_time_manual_inputs .= '<optgroup label="' . $lkey . '">';
		if ( is_array( $lvalue ) ) {
			foreach ( $lvalue as $value => $label ) {
				$selected = ( $hm_time_manual_value == $value ? 'selected="selected"' : '' );
				$hm_time_manual_inputs .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
			}
		}
		$hm_time_manual_inputs .= '</optgroup>';
	}

	$hm_time_manual = '<select name="hm_time_timezone">' . $hm_time_manual_inputs . '</select>';
	printf( $table_row, 'hm_time_timezone', __( 'Manual Selection' ), $hm_time_manual, __( 'Please select your timezone' ) );

	echo '</table>';

}

function hm_time_location_settings( $user_id, $table_row, $input_text ) {

	$output = '<h3>' . __( 'Location' ) . '</h3>
		  <table class="form-table">';

	$hm_time_location_value = get_user_meta( $user_id, 'hm_time_location', true );
	$hm_time_location_input = '<input type="text" name="hm_time_location" value="' . $hm_time_location_value . '"/>';

	$output .= sprintf( $table_row, 'hm_time_location', __( 'City/ Country' ), $hm_time_location_input, '' );
	$output .= '</table>';

	echo $output;

}

function hm_time_workhours_settings( $user_id, $table_row, $input_text ) {
	$output = '<h3>' . __( 'Work Hours' ) . '</h3>
		  <p>Shown in 24 hour clock</p>
		  <table id="work_hours" class="form-table">
			<tr><th>Start</th><th>End</th></tr>
		  ';

	$hm_wh_values = get_user_meta( $user_id, 'hm_time_workhours', true );

	$wh_row = '<tr class="tr_clone">
				<td><input type="time" value="%2$s" name="hm_time_workhours[%1$s][start]" data-rownum="%1$s" class="start"></td>
				<td><input type="time" value="%3$s" name="hm_time_workhours[%1$s][end]" data-rownum="%1$s" class="end"></td>
			   </tr>';

	$wh_count = 0;

	if ( is_array( $hm_wh_values ) ) {

		foreach ( $hm_wh_values as $row => $wh_times ) {

			if ( empty ( $wh_times['start'] ) ) {
				continue;
			}

			$output .= sprintf( $wh_row, $wh_count, $wh_times['start'], $wh_times['end'] );
			$wh_count++;

		}

	}

	$output .= sprintf( $wh_row, $wh_count, '', '' );
	$output .= '</table><input type="button" name="add" value="Add New Row" class="tr_clone_add">';

	echo $output;

}

function hm_time_save_profile_fields( $user_id, $timezone = null, $location = null, $workhours = null ) {

	$hm_time_new_set_method = '';
	$hm_time_new_timezone   = '';
	$hm_time_new_location   = '';
	$hm_time_new_workhours  = array();


	// data coming from foursqaure push api
	$set_method = get_user_meta( $user_id, 'hm_time_set_method', true );
	if ( 'foursquare' == $set_method && ! isset( $_POST['hm_time_set_method'] ) ) {

		if ( ! empty( $timezone ) ) {
			$hm_time_new_timezone = $timezone;
		}

		if ( ! empty( $location ) ) {
			$hm_time_new_location = $location;
		}

		if ( ! empty( $workshours ) ) {
			$hm_time_new_workhours = $workhours;
		}

	} else {

		$hm_time_new_set_method = $_POST['hm_time_set_method'];
		$hm_time_new_timezone   = $_POST['hm_time_timezone'];
		$hm_time_new_location   = $_POST['hm_time_location'];
		$hm_time_new_workhours  = $_POST['hm_time_workhours'];

	}
	// Validate and Sanitize

	//Set method validation
	$valid_set_methods = hm_time_timezone_options();

	if ( ! empty ( $hm_time_new_set_method ) && array_key_exists( $hm_time_new_set_method, $valid_set_methods ) ) {
		update_user_meta( $user_id, 'hm_time_set_method', $hm_time_new_set_method );
	}

	// Timezone validation
	$valid_timezones = timezone_identifiers_list();

	if ( ! empty( $hm_time_new_timezone ) && in_array( $hm_time_new_timezone, $valid_timezones ) ) {
		$hm_time_new_timezone = apply_filters( 'hm_time_timezone_filter', $hm_time_new_timezone, $_POST );
		update_user_meta( $user_id, 'hm_time_timezone', $hm_time_new_timezone );
	};

	// Location validation
	if ( ! empty( $hm_time_new_location ) ) {
		$hm_time_new_location = sanitize_text_field( $hm_time_new_location );

		$hm_time_new_location = apply_filters( 'hm_time_location_filter', $hm_time_new_location, $_POST );
		update_user_meta( $user_id, 'hm_time_location', $hm_time_new_location );
	}

	// Work hours validation
	if ( ! empty( $hm_time_new_workhours ) ) {

		$valid_workhours = array_walk_recursive( $hm_time_new_workhours, 'validate_workhours' );

		if ( $valid_workhours ) {
			$hm_time_new_workhours = apply_filters( 'hm_time_workhours_filter', $hm_time_new_workhours, $_POST );
			update_user_meta( $user_id, 'hm_time_workhours', $hm_time_new_workhours );
		}
	}
}

add_action( 'personal_options_update', 'hm_time_save_profile_fields' );
add_action( 'edit_user_profile_update', 'hm_time_save_profile_fields' );

function hm_time_locations() {

	$zones     = timezone_identifiers_list();
	$locations = array();

	foreach ( $zones as $zone ) {
		$zone = explode( '/', $zone ); // 0 => Continent, 1 => City

		// Only use "friendly" continent names
		if ( $zone[0] == 'Africa' || $zone[0] == 'America' || $zone[0] == 'Antarctica' || $zone[0] == 'Arctic' || $zone[0] == 'Asia' || $zone[0] == 'Atlantic' || $zone[0] == 'Australia' || $zone[0] == 'Europe' || $zone[0] == 'Indian' || $zone[0] == 'Pacific' ) {
			if ( isset( $zone[1] ) != '' ) {
				$locations[ $zone[0] ][ $zone[0] . '/' . $zone[1] ] = str_replace( '_', ' ', $zone[1] ); // Creates array(DateTimeZone => 'Friendly name')
			}
		}
	}

	return $locations;
}

function validate_workhours( &$item, $key ) {

	$checked_item = preg_match( '/^[0-2][0-9]:[0-5][0-9]$/', $item );

	if ( ! $checked_item ) {
		$item = '';
	}

	return $item;

}
