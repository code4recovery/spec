<?php

//debug function
function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

//need these for looping
$days = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
$type_lookup = array(
    'O' => 'O',
    'C' => 'C',
    'ag' => 'A',
    'AB' => 'ABSI',
    'BG' => 'BE',
    'BB' => 'B',
    'CB' => 'LIT',
    //'CC' => 'Chair's Choice',
    'DR' => 'DR',
    'D' => 'D',
    'FR' => 'FR',
    'g' => 'G',
    'GV' => 'GR',
    'LT' => 'LIT',
    'LS' => 'LS',
    'M' => 'MED',
    'm' => 'M',
    'PG' => 'POR',
    //'RF' => 'Rotating Format',
    'RU' => 'RUS',
    'SH' => 'S',
    'SP' => 'SP',
    'SPD' => array('SP', 'D'),
    'ST' => 'ST',
    'SS' => 'ST',
    'STR' => array('ST', 'TR'),
    'TR' => 'TR',
    'w' => 'W',
    'YP' => 'Y',
);

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
	$result = $pdo->query('SELECT ID, post_title, post_name, post_modified_gmt FROM ' . $table_prefix . 'posts WHERE post_type = "locations" AND post_status = "publish"');
	//$meetings = $pdo->query('SELECT ID, post_title, post_modified_gmt FROM ' . $table_prefix . 'posts WHERE post_type = "meetings" AND post_status = "publish"');
	//$meta = $pdo->query('SELECT post_id, meta_key, meta_value FROM ' . $table_prefix . 'postmeta WHERE post_id IN (SELECT ID FROM ' . $table_prefix . 'posts WHERE meta_key NOT LIKE "\_%" AND post_type IN ("meetings", "locations"))');
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

//define some arrays
$locations = $groups = $meetings = array();

//build an array of locations, keyed by id
foreach ($result as $r) {
	$locations[$r->ID] = array(
		'location' => $r->post_title,
		'location_slug' => $r->post_name,
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
	$groups[$r->ID] = array(
		'name' => $r->post_title,
		'meeting_updated' =>  $r->post_modified_gmt,
	);
}

//get group metadata
$result = $pdo->query('SELECT post_id, meta_key, meta_value FROM ' . $table_prefix . 'postmeta WHERE meta_key NOT LIKE "\_%" AND post_id IN (' . implode(',', array_keys($groups)) . ')');

//attach meta values to group
foreach ($result as $r) {
	if ($r->meta_key == 'meeting_location') {
		//attach location info to meeting
		if (array_key_exists($r->meta_value, $locations)) {
			$groups[$r->post_id] = array_merge($groups[$r->post_id], $locations[$r->meta_value]);
		}
	}
	$groups[$r->post_id][$r->meta_key] = $r->meta_value;	
}

//go through the groups and add to the meetings array in the right format
foreach ($groups as $group_id => $group) {
	foreach ($days as $day) {
		if (!empty($group[$day . '_new'])) {
			for ($i = 0; $i < $group[$day . '_new']; $i++) {

				//decode types
				$types = array();
				if ($group['accessible_venue'] == '1') $types[] = 'X';				
				if (isset($group[$day . '_new_' . $i . '_meeting_type'])) {
					$undecoded_types = unserialize($group[$day . '_new_' . $i . '_meeting_type']);
					foreach ($undecoded_types as $type) {
						if (array_key_exists($type, $type_lookup)) {
							if (is_array($type_lookup[$type])) {
								$types = array_merge($types, $type_lookup[$type]);
							} else {
								$types[] = $type_lookup[$type];
							}
						}
					}
				}

				//append meeting to meetings array
				$meetings[] = array(
					'slug' => $group_id . '_' . $day . '_' . $i,
					'name' => $group['name'],
					'location' => $group['location'],
					'updated' => max($group['meeting_updated'], $group['location_updated']),
					'city' => $group['city'],
					'state' => $group['state'],
					'street_address' => $group['street_address'],
					'notes' => $group['suite_number'],
					'postal_code' => $group['zip'],
					'time' => $group[$day . '_new_' . $i . '_meeting_time'],
					'types' => $types,
					'url' => 'https://aabroward.org/locations/' . $group['location_slug'] . '/',
				);

			}
		}
	}
}

//encode JSON
$return = json_encode($meetings);
if (json_last_error()) {
	die('JSON error: ' . json_last_error_msg());
}

//make sure headers haven't already been sent, will cause error
if (headers_sent()) {
	die('Error: headers already sent!');
}

//output
header('Content-type: application/json; charset=utf-8');
echo $return;
