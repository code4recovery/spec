<?php
/*
Plugin Name: The Events Calendar - Meeting Guide Converter
Plugin URI: https://github.com/meeting-guide/api
Description: Takes AA meeting info from The Events Calendar and formats it for Meeting Guide
Version: 0.0.1
Author: Meeting Guide
Author URI: https://meetingguide.org
License: none
Text Domain: tecmgc
*/

add_action('wp_ajax_tecmgc', 'tecgmc_output');
add_action('wp_ajax_nopriv_tecmgc', 'tecmgc_output');

function tecgmc_output() {

	//define some arrays that we'll need
	$locations = $meetings = array();
	
	$types = array(
		'BB' => 'B',
		'CC' => 'C',
		'D' => 'D',
		'G' => 'G',
		'H' => 'X',
		'M' => 'M',
		'N' => 'B',
		'O' => 'O',
		//PL—Pot Luck,
		'S' => 'SP',
		//SIS—Seniors in Sobriety,
		'T' => 'ST',
		'W' => 'W',
		'Y' => 'Y',
		'SP' => 'S',
	);
	
	//fetch all the venues
	$tribe_venues = get_posts(array(
		'post_type' => 'tribe_venue',
		'numberposts' => -1,
	));
	
	//format the relevant venue information in a lookup array
	foreach ($tribe_venues as $venue) {
		$custom = get_post_meta($venue->ID);
		$locations[$venue->ID] = array(
			'location' => $venue->post_title,
			'location_notes' => trim(strip_tags($venue->post_content)),
			'address' => $custom['_VenueAddress'][0],
			'city' => $custom['_VenueCity'][0],
			'state' => $custom['_VenueState'][0],
			'postal_code' => $custom['_VenueZip'][0],
			'region' => $custom['_VenueCity'][0],
		);
	}
	
	//get the events for the next week (since they recur)
	date_default_timezone_set(get_option('timezone_string'));
	//dd($date_range);
	
	$tribe_events = tribe_get_events(array(
		'post_type' => 'tribe_events',
		'posts_per_page' => -1,
		'start_date' => date('Y-m-d H:i:s', strtotime('+1 second')),
		'end_date' => date('Y-m-d H:i:s', strtotime('+1 week')),
	));
	
	//loop through and attach the correct info to each meeting listing
	foreach ($tribe_events as $event) {
	
		//get metadata for event
		$custom = get_post_meta($event->ID);
	
		//skip if no venue	
		if (empty($custom['_EventVenueID'][0]) || !array_key_exists($custom['_EventVenueID'][0], $locations)) continue;
	
		//figure out the meeting types for this meeting
		$meeting_types = array();
		$type = tribe_get_custom_field('Type');
		foreach ($types as $key => $value) {
			if (strstr($type, $key)) {
				$meeting_types[] = $value;
			}
		}
		
		//append meeting to the array
		$meetings[] = array_merge(array(
			'name' => $event->post_title,
			'slug' => $event->post_parent ?: $event->ID,
			'day' => date('w', strtotime($custom['_EventStartDate'][0])),
			'time' => date('G:i', strtotime($custom['_EventStartDate'][0])),
			'end_time' => date('G:i', strtotime($custom['_EventEndDate'][0])),
			'notes' => trim(strip_tags($event->post_content)),
			'url' => get_permalink($event->ID),
			'updated' => $event->post_modified_gmt,
			'types' => $meeting_types,
		), $locations[$custom['_EventVenueID'][0]]);
	}
	
	wp_send_json($meetings);
}