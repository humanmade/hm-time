<?php

add_action ( 'admin_menu', 'hm_time_add_page' );

function hm_time_add_page () {

	$hm_time_settings = new HM_Time_Settings();
	add_options_page ( 'HM Time', 'HM Time', 'manage_options', 'hm_time', array ( $hm_time_settings, 'options_page' ) );
	add_action ( 'admin_init', array ( $hm_time_settings, 'admin_init' ) );
}

class HM_Time_Settings{

	public function options_page () {
		?>
		<div class="wrap">
			<h2>HM Time Settings</h2>
			<form action="options.php" method="post">
				<?php settings_fields('hm_time_options'); ?>
				<?php do_settings_sections('hm_time'); ?>
				<?php submit_button( 'Save Changes', 'primary', 'submit', false ); ?>
			</form>
		</div>
	<?php
	}

	public function admin_init () {

		register_setting ( 'hm_time_options', 'hm_time_options', array( $this, 'validate_options' ) );

		// Geo Ip Settings
		add_settings_section ( 'hm_time_geioip', 'GeoIP', array( $this, 'geoip_section_text' ), 'hm_time' );
		add_settings_field ( 'hm_time_geoip_user_id', 'User ID', array( $this, 'geoip_user_id_input'), 'hm_time', 'hm_time_geioip' );
		add_settings_field ( 'hm_time_geoip_license_key', 'License Key', array( $this, 'geoip_license_key_input'), 'hm_time', 'hm_time_geioip' );

		// Foursquare Settings
		add_settings_section ( 'hm_time_foursquare', 'Foursquare', array( $this, 'foursquare_section_text' ), 'hm_time' );
		add_settings_field ( 'hm_time_foursquare_client_id', 'Client ID', array( $this, 'foursquare_client_id_input' ), 'hm_time', 'hm_time_foursquare' );
		add_settings_field ( 'hm_time_foursquare_client_secret', 'Client Secret', array( $this, 'foursquare_client_secret_input' ), 'hm_time', 'hm_time_foursquare' );
		add_settings_field ( 'hm_time_foursquare_redirect_uri', 'Redirect URI', array( $this, 'foursquare_redirect_uri_input' ), 'hm_time', 'hm_time_foursquare' );
		add_settings_field ( 'hm_time_foursquare_push_secret', 'Push Secret', array( $this, 'foursquare_push_secret_input' ), 'hm_time', 'hm_time_foursquare' );
		add_settings_field ( 'hm_time_foursquare_push_url', 'Push URL', array( $this, 'foursquare_push_url_input' ), 'hm_time', 'hm_time_foursquare' );
		add_settings_field ( 'hm_time_foursquare_push_version', 'Push Version', array( $this, 'foursquare_push_version_input' ), 'hm_time', 'hm_time_foursquare' );
		add_settings_field ( 'hm_time_google_timezone_api_key', 'Google Timezone API Key', array( $this, 'google_timezone_api_key_input' ), 'hm_time', 'hm_time_foursquare' );

		// Default Definitions
		add_settings_section ( 'hm_time_defaults', 'Defaults', array( $this, 'defaults_section_text' ), 'hm_time' );
		add_settings_field ( 'hm_time_default_set_method', 'Set Method', array( $this, 'default_set_method_input' ), 'hm_time', 'hm_time_defaults' );

	}

	// Geo IP
	public function geoip_section_text () {

		echo '<p>Please input your Maxmind Omni API details to use Geo IP with this plugin.</p>';

	}

	public function geoip_user_id_input () {

		$geoip_user_id = hm_time_options ( 'geoip_user_id', 'string' );
		echo '<input id="geoip_user_id" name="hm_time_options[geoip_user_id]" type="text" value="' . $geoip_user_id . '" />';

	}

	public function geoip_license_key_input () {

		$geoip_license_key = hm_time_options ( 'geoip_license_key', 'string' );
		echo '<input id="geoip_license_key" name="hm_time_options[geoip_license_key]" type="text" value="' . $geoip_license_key . '" />';

	}

	// Foursquare
	public function foursquare_section_text () {

		echo '<p>Please input your Foursquare app details below to enable Foursquare with this plugin.</p>';

	}

	public function foursquare_client_id_input () {

		$foursquare_client_id = hm_time_options ( 'foursquare_client_id' );
		echo '<input class="widefat" id="foursquare_client_id" name="hm_time_options[foursquare_client_id]" type="text" value="' . $foursquare_client_id . '" />';

	}

	public function foursquare_client_secret_input () {

		$foursquare_client_secret = hm_time_options ( 'foursquare_client_secret' );
		echo '<input class="widefat" id="foursquare_client_secret" name="hm_time_options[foursquare_client_secret]" type="text" value="' . $foursquare_client_secret . '" />';

	}

	public function foursquare_redirect_uri_input () {

		$foursquare_redirect_uri = hm_time_options ( 'foursquare_redirect_uri' );
		echo '<input readonly class="widefat" id="foursquare_redirect_uri" name="hm_time_options[foursquare_redirect_uri]" type="text" value="' . $foursquare_redirect_uri . '" />';

	}

	public function foursquare_push_secret_input () {

		$foursquare_push_secret = hm_time_options ( 'foursquare_push_secret' );
		echo '<input class="widefat" id="foursquare_push_secret" name="hm_time_options[foursquare_push_secret]" type="text" value="' . $foursquare_push_secret . '" />';

	}

	public function foursquare_push_url_input () {

		$foursquare_push_url = hm_time_options ( 'foursquare_push_url' );
		echo '<input readonly class="widefat" id="foursquare_push_url" name="hm_time_options[foursquare_push_url]" type="text" value="' . $foursquare_push_url . '" />';

	}

	public function foursquare_push_version_input () {

		$foursquare_push_version = hm_time_options ( 'foursquare_push_version' );
		echo '<input class="widefat" id="foursquare_push_version" name="hm_time_options[foursquare_push_version]" type="text" value="' . $foursquare_push_version . '" />';

	}

	public function google_timezone_api_key_input () {

		$google_timezone_api_key = hm_time_options ( 'google_timezone_api_key' );
		echo '<input class="widefat" id="google_timezone_api_key" name="hm_time_options[google_timezone_api_key]" type="text" value="' . $google_timezone_api_key . '" />';

	}

	//Defaults
	public function defaults_section_text () {

		return;

	}

	public function default_set_method_input () {

		$set_method_array = hm_time_timezone_options();
		$set_method = '';

		// only need to display set method if there is more than one option
		if( 1 < count ( $set_method_array ) ) {

			$set_method_input = '<p><label><input type="radio" name="%1$s" value="%2$s" %3$s/> %4$s</label></p>';
			$global_default_method = hm_time_options ( 'default_set_method' );
			$stored_default_method = 'manual';


			if( !empty ( $global_default_method ) ) {
				$stored_default_method = $global_default_method;
			}

			foreach ( $set_method_array as $sm_value => $sm_label ) {

				$sm_saved = ( $stored_default_method === $sm_value ? 'checked=checked' : '' );
				$set_method .= sprintf ( $set_method_input, 'hm_time_options[default_set_method]', $sm_value, $sm_saved, $sm_label );

			}

			$set_method .= '<span class="description">Please select how you want your timezone to be updated</span>';

		} else {

			$set_method = '<p>Only method currently is <strong>Manual</strong>. Please insert Foursqaure app or GeoIP api details to have more options.</p>';

		}

		echo $set_method;

	}


	/*
	 *  Validate user input
	 */
	public function validate_options ( $input ) {
		$valid = array();
		// validation for each input
		$valid = $input;

		// turn it into a validation hook?

		if( $valid['geoip_user_id'] != $input['geoip_user_id'] ) {
			add_settings_error ( 'hm_time_text_string', 'hm_time_texterror', 'Incorrect value entered!', 'error' );
		}

		return $valid;
	}

}