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

			//should make this a filter?
			'/hm-time/4sq'             => array(
				array( array( $this, 'get_code' ), WP_JSON_Server::READABLE ),
				array( array( $this, 'new_push' ),  WP_JSON_Server::CREATABLE | WP_JSON_Server::ACCEPT_JSON ),
			),
		);
		return array_merge( $routes, $user_routes );
	}

	/**
	 * Retrieve posts.
	 *
	 * @since 3.4.0
	 *
	 * The optional $filter parameter modifies the query used to retrieve posts.
	 * Accepted keys are 'post_type', 'post_status', 'number', 'offset',
	 * 'orderby', and 'order'.
	 *
	 * The optional $fields parameter specifies what fields will be included
	 * in the response array.
	 *
	 * @uses wp_get_recent_posts()
	 * @see WP_JSON_Posts::get_post() for more on $fields
	 * @see get_posts() for more on $filter values
	 *
	 * @param array $filter Parameters to pass through to `WP_Query`
	 * @param string $context
	 * @param string|array $type Post type slug, or array of slugs
	 * @param int $page Page number (1-indexed)
	 * @return stdClass[] Collection of Post entities
	 */
	public function get_users( $filter = array(), $context = 'view', $type = 'post', $page = 1 ) {

		$response = 'hi jen';
		return $response;
	}

	/**
	 * Callback for when a user links their profile to their foursquare account
	 */
	public function get_code( $data ) {
		$user_id = get_current_user_id();
		$user_id = 1   ;

		if(isset($_GET['code'])){
			$code = $_GET[code];
			$options = get_option('hm_time_options');
			$client_id = $options['foursquare_client_id'];
			$client_secret = $options['foursquare_client_secret'];
			$registered_redirect_uri = 'https://compassionate-frog-8882.vagrantshare.com/foursquare';

			$access_token_url =  'https://foursquare.com/oauth2/access_token?client_id='.$client_id.'&client_secret='.$client_secret.'&grant_type=authorization_code&redirect_uri='.$registered_redirect_uri.'&code='.$code;

			$access_token_json = wp_remote_get($access_token_url);
			$access_token_decoded = json_decode($access_token_json['body']);
			$access_token = $access_token_decoded->access_token;

			if(is_string($access_token)){
				if(is_int($user_id) && 0 != $user_id){
					update_user_meta($user_id, 'hm_time_foursqaure_access_token', $access_token);
				}
			}

			// get user details
			$user_details_url = 'https://api.foursquare.com/v2/users/self?oauth_token='.$access_token.'&v=20140519';
			$user_details_json = wp_remote_get($user_details_url);
			$user_details_decoded = json_decode($user_details_json['body']);

			// store foursquare user id
			update_user_meta($user_id, 'hm_time_foursquare_user_id', $user_details_decoded->response->user->id);
		}
		return $response;
	}

	/**
	 * Recieves and deals with Foursquare
	 */
	public function new_push( $data ) {
		if(isset($_POST['checkin']) && isset($_POST['secret'])){
			$options = get_option('hm_time_options');
			$push_secret = $options['foursquare_push_secret'];
			if($_POST['secret'] != $push_secret){
				// send error back
				exit;
			}

			$foursquare_user = json_decode($_POST['user']);
			$foursquare_user_id = $foursquare_user->id;
			$user = get_user_by_meta_data('hm_time_foursquare_user_id',$foursquare_user_id );
			$user_id = $user->id;
			update_user_meta($user_id, 'foursquare_push_data', $_POST['checkin']);
//	var_dump('</pre>');
		}


		var_dump('<pre>');
		$foursquare_push_data = get_user_meta($user_id, 'foursquare_push_data', true);
		$foursquare_push_data_decoded = json_decode($foursquare_push_data);
		var_dump($foursquare_push_data_decoded->venue);
		$venue = $foursquare_push_data_decoded->venue;

		$options = get_option('hm_time_options');
		$google_tz_api_key = $options['google_timezone_api_key'];
		$venue_lat = $venue->location->lat;
		$venue_lng = $venue->location->lng;
		$timestamp = time();
		$google_tz_api_url = 'https://maps.googleapis.com/maps/api/timezone/json?location='.$venue_lat.','.$venue_lng.'&timestamp='.$timestamp.'&sensor=false&key='.$google_tz_api_key;
		$google_tz_api_response = wp_remote_get($google_tz_api_url);
		$google_tz_api_body = json_decode($google_tz_api_response['body']);
		$timezone = $google_tz_api_body->timeZoneId;
		$location = $venue->location->city . ', ' .$venue->location->country;
		var_dump($location);
		hm_time_save_profile_fields($user_id, $timezone, $location);

		$response = json_ensure_response( $this->get_post( $result ) );
		$response->set_status( 201 );
		$response->header( 'Location', json_url( '/posts/' . $result ) );
		return $response;
	}

	// function by http://tommcfarlin.com/get-user-by-meta-data/
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

}
