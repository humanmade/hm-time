<?php

function hm_time_api_user_init() {

	global $hm_time_api_users;

	$hm_time_api_users = new HM_Time_API_Users();
	add_filter( 'json_endpoints', array( $hm_time_api_users, 'register_routes' ) );

}
add_action( 'wp_json_server_before_serve', 'hm_time_api_user_init' );

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
					$meta_key = 'hm_time_timezone';
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

			$data = array();
			
			$data['user_id']     = $user->ID;
			$data['name']        = $user->display_name;
			$data['email']       = $user->user_email;
			$data['timezone']    = get_user_meta($user->ID, 'hm_time_timezone', true) ? get_user_meta($user->ID, 'hm_time_timezone', true) : 'UTC';
			$data['location']    = get_user_meta($user->ID, 'hm_time_location', true);
			$data['workhours']   = get_user_meta($user->ID, 'hm_time_workhours', true) ? get_user_meta($user->ID, 'hm_time_workhours', true) : array();
			$data['curr_time']   = '';
			$data['curr_offset'] = '';
			$data['avatar']      = get_avatar_url( $user->ID );

			$dateTimeObj = new DateTime('NOW');

			$dateTimeObj->setTimezone(new DateTimeZone($data['timezone']));
			$data['curr_time'] = $dateTimeObj->format('Y-m-d H:i:s');
			$offset_in_secs = $dateTimeObj->getOffset();
			$offset_in_hours = $offset_in_secs / 60 / 60 ;
			$data['curr_offset'] = $offset_in_hours;

			foreach ( $data['workhours'] as $num => $hours) {

				$dateTimeObj = new DateTime( $hours['start'], new DateTimeZone( $data['timezone'] ) );
				$dateTimeObj->setTimezone( new DateTimeZone( 'UTC' ) );
				$data['workhours_utc'][$num]['start'] = $dateTimeObj->format('H:i');

				$dateTimeObj = new DateTime( $hours['end'], new DateTimeZone( $data['timezone'] ) );
				$dateTimeObj->setTimezone( new DateTimeZone( 'UTC' ) );
				$data['workhours_utc'][$num]['end'] = $dateTimeObj->format('H:i');
			}

			$response[] = $data;
		}
		return $response;
	}

}
