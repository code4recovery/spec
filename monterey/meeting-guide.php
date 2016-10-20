<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

//connect to database
require_once('wp-config.php');
$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die('could not connect to db');
mysql_select_db(DB_NAME, $link) or error('could not select database');
mysql_set_charset(DB_CHARSET, $link);

//build array of locations
$locations = array();
$posts = mysql_query('SELECT ID, post_title FROM ' . $table_prefix . 'posts WHERE post_type = "locations" AND post_status = "publish"');
if (!$posts) die('Error selecting locations: ' . mysql_error($link));
while ($post = mysql_fetch_object($posts)) {
	$locations[$post->ID] = array(
		'name' => trim($post->post_title),
		'thumbnail' => null,
	);
}

//build array of metadata (and thumbnails)
$metadata = $thumbnail_ids = array();
$posts = mysql_query('SELECT post_id, meta_key, meta_value FROM ' . $table_prefix . 'postmeta WHERE post_id IN (
	SELECT id FROM ' . $table_prefix . 'posts WHERE post_type IN ("meetings", "locations") AND post_status = "publish"
)');
if (!$posts) die('Error selecting metadata: ' . mysql_error($link));
while ($post = mysql_fetch_object($posts)) {
	if (!array_key_exists($post->post_id, $metadata)) $metadata[$post->post_id] = array();
	$metadata[$post->post_id][$post->meta_key] = trim($post->meta_value);
	if ($post->meta_key == '_thumbnail_id') $thumbnail_ids[] = $post->meta_value;
}

//fetch the thumbnails
$thumbnails = array();
$posts = mysql_query('SELECT ID, guid FROM ' . $table_prefix . 'posts WHERE ID IN (' . implode(', ', $thumbnail_ids) . ')');
while ($post = mysql_fetch_object($posts)) {
	$thumbnails[$post->ID] = $post->guid;
}

//build array of meetings
$meetings = array();
$posts = mysql_query('SELECT * FROM ' . $table_prefix . 'posts WHERE post_type = "meetings" AND post_status = "publish"');
if (!$posts) die('Error selecting meetings: ' . mysql_error($link));
while ($post = mysql_fetch_object($posts)) {
	
	//check required values
	if (!array_key_exists($post->ID, $metadata)) die('metadata not set for meeting');
	if (empty($metadata[$post->ID]['location'])) die('meeting location id empty');
	if (!array_key_exists($metadata[$post->ID]['location'], $locations)) die('locations empty for location id ' . $metadata[$post->ID]['location']);
	if (!array_key_exists($metadata[$post->ID]['location'], $metadata)) die('metadata empty for location id');

	//variables
	$location_id = $metadata[$post->ID]['location'];
	$meeting = $metadata[$post->ID];
	$location = array_merge(
		$locations[$location_id], 
		$metadata[$location_id]
	);
	
	//get days and codes
	$codes = $days = array();
	$day_decode = array('Sun' => 0, 'Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6);
	if (!empty($meeting['day_of_week'])) {
		$days = unserialize($meeting['day_of_week']);
		if ($days[0] == 'Daily') {
			$days = array(0, 1, 2, 3, 4, 5, 6);			
		} else {
			for ($i = 0; $i < count($days); $i++) {
				if (array_key_exists($days[$i], $day_decode)) {
					$days[$i] = $day_decode[$days[$i]];
				} else {
					unset($days[$i]);
				}
			}
		}
	}

	if (!empty($meeting['codes'])) {
		$codes = unserialize($meeting['codes']);
		for ($i = 0; $i < count($codes); $i++) {
			/*
			O = Open
			C = Closed
			M = Men
			W = Women
			G = Gay/Lesbian
			H = Handicapped Accessible
			PI = Public Information
			Sp = Spanish-speaking
			*/
			if ($codes[$i] == 'PI')	{
				unset($codes[$i]); //don't have a type for it
			} elseif ($codes[$i] == 'Sp') {
				$codes[$i] = 'S';
			} elseif ($codes[$i] == 'H') {
				$codes[$i] = 'X';
			} elseif ($codes[$i] == 'G') {
				if (in_array('M', $codes) && !in_array('W', $codes)) {
					//men's meeting, great
				} elseif (!in_array('M', $codes) && in_array('W', $codes)) {
					$codes[$i] = 'L'; //women's meeting, change to lesbian
				} else {
					$codes[$i] = 'LGBTQ';
				}
			}
		}
	}
	
	//thumbnail?
	$thumbnail = null;
	if (array_key_exists($location['_thumbnail_id'], $thumbnails)) {
		$thumbnail = $thumbnails[$location['_thumbnail_id']];
	}
		
	$meetings[] = array(
		'name' => trim($post->post_title),
		'slug' => $post->post_name,
		'notes' => strip_tags($post->post_content),
		'updated' => $post->post_modified_gmt,
		'location' => $location['name'],
		'formatted_address' => $location['address'],
		'day' => $days,
		'time' => date('H:i', strtotime($meeting['time'])),
		'types' => $codes,
		'image' => $thumbnail,
		'region' => $location['city'],
		'url' => 'http://www.aamonterey.org/meetings/' . $post->post_name . '/',
	);
}

mysql_free_result($posts);

wp_send_json($meetings);