<?php
//simple PHP script to output API for a basic database

//database connection info, edit me!
$server		= '127.0.0.1';
$username	= 'root';
$password	= '';
$database	= 'your_database_name';

//if you have only a meetings table, use this variable
$sql		= 'SELECT * FROM meetings';

//send header and turn off error reporting so we can get a proper HTTP result
header('Content-type: application/json; charset=utf-8');
error_reporting(E_ALL);

//connect to database
if (empty($server)) error('$server variable is empty');
$link = mysql_connect($server, $username, $password) or error('could not connect to database server');
mysql_select_db($database, $link) or error('could not select database');
mysql_set_charset('utf8', $link);

//select data
if (empty($sql)) error('$sql variable is empty');
$result = mysql_query($sql, $link);
if (!$result) error(mysql_error($link));

//fetch data into array
$return = array();
while ($row = mysql_fetch_assoc($result)) {
	
	//assemble types & notes fields
	$types = $notes = array();
	
	//notes
	if (!empty($row['announcementNotes'])) $notes[] = $row['announcementNotes'];
	if (!empty($row['locationNotes'])) $notes[] = $row['locationNotes'];
	if (!empty($row['businessMeeting'])) $notes[] = $row['businessMeeting'];
	
	//types
	if ($row['language'] == 'Spanish') $types[] = 'S';
	if ($row['language'] == 'French') $types[] = 'FR';
	if (!empty($row['typeClosed'])) {
		$types[] = 'C';
	} elseif (!empty($row['typeOpen'])) {
		$types[] = 'O';
	}
	if (!empty($row['accessibility'])) $types[] = 'X';
	if (!empty($row['typeDiscussion'])) $types[] = 'D';
	if (!empty($row['typeSpeaker'])) $types[] = 'SP';
	if (!empty($row['typeMeditation'])) $types[] = 'MED';
	if ($row['typeSpecial'] == 'WO') {
		$types[] = 'W';
	} elseif ($row['typeSpecial'] == 'LGBT') {
		$types[] = 'LGBTQ';
	} elseif ($row['typeSpecial'] == 'MO') {
		$types[] = 'M';
	} elseif ($row['typeSpecial'] == 'STEP') {
		$types[] = 'ST';
	}
	if ($row['typeNotes'] == 'BB') {
		$types[] = 'B';
	} elseif ($row['typeNotes'] == 'TR') {
		$types[] = 'TR';
	} elseif ($row['typeNotes'] == 'MO') {
		$types[] = 'M';
	} elseif ($row['typeNotes'] == 'STEP') {
		$types[] = 'ST';
	}
	$types = array_unique($types);
	
	$return[] = array(
		'name' => $row['groupName'],
		'slug' => $row['meetingId'],
		'day' => $row['dayNumber'],
		'time' => $row['meetingTime'],
		'location' => $row['landmark'],
		'group' => $row['groupName'],
		'notes' => implode(PHP_EOL, $notes),
		//'updated' => $row['meetingId'],
		//'url' => $row['meetingId'],
		'types' => $types,
		'address' => $row['location'],
		'city' => $row['city'],
		//'postal_code' => $row['meetingId'],
		'country' => 'CA',
		'region' => $row['area'],
		'latitude' => $row['latitude'],
		'longitude' => $row['longitude'],
	);
}
mysql_free_result($result);
mysql_close($link);

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