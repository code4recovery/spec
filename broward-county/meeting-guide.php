<?php

//debug function
function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

//need these for looping
$days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

if (file_exists('wp-config.php')) {
	include('wp-config.php');
} else {
	define('DB_NAME', 'broward');
	define('DB_USER', 'root');
	define('DB_PASSWORD', '');
	define('DB_HOST', 'localhost');
	$table_prefix  = 'aabci_';
}

//make sure errors are being reported
error_reporting(E_ALL);

//connect to database
try {
    $pdo = new PDO('mysql:charset=UTF8;dbname=' . DB_NAME . ';host=' . DB_HOST, DB_USER, DB_PASSWORD);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

//error handling
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

//select location data
try {
	$result = $pdo->query('SELECT ID, post_title, post_modified_gmt FROM ' . $table_prefix . 'posts WHERE post_type = "locations" AND post_status = "publish"');
	//$meetings = $pdo->query('SELECT ID, post_title, post_modified_gmt FROM ' . $table_prefix . 'posts WHERE post_type = "meetings" AND post_status = "publish"');
	//$meta = $pdo->query('SELECT post_id, meta_key, meta_value FROM ' . $table_prefix . 'postmeta WHERE post_id IN (SELECT ID FROM ' . $table_prefix . 'posts WHERE meta_key NOT LIKE "\_%" AND post_type IN ("meetings", "locations"))');
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

//define some arrays
$locations = $meetings = array();

//build an array of locations, keyed by id
foreach ($result as $r) {
	$locations[$r->ID] = array(
		'location' => $r->post_title,
		'location_updated' =>  $r->post_modified_gmt,
	);
}

//get location metadata
$result = $pdo->query('SELECT post_id, meta_key, meta_value FROM ' . $table_prefix . 'postmeta WHERE meta_key IN ("street_address", "suite_number", "city", "state", "zip", "accessible_venue") AND post_id IN (' . implode(',', array_keys($locations)) . ')');

//attach meta values to locations
foreach ($result as $r) {
	$locations[$r->post_id][$r->meta_key] = $r->meta_value;
}

//get meetings
$result = $pdo->query('SELECT ID, post_title, post_modified_gmt FROM ' . $table_prefix . 'posts WHERE post_type = "meetings" AND post_status = "publish"');

//build an array of meetings, keyed by id
foreach ($result as $r) {
	$meetings[$r->ID] = array(
		'name' => $r->post_title,
		'meeting_updated' =>  $r->post_modified_gmt,
	);
}

//get meeting metadata
$result = $pdo->query('SELECT post_id, meta_key, meta_value FROM ' . $table_prefix . 'postmeta WHERE post_id IN (' . implode(',', array_keys($meetings)) . ')');

$days = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

//attach meta values to meetings
foreach ($result as $r) {
	if ($r->meta_key == 'meeting_location') {
		//attach location info to meeting
		if (array_key_exists($r->meta_value, $locations)) {
			$meetings[$r->post_id] = array_merge($meetings[$r->post_id], $locations[$r->meta_value]);
		}
	}
	foreach ($days as $day) {
		if ($r->meta_key == $day . '_new' && !empty($r->meta_value)) {
			for ($i = 0; $i < $r->meta_value; $i++) {
				dd()
			}
		}
	}
	$meetings[$r->post_id][$r->meta_key] = $r->meta_value;	
}

dd($meetings);

function getMeta($id, $key) {
	global $meta;
	$match = current(array_filter($meta, function($meta) use ($id, $key) {
		return $meta->post_id == $id && $meta->meta_key == $key;
	}));
	return $match ? $match->meta_value : null;
}

//reformat meetings
$meetings = array_map(function($meeting){
	global $locations, $meta;
	
	$meeting_meta = array_filter($meta, function($meta) use ($meeting) {
		return $meta->post_id == $meeting->ID;
	});

	$location_id = current(array_filter($meeting_meta, function($meta){
		return $meta->meta_key == 'meeting_location';
	}))->meta_value;

	$location = current(array_filter($locations, function($location) use ($location_id) {
		return $location->ID == $location_id;
	}));

	if ($access = getMeta($location_id, 'accessible_venue')) {
		//add H to types

	}

	return array(
		'slug' => $meeting->ID,
		'name' => $meeting->post_title,
		'updated' => $meeting->post_modified_gmt,
		'location_id' => $location_id,
		'location' => $location ? $location->post_title : '',
		'location_notes' => getMeta($location_id, 'suite_number'),
		'address' => getMeta($location_id, 'street_address'),
		'city' => getMeta($location_id, 'city'),
		'state' => getMeta($location_id, 'state'),
		'postal_code' => getMeta($location_id, 'zip'),
	);
}, $meetings);

dd($meetings);

