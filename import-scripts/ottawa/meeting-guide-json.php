<?php
//simple PHP script to output API for a basic database

$host		= 'localhost';
$user		= 'root';
$password	= '';
$database	= 'ottawa';

//make sure errors are being reported

error_reporting(E_ALL);

//send header and turn off error reporting so we can get a proper HTTP result
header('Content-type: application/json; charset=utf-8');

//connect to database

try {

    $pdo = new PDO('mysql:charset=UTF8;dbname=' . $database . ';host=' . $host, $user, $password);

} catch (PDOException $e) {

    die('Database connection failed: ' . $e->getMessage());

}

//error handling

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


//select data

try {

	$result = $pdo->query('SELECT * FROM meetings');

} catch (PDOException $e) {

    die('SQL query failed: ' . $e->getMessage());

}



//debugging

function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

//fetch data into array
$return = array();
foreach ($result as $row) {
	
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
		'day' => $row['dayNumber'] - 1,
		'time' => $row['meetingTime'],
		'location' => $row['landmark'],
		'group' => $row['groupName'],
		'notes' => implode(PHP_EOL, $notes),
		'updated' => date('Y-m-d H:m:s'),
		'url' => null,
		'types' => $types,
		'address' => $row['location'],
		'city' => $row['city'],
		'state' => null,
		'postal_code' => null,
		'country' => 'CA',
		'region' => $row['area'],
		'latitude' => $row['latitude'],
		'longitude' => $row['longitude'],
	);
}


//return JSON
$return = json_encode($return);
if (json_last_error()) error(json_last_error_msg());
die($return);

//function to handle errors
function error($string) {
	output(array(
		'error' => $string,
	));
}