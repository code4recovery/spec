<?php

//database connection info, edit me!
$host		= 'localhost';
$user		= 'root';
$password	= '';
$database	= 'nw_texas';

//make sure errors are being reported
error_reporting(E_ALL);

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
	$result = $pdo->query('SELECT * FROM scheds');
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

//debugging
function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

//convert local meeting type to meeting guide type
function meetingGuideType($type) {
	global $type_lookup;
	return array_key_exists($type, $type_lookup) ? $type_lookup[$type] : null;
}

//define array to return
$meetings = array();

//will use later to convert days to integers
$day_lookup = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

//will use later to convert local types to meeting guide types
$type_lookup = array(
	'O' => 'O',
	'C' => 'C',
	'D' => 'D',
	'BB' => 'B',
	'S' => 'SP',
	'SS' => 'ST',
	'M' => 'M',
	'W' => 'W',
	'ABSI' => 'ABSI',
	'NS' => 'NS',
);

foreach ($result as $r) {

	//replace string day with integer
	if ($r['day'] == 'Everyday') {
		$day = range(0, 6);
	} elseif (in_array($r['day'], $day_lookup)) {
		$day = array_search($r['day'], $day_lookup);
	} else {
		continue;
	}

	//turn types column into array of meeting guide type codes
	$types = explode(' ', str_replace(array(',', '*'), ' ', $r['type']));
	$types = array_values(array_filter(array_map('meetingGuideType', $types)));

	//format address, split notes off
	$address = $r['address'];

	//return a key
	$meetings[] = array(
		'slug' => $r['id'],
		'city' => $r['city'],
		'day' => $day,
		'time' => date('H:i', strtotime($r['timeampm'])),
		//'timeampm' => $r['timeampm'],
		//'type' => $r['type'],
		'types' => $types,
		'address' => $address,
		'phone' => $r['phone'],
		'name' => $r['groupname'],
		'state' => 'TX',
		'country' => 'US',
	);
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