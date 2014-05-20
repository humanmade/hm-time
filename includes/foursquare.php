<?php



function hm_time_foursqaure_options( $hm_time_set_method_array ){

	$hm_time_set_method_array['foursquare'] = 'Foursquare';
	return $hm_time_set_method_array;

}
add_filter( 'hm_time_set_method_array', 'hm_time_foursqaure_options', 10, 1 );

function hm_time_foursqaure_options_input( $user_id, $table_row, $input_text ){
	// Set Foursquare username input
	$foursquare_user_id = get_user_meta( $user_id, 'hm_time_foursquare_user_id', true );

	if( empty( $foursquare_user_id ) ){
		$foursquare_link_template = '<a href="https://foursquare.com/oauth2/authenticate?client_id=%s&response_type=code&redirect_uri=%s" class="button">Link to Foursqaure</a>';

		$client_id = hm_time_options( 'foursquare_client_id' );
		$redirect_uri = hm_time_options( 'foursquare_redirect_uri' );

		$foursquare_input = sprintf( $foursquare_link_template, $client_id, $redirect_uri );
		$foursquare_desc  = __('Please connect your foursqaure account so we can get sent user push notifications.');

	} else {

		$foursquare_input = __('Your profile is already connected to an Foursquare account.');
		$foursquare_desc  = __('Please go to <a href="https://foursquare.com/settings/connections">Foursquare settings page</a> and disconnect the HM Time app.');

	}

	printf( $table_row, 'hm_time_foursquare_id', __('Foursquare Account'), $foursquare_input, $foursquare_desc );

}
add_action('hm_time_add_options', 'hm_time_foursqaure_options_input', 10, 3);