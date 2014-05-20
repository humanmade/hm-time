<?php

// Ensure that its being called from WordPress
if(!defined('WP_UNINSTALL_PLUGIN')){
	exit();
}

// Delete any settings or options saved into the DB

delete_option('hm_time_options');