<?php
//simple PHP script to output API for a basic database

//database connection info, edit me!
$server		= '127.0.0.1';
$username	= 'root';
$password	= '';
$database	= 'aa_santa_barbara';

$sql		= 'SELECT 
	meetings_id `slug`,
	meetings_time `time`,
	meetings_name `name`,
	meetings_types `types`,
	meetings_location `location`,
	meetings_directions `notes`,
	meetings_address `address`,
	meetings_day_of_week `day`,
	meetings_city `city`,
	meetings_is_spanish
FROM meetings';

//if you have both meetings and locations, try this one. You may need to customize it some.
//$sql		= 'SELECT meetings.*, locations.* FROM meetings JOIN locations ON meetings.location_id = locations.id';

//send header and turn off error reporting so we can get a proper HTTP result
header('Content-type: application/json; charset=utf-8');
error_reporting(E_ALL);

//connect to database
if (empty($server)) error('$server variable is empty');
$link = mysqli_connect($server, $username, $password) or error('could not connect to database server');
mysqli_select_db($link, $database) or error('could not select database');
mysqli_set_charset($link, 'utf8');

//select data
if (empty($sql)) error('$sql variable is empty');
$result = mysqli_query($link, $sql);
if (!$result) error(mysql_error($link));

$decode_types = array(
	'C' => 'C',
	'SS' => 'ST',
	'BB' => 'B',
	'SPK' => 'SP',
	'P' => 'D',
	'H' => 'X',
	'W' => 'W',
	'M' => 'M',
	'ME' => 'MED',
	'GLBT' => 'LGBTQ',
	'CC' => 'BA',
);

$decode_days = array(
	'SUNDAY' => 0,
	'MONDAY' => 1,
	'TUESDAY' => 2,
	'WEDNESDAY' => 3,
	'THURSDAY' => 4,
	'FRIDAY' => 5,
	'SATURDAY' => 6,
);

//fetch data into array
$return = array();
while ($row = mysqli_fetch_assoc($result)) {

	//format days
	$days = explode(',', $row['day']);
	$days = array_map('strtoupper', $days);
	$days = array_map('trim', $days);
	$row['day'] = array();
	foreach ($days as $day) {
		if (array_key_exists($day, $decode_days)) {
			$row['day'][] = $decode_days[$day];
		}
	}
	
	//format time
	$row['time'] = substr($row['time'], 0, 5);
	
	//format types
	$types = explode(',', $row['types']);
	$types = array_map('strtoupper', $types);
	$types = array_map('trim', $types);
	$row['types'] = array();
	foreach ($types as $type) {
		if (array_key_exists($type, $decode_types)) {
			$row['types'][] = $decode_types[$type];
		}
	}
	if ($row['meetings_is_spanish'] != '0') {
		$row['types'][] = 'S';
	}
	$row['types'] = array_unique($row['types']);
	unset($row['meetings_is_spanish']);
	
	$row['city'] = trim($row['city']);
	if (substr(strtoupper($row['city']), -4) == ', CA') {
		$row['city'] = substr($row['city'], 0, strlen($row['city']) - 4);
	}
	
	//additional
	$row['address'] = strip_tags($row['address']);
	$row['state'] = 'CA';
	$row['country'] = 'US';
	
	$return[] = $row;
}

mysqli_free_result($result);
mysqli_close($link);

//return JSON
output($return);

//function to handle errors
function error($string) {
	output(array(
		'error' => $string,
	));
}

//function to output json
function output($array) {
	$return = json_encode($array);
	if (json_last_error()) error(json_last_error_msg());
	die($return);
}