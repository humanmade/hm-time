<?php

add_action('admin_menu', 'hm_time_add_page');

function hm_time_add_page(){
	add_options_page('HM Time', 'HM Time', 'manage_options', 'hm_time', 'hm_time_options_page' );
}

function hm_time_options_page(){
	?>
	<div class="wrap">
		<h2>HM Time Settings</h2>
		<form action="options.php" method="post">
			<?php settings_fields('hm_time_options'); ?>
			<?php do_settings_sections('hm_time'); ?>
			<input name="Submit" type="submit" value="Save Changes" />
		</form>
	</div>
	<?php
}

add_action('admin_init', 'hm_time_admin_init');
function hm_time_admin_init(){
	register_setting( 'hm_time_options', 'hm_time_options', 'hm_time_validate_options');

	// Geo Ip Settings
	add_settings_section('hm_time_geioip', 'GeoIP','hm_time_geoip_section_text', 'hm_time');
	add_settings_field('hm_time_geoip_user_id', 'User ID','hm_time_geoip_user_id_input', 'hm_time', 'hm_time_geioip');
	add_settings_field('hm_time_geoip_license_key', 'License Key','hm_time_geoip_license_key_input', 'hm_time', 'hm_time_geioip');

	// Foursquare Settings
	add_settings_section('hm_time_foursquare', 'Foursqaure','hm_time_foursquare_section_text', 'hm_time');
	add_settings_field('hm_time_foursquare_client_id', 'Client ID','hm_time_foursquare_client_id_input', 'hm_time', 'hm_time_foursquare');
	add_settings_field('hm_time_foursquare_client_secret', 'Client Secret','hm_time_foursquare_client_secret_input', 'hm_time', 'hm_time_foursquare');
	add_settings_field('hm_time_foursquare_push_secret', 'Push Secret','hm_time_foursquare_push_secret_input', 'hm_time', 'hm_time_foursquare');
	add_settings_field('hm_time_google_timezone_api_key', 'Google Timezone API Key','hm_time_google_timezone_api_key_input', 'hm_time', 'hm_time_foursquare');

	// Default Definitions
	add_settings_section('hm_time_defaults', 'Defaults','hm_time_defaults_section_text', 'hm_time');
	add_settings_field('hm_time_default_set_method', 'Set Method','hm_time_default_set_method_input', 'hm_time', 'hm_time_defaults');

}

// Geo IP
function hm_time_geoip_section_text(){
	echo '<p>PLease input your Maxmind Omni API key here to use Geo IP with this plugin.</p>';
}

function hm_time_geoip_user_id_input(){

	$geoip_user_id = hm_time_options('geoip_user_id', 'string');

	echo '<input id="geoip_user_id" name="hm_time_options[geoip_user_id]" type="text" value="'.$geoip_user_id.'" />';
}

function hm_time_geoip_license_key_input(){
    $options = get_option('hm_time_options');
	$geoip_license_key = '';
	if(isset($options['geoip_license_key'])){
		$geoip_license_key = $options['geoip_license_key'];
	}

	echo '<input id="geoip_license_key" name="hm_time_options[geoip_license_key]" type="text" value="'.$geoip_license_key.'" />';
}

// Foursqaure
function hm_time_foursquare_section_text(){
	echo '<p>PLease input your Foursqaure app details here to use Foursqaure with this plugin.</p>';
}

function hm_time_foursquare_client_id_input(){
	$options = get_option('hm_time_options');
	$foursquare_client_id = '';
	if(isset($options['foursquare_client_id'])){
		$foursquare_client_id = $options['foursquare_client_id'];
	}

	echo '<input id="foursquare_client_id" name="hm_time_options[foursquare_client_id]" type="text" value="'.$foursquare_client_id.'" />';
}

function hm_time_foursquare_client_secret_input(){
	$options = get_option('hm_time_options');
	$foursquare_client_secret = '';
	if(isset($options['foursquare_client_secret'])){
		$foursquare_client_secret = $options['foursquare_client_secret'];
	}

	echo '<input id="foursquare_client_secret" name="hm_time_options[foursquare_client_secret]" type="text" value="'.$foursquare_client_secret.'" />';
}

function hm_time_foursquare_push_secret_input(){
	$options = get_option('hm_time_options');
	$foursquare_push_secret = '';
	if(isset($options['foursquare_push_secret'])){
		$foursquare_push_secret = $options['foursquare_push_secret'];
	}

	echo '<input id="foursquare_push_secret" name="hm_time_options[foursquare_push_secret]" type="text" value="'.$foursquare_push_secret.'" />';
}

function hm_time_google_timezone_api_key_input(){
	$options = get_option('hm_time_options');
	$google_timezone_api_key = '';
	if(isset($options['google_timezone_api_key'])){
		$google_timezone_api_key = $options['google_timezone_api_key'];
	}

	echo '<input id="google_timezone_api_key" name="hm_time_options[google_timezone_api_key]" type="text" value="'.$google_timezone_api_key.'" />';
}

//Defaults
function hm_time_defaults_section_text(){
//	echo '<p>PLugin Defaults</p>';
	return;
}

function hm_time_default_set_method_input(){
//	echo '<input id="default_method" name="hm_time_options[default_method]" type="radio" value="Geio" />';

	$hm_tz_set_method_array = hm_tz_timezone_options();

	// only need to display set method if there is more than one option
	if(1 < count($hm_tz_set_method_array)){
		$hm_tz_set_method_input = '<p><label><input type="radio" name="%1$s" value="%2$s" %3$s/> %4$s</label></p>';

		$options = get_option('hm_time_options');
		$hm_tz_stored_default_method = 'manual';

		if(isset($options['default_set_method'])){
			$hm_tz_stored_default_method = $options['default_set_method'];
		}

		$hm_tz_set_method = '';

		foreach ( $hm_tz_set_method_array as $sm_value => $sm_label ) {

			$sm_saved = ( $hm_tz_stored_default_method === $sm_value ? 'checked=checked' : '');
			$hm_tz_set_method .= sprintf($hm_tz_set_method_input, 'hm_time_options[default_set_method]', $sm_value , $sm_saved , $sm_label);
		}
		$hm_tz_set_method .= '<span class="description">Please select how you want your timezone to be updated</span>';
		echo  $hm_tz_set_method;
	} else {
		echo '<p>Only method currently is <strong>Manual</strong>. Please insert Foursqaure client ID or GeoIP user ID to have more options.</p>';
	}
}


// Validate user input
function hm_time_validate_options($input){
	$valid = array();
	// validation for each input
	$valid = $input;

	// turn it into a validation hook?

	if($valid['geoip_user_id'] != $input['geoip_user_id']){
		add_settings_error('hm_time_text_string', 'hm_time_texterror', 'Incorrect value entered!', 'error' );
	}
	return $valid;
}