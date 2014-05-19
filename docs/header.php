<?php
/**
 * Worse possible thing to do but I did it. ( and is why i rather you waited)
 * This file needs to be copied and pasted into the top of header.php  its for testing
 * the foursquare user push api and foursquare connection.
 *
 * This should all go into the WP API extention im currently building.
 *
 * @param $meta_key
 * @param $meta_value
 * @return mixed
 */

// function by http://tommcfarlin.com/get-user-by-meta-data/
function get_user_by_meta_data( $meta_key, $meta_value ) {

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


$user_id = get_current_user_id();
$user_id = 1   ;   // reassign as logging via vagrant share sends me back to the local url


//foursquare connecting to logged in user.
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

/**
* triggered when foursquare user push api hits the site.
*/
if(isset($_POST['checkin']) && isset($_POST['secret'])){
$options = get_option('hm_time_options');
$push_secret = $options['foursquare_push_secret'];
if($_POST['secret'] != $push_secret){
// should really return a fail message.
exit;
}

//find user who has the $_POST[user] details and make sure you are storing to the correct user
$foursquare_user = json_decode($_POST['user']);
$foursquare_user_id = $foursquare_user->id;
$user = get_user_by_meta_data('hm_time_foursquare_user_id',$foursquare_user_id );
$user_id = $user->id;

// used to store the post data so that its possible for me to review what data 4sq was returning
//	update_user_meta($user_id, 'foursquare_push_data', $_POST['checkin']);
//	$foursquare_push_data = get_user_meta($user_id, 'foursquare_push_data', true);
//	$foursquare_push_data_decoded = json_decode($foursquare_push_data);
$foursquare_push_data_decoded = json_decode($_POST['checkin']);
$venue = $foursquare_push_data_decoded->venue;

//convert venue lat lng to timezone id
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
hm_time_save_profile_fields($user_id, $timezone, $location);

}