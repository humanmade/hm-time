<?php

function hm_time_api_init() {
	global $hm_time_api_users;

	$hm_time_api_users = new HM_Time_API_Users();
	add_filter( 'json_endpoints', array( $hm_time_api_users, 'register_routes' ) );
}
add_action( 'wp_json_server_before_serve', 'hm_time_api_init' );

class HM_Time_API_Users {

	/**
	 * Register the user-related routes
	 *
	 * @param array $routes Existing routes
	 * @return array Modified routes
	 */
	public function register_routes( $routes ) {
		$user_routes = array(
			// Users endpoints
			'/hm-time/users'             => array(
				array( array( $this, 'get_users' ), WP_JSON_Server::READABLE ),
			),

			// should break this into its own class / file
			'/hm-time'             => array(
				array( array( $this, 'get_code' ), WP_JSON_Server::READABLE ),
				array( array( $this, 'new_push' ),  WP_JSON_Server::CREATABLE | WP_JSON_Server::ACCEPT_JSON ),
			),
		);
		return array_merge( $routes, $user_routes );
	}

	/**
	 * Retrieve users time data.
	 */
	public function get_users( $filter = null ) {

		$args = '';

		if($filter){

			switch($filter){
				case 'location':
					$meta_key = 'hm_time_location';
					break;
				case 'timezone':
					$meta_key = 'hm_tz_timezone';
					break;
				case 'workhours':
					$meta_key = 'hm_time_workhours';
					break;
				default:
					break;
			}

			$args['meta_key'] = $meta_key;
		}

		$users  = get_users( $args );
		$response = array();

		foreach($users as $user){
			$data['timezone'] 	= get_user_meta($user->id, 'hm_tz_timezone', true);
			$data['location'] 	= get_user_meta($user->id, 'hm_tz_location', true);
			$data['workhours'] 	= get_user_meta($user->id, 'hm_tz_workhours', true);
			$data['curr_time']	= '';
			$data['curr_offset']	= '';
			$dateTimeObj = new DateTime('NOW');
			if(!empty($data['timezone'])){
				$dateTimeObj->setTimezone(new DateTimeZone($data['timezone']));
				$data['curr_time'] = $dateTimeObj->format('Y-m-d H:i:s');
				$offset_in_secs = $dateTimeObj->getOffset();
				$offset_in_hours = $offset_in_secs / 60 / 60 ;
				$data['curr_offset'] = $offset_in_hours;
			}
			$response[$user->id] = $data;
		}
		return $response;
	}

	/**
	 * Callback for when a user links their profile to their foursquare account
	 */
	public function get_code( $code ) {
		$user_id = get_current_user_id();
		$user_id = 1 ;   //override because using vagrnt share to log in causes the site to  revert back to its local url.

		if ( $code ) {
			$options = get_option( 'hm_time_options' );

			/* Debug Only */
			$options['foursquare_push_version'] = '20140519';
			/* End Debug Only */

			$client_id = $options['foursquare_client_id'];
			$client_secret = $options['foursquare_client_secret'];
			$push_version = $options['foursquare_push_version'];
			$registered_redirect_uri = site_url('/wp-json/hm-time', 'https');
			$registered_redirect_uri = $options['foursquare_request_uri'];      // debug mode

			$access_token_url =  'https://foursquare.com/oauth2/access_token?client_id='.$client_id.'&client_secret='.$client_secret.'&grant_type=authorization_code&redirect_uri='.$registered_redirect_uri.'&code='.$code;

			$access_token_json = wp_remote_get($access_token_url);
			$access_token_decoded = json_decode($access_token_json['body']);
			if($access_token_decoded->error){
				return 'Error with access token'; // Need to send back an error to the user saying that foursquare auth failed.
			}

			$access_token = $access_token_decoded->access_token;
			if(is_string($access_token)){
				if(is_int($user_id) && 0 != $user_id){
					update_user_meta($user_id, 'hm_time_foursquare_access_token', $access_token);
				}
			}

			// get user details
			$user_details_url = 'https://api.foursquare.com/v2/users/self?oauth_token='.$access_token.'&v='.$push_version;
			$user_details_json = wp_remote_get($user_details_url);
			$user_details_decoded = json_decode($user_details_json['body']);

			if($user_details_decoded->error){
				return 'Error with user details'; // Need to send back an error to the user saying that foursquare auth failed.
			}
			// store foursquare user id
			update_user_meta($user_id, 'hm_time_foursquare_user_id', $user_details_decoded->response->user->id);
		}

		$response = json_ensure_response( 'success' );
		$response->set_status( 201 );
		$response->header( 'Location', $registered_redirect_uri );
	}

	/**
	 * Recieves and stores data from Foursquare User Push APIs
	 */
	public function new_push( $checkin, $secret, $user ) {

		if( !isset ( $checkin ) && !isset ( $secret ) ){
			// send error back
			exit;
		}

		$options 		= get_option( 'hm_time_options' );
		$push_secret 	= $options['foursquare_push_secret'];
		$google_tz_api_key = $options['google_timezone_api_key'];

		if ( $secret != $push_secret ){
			// send error back
			exit;
		}

		// fix mapping issue where its unescaping the values.
		$checking = $this->hm_stripslashes($checkin);
		$checkinDecoded = json_decode($checkin);

		$user = $this->hm_stripslashes($user);
		$userDecoded = json_decode($user);

		$wp_user = $this->get_user_by_meta_data ( 'hm_time_foursquare_user_id', $userDecoded->id );

		$venue = $checkinDecoded->venue;
		$venue_lat = $venue->location->lat;
		$venue_lng = $venue->location->lng;

		$timestamp = time();

		$google_tz_api_url = 'https://maps.googleapis.com/maps/api/timezone/json?location='.$venue_lat.','.$venue_lng.'&timestamp='.$timestamp.'&sensor=false&key='.$google_tz_api_key;

		$google_tz_api_response = wp_remote_get ( $google_tz_api_url );
		$google_tz_api_body 	= json_decode ( $google_tz_api_response['body'] );
		$timezone_id 			= $google_tz_api_body->timeZoneId;
		$location 				= $venue->location->city . ', ' .$venue->location->country;

		hm_time_save_profile_fields ( $wp_user->id, $timezone_id, $location );

		$response = json_ensure_response( 'success' );
		$response->set_status( 201 );
		$response->header( 'Location', json_url( '/hm-time/' . $result ) );

		return $response;
	}

	/**
	 * Get user object by meta data
	 *
	 * function by http://tommcfarlin.com/get-user-by-meta-data/
	 *
	 * @param string $meta_key
	 * @param string $meta_value
	 * @return first user object found on success
	 */
	protected function get_user_by_meta_data( $meta_key, $meta_value ) {

		// Query for users based on the meta data
		$user_query = new WP_User_Query(
			array(
				'meta_key'	  =>	$meta_key,
				'meta_value'	=>	$meta_value
			)
		);

		// Get the results from the query, returning the first user
		$users = $user_query->get_results();

		return $users[0];

	} // end get_user_by_meta_data

	/**
	 * Incoming JSON is being escaped too much.
	 * Use function to unescape the json before decoding.
	 *
	 * @param $data
	 * @return string
	 */
	protected function hm_stripslashes($data){
		while ( strpos ( $data, '\\') !== false) {
			$data = stripslashes($data);
		}
		return $data;
	}

}
