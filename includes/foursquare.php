<?php

// Set Foursquare username input
$hm_tz_foursquare_value = get_user_meta($user_id, 'hm_tz_foursquare_id', true);
$hm_tz_foursquare = sprintf($input_text, 'hm_tz_foursquare_id', $hm_tz_foursquare_value);
printf($table_row, 'hm_tz_foursquare_id', __('Foursquare Account'), $hm_tz_foursquare, __('Please enter your foursquare username'));