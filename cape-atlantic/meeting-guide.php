<?php
/*
Plugin Name: Meeting Guide Link
Plugin URI: https://github.com/meeting-guide/spec
Description: Link to Meeting Guide
Version: 1.0.0
Author: Meeting Guide
Author URI: https://meetingguide.org
Text Domain: mgl
*/

//api ajax function
//used by theme, web app, mobile app
add_action('wp_ajax_meeting_guide_link', 'mgl_link');
add_action('wp_ajax_nopriv_meeting_guide_link', 'mgl_link');

if (!function_exists('mgl_link')) {
	function mgl_link() {
		//make sure errors are being reported
		error_reporting(E_ALL);
		ini_set('display_errors', 'On');

		global $wpdb;

		$result = $wpdb->get_results('SELECT 
			SUBSTRING_INDEX(p.post_title, ": ", -1) name,
			p.post_name slug,
			TIME_FORMAT(FROM_UNIXTIME(e.start), "%H:%i") "time", 
			TIME_FORMAT(FROM_UNIXTIME(e.end), "%H:%i") "end_time", 
			e.recurrence_rules, 
			e.venue "location", 
			e.address "formatted_address",
			GROUP_CONCAT(CASE WHEN x.taxonomy = "events_tags" THEN REPLACE(t.name, "/", "") ELSE null END SEPARATOR "|") `types`,
			p.post_content "notes",
			p.post_modified_gmt "updated"
		FROM wp_ai1ec_events e
		JOIN wp_posts p ON e.post_id = p.ID
		JOIN wp_term_relationships r ON p.ID = r.object_id
		JOIN wp_term_taxonomy x ON r.term_taxonomy_id = x.term_taxonomy_id
		JOIN wp_terms t ON x.term_id = t.term_id
		GROUP BY p.ID');

		//for appending daily meetings
		$daily = array(); 

		//days
		$days = array('BYday=SU', 'BYday=MO', 'BYday=TU', 'BYday=WE', 'BYday=TH', 'BYday=FR', 'BYday=SA');

		//types
		$types = array(
			'Accessible' => 'X',
			'As Bill Sees It' => 'ABSI',
			//'Beach Meeting' => 
			'Beginner' => 'BE',
			'Big Book' => 'BB',
			//'Came to Believe' =>
			'Closed Meeting' => 'C',
			'Crosstalk encouraged' => 'XT',
			'Daily Reflections' => 'DR',
			'Discussion' => 'D',
			'Grapevine' => 'GR',
			//'Joe & Charlie' => 
			'Literature' => 'LIT',
			'Living Sober' => '',
			'Meditation' => 'MED',
			'Men\'s' => 'M',
			'Open Meeting' => 'O',
			'Spanish Speaking' => 'S',
			'Speaker' => 'SP',
			//'Spiritual Meeting' => 
			'Step' => 'ST',
			'Tradition' => 'T',
			//'Varied Format' => 
			'Women\'s' => 'W',
			'Young People' => 'YP',
			//variable
		);

		for ($i = 0; $i < count($result); $i++) {

			//url
			$result[$i]->url = 'http://capeatlanticaa.org/ai1ec-event/' . $result[$i]->slug . '/';

			//notes
			$result[$i]->notes = trim(strip_tags($result[$i]->notes));

			//handle types
			//$result[$i]->type_compare = $result[$i]->types;
			$meeting_types = explode('|', $result[$i]->types);
			$result[$i]->types = array();
			foreach ($meeting_types as $type) {
				if (array_key_exists($type, $types)) {
					$result[$i]->types[] = $types[$type];
				}
			}

			//get day from recurrence_rules
			if (!empty($result[$i]->recurrence_rules)) {
				if ($result[$i]->recurrence_rules == 'FREQ=DAILY;') {
					unset($result[$i]->recurrence_rules);
					for ($day = 0; $day < 7; $day++) {
						$result[$i]->day = $day;
						$daily[] = clone($result[$i]);
					}
				} else {
					$rules = explode(';', $result[$i]->recurrence_rules);
					if (isset($rules[2])) {
						$day = array_search($rules[2], $days);
						if ($day !== false) {
							$result[$i]->day = $day;
							unset($result[$i]->recurrence_rules);
						}
					}
				}
			}
		}

		$result = array_merge($result, $daily);

		//dd($daily);

		if (!headers_sent()) header('Access-Control-Allow-Origin: *');

		wp_send_json($result);
	}
}