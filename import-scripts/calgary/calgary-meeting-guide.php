<?php
/*
Plugin Name: Calgary Meeting Guide
Plugin URI: http://calgaryaa.org
Description: Create a bridge for the Meeting Guide app
Version: 1.0
Author: Meeting Guide
Author URI: https://meetingguide.org
License: none
Text Domain: cmg
*/

//create ajax function that Meeting Guide can access
add_action('wp_ajax_meetingguide', 'cmg_data_feed');
add_action('wp_ajax_nopriv_meetingguide', 'cmg_data_feed');

function cmg_data_feed() {
	global $wpdb;
	
	$meetings = $values = array();
	
	//get basic meetings
	$posts = $wpdb->get_results('SELECT
		ID,
		post_title,
		post_name,
		post_modified_gmt
	FROM al_posts 
	WHERE post_type = "alco-meetings" AND post_status = "publish"');
	
	//loop through and create a keyed array of meetings
	foreach ($posts as $post) {
		$meetings[$post->ID] = array(
			'name' => $post->post_title,
			'slug' => $post->post_name,
			'updated' => $post->post_modified_gmt,	
			'types' => array(),
		);
	}
	
	//get metadata
	$meta = $wpdb->get_results('SELECT 
		post_id, 
		meta_key, 
		meta_value 
	FROM al_postmeta
	WHERE post_id IN (' . implode(',', array_keys($meetings)) . ') AND 
		meta_key IN ("date-time", "address", "decri", "code", "cityq", "landmark")');

	//loop through metadata and attach to meeting
	foreach ($meta as $value) {
		$value->meta_value = trim($value->meta_value);
		if ($value->meta_key == 'date-time') {
			//fix certain times
			$value->meta_value = strtolower($value->meta_value);
			$value->meta_value = str_replace('noon', 'pm', $value->meta_value);
			$value->meta_value = str_replace('midnight', 'am', $value->meta_value);
			$meetings[$value->post_id]['time'] = $value->meta_value;
			//$values[] = $value->meta_value;
		} elseif ($value->meta_key == 'address') {
			$meetings[$value->post_id]['address'] = $value->meta_value;
		} elseif ($value->meta_key == 'decri') {
			$meetings[$value->post_id]['notes'] = $value->meta_value;
		} elseif ($value->meta_key == 'code') {
		} elseif ($value->meta_key == 'cityq') {
			$meetings[$value->post_id]['region'] = $value->meta_value;
		} elseif ($value->meta_key == 'landmark') {
			$meetings[$value->post_id]['location'] = $value->meta_value;
		}
	}
		
	//grab terms
	$terms = $wpdb->get_results('SELECT  
		r.object_id,
		t.`name`,
		x.taxonomy
	FROM al_term_relationships r 
	JOIN al_term_taxonomy x ON r.term_taxonomy_id = x.term_taxonomy_id
	JOIN al_terms t ON x.term_id = t.term_id
	WHERE object_id 	IN (' . implode(',', array_keys($meetings)) . ')');
	
	//day options
	$days = array(
		'sunday' => 0,
		'monday' => 1,
		'tuesday' => 2,
		'wednesday' => 3,
		'thursday' => 4,
		'friday' => 5,
		'saturday' => 6,
	);
	
	foreach ($terms as $term) {
		$term->name = strtolower(trim($term->name));
		if ($term->taxonomy == 'day-of-week') {
			if (array_key_exists($term->name, $days)) {
				$meetings[$term->object_id]['day'] = $days[$term->name];
			}
		} elseif ($term->taxonomy == 'meeting-type') {
			if ($term->name == 'closed meeting') {
				$meetings[$term->object_id]['types'][] = 'C';
			} elseif ($term->name == 'gay/lesbian') {
				$meetings[$term->object_id]['types'][] = 'LGBTQ';
			} elseif ($term->name == 'men only') {
				$meetings[$term->object_id]['types'][] = 'M';
			} elseif ($term->name == 'open meeting') {
				$meetings[$term->object_id]['types'][] = 'O';
			} elseif ($term->name == 'polish speaking') {
				$meetings[$term->object_id]['types'][] = 'POL';
			} elseif ($term->name == 'spanish speaking') {
				$meetings[$term->object_id]['types'][] = 'S';
			} elseif ($term->name == 'wheelchair accessible') {
				$meetings[$term->object_id]['types'][] = 'X';
			} elseif ($term->name == 'women only') {
				$meetings[$term->object_id]['types'][] = 'W';
			}
		} elseif ($term->taxonomy == 'location-meeting') {
			//potential region (northeast, northwest, out of town) -- think city name is better
			//$values[] = $term->name;
		}
	}
	
	//debugging: show collected values
	if (count($values)) {
		sort($values);
		wp_send_json(array_unique($values));
	}
	
	//remove any that don't have a day or an address
	foreach ($meetings as $meeting_id => $meeting) {
		if (!isset($meeting['day'])) unset($meetings[$meeting_id]);
		if (empty($meeting['address'])) unset($meetings[$meeting_id]);
	}
	
	wp_send_json(array_values($meetings));
}
