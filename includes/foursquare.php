<?php

add_filter('hm_tz_set_method_array', 'hm_tz_foursqaure_options', 10, 1 );

function hm_tz_foursqaure_options($hm_tz_set_method_array){
	$hm_tz_set_method_array['foursquare'] = 'Foursquare';
	return $hm_tz_set_method_array;
}

add_action('hm_tz_add_options', 'hm_tz_foursqaure_options_input', 10, 3);

function hm_tz_foursqaure_options_input($user_id, $table_row, $input_text ){
// Set Foursquare username input
	$hm_tz_foursquare_value = get_user_meta($user_id, 'hm_tz_foursquare_id', true);
	$hm_tz_foursquare_link = '<a href="https://foursquare.com/oauth2/authenticate?client_id=%s&response_type=code&redirect_uri=%s" class="button">Link to Foursqaure</a>';

	$client_id = hm_time_options('foursquare_client_id');
	$redirect_uri = "https://compassionate-frog-8882.vagrantshare.com/foursquare";

	$hm_tz_foursquare = sprintf($hm_tz_foursquare_link, $client_id, $redirect_uri);

	printf($table_row, 'hm_tz_foursquare_id', __('Foursquare Account'), $hm_tz_foursquare, __('Please connect your foursqaure account so we can get sent user push notifications.'));

}



// save
//update_user_meta( $user_id, 'hm_tz_foursquare_id', $_POST['hm_tz_foursquare_id'] );
