<?php

function hm_time_foursquare_options( $hm_time_set_method_array ) {

	$hm_time_set_method_array['foursquare'] = 'Foursquare';
	return $hm_time_set_method_array;

}

add_filter( 'hm_time_set_method_array', 'hm_time_foursquare_options', 10, 1 );

function hm_time_foursquare_options_input( $user_id, $table_row, $input_text ) {
	// Set Foursquare username input
	$foursquare_user_id = get_user_meta( $user_id, 'hm_time_foursquare_user_id', true );

	if ( empty( $foursquare_user_id ) ) {
		$foursquare_link_template = '<a href="https://foursquare.com/oauth2/authenticate?client_id=%s&response_type=code&redirect_uri=%s" class="button">%s</a>';

		$client_id    = hm_time_options( 'foursquare_client_id' );
		$redirect_uri = home_url( '/wp-json/hm-time/v1/auth' );
		$redirect_uri = add_query_arg( array( 'user_id' => $user_id ), $redirect_uri );
		$link_text    = esc_html__( 'Link to Foursquare', 'hm-time' );

		$foursquare_input = sprintf( $foursquare_link_template, $client_id, $redirect_uri, $link_text );
		$foursquare_desc  = esc_html__( 'Please connect your Foursquare account so we can get sent user push notifications.', 'hm-time' );

	} else {

		$foursquare_input = esc_html__( 'Your profile is already connected to an Foursquare account.', 'hm-time' );
		$foursquare_desc  = sprintf( esc_html__( 'Please go to your %s and disconnect the HM Time app.', 'hm-time' ), '<a href="https://foursquare.com/settings/connections">' . esc_html__( 'Foursquare connections page', 'hm-time' ) . '</a>' );

	}

	printf( $table_row, 'hm_time_foursquare_id', esc_html__( 'Foursquare Account', 'hm-time' ), $foursquare_input, $foursquare_desc );

}

add_action( 'hm_time_add_options', 'hm_time_foursquare_options_input', 10, 3 );
