<?php

//database connection info, edit me!
$host		= 'localhost';
$user		= 'root';
$password	= '';
$database	= 'suffolk_county';

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
	$result = $pdo->query('SELECT * FROM `2019_Suffolk_MG`');
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

function array_flatten($array, $return=array()) {
	for ($x = 0; $x <= count($array); $x++) {
		if (isset($array[$x])) {
			if (is_array($array[$x])) {
				$return = array_flatten($array[$x], $return);
			} else {
				$return[] = $array[$x];
			}
		}
	}
	return $return;
}

$type_lookup = array(
	'A' => 'ASL', // ​American Sign Language
	'ABSI' => 'ABSI', // As Bill Sees It
	'ABSIT' => 'ABSI', // As Bill Sees It
	'B' => 'BE', // Newcomer
	'BB' => 'B', // Big Book
	'BBOD' => array('B', 'O', 'D'), // Big Book, Open, Discussion
	'BYOC' => 'C', // Bring Your Own Closed
	'C' => 'C', // Closed
	'CD' => array('C', 'D'), // Closed, Discussion
	'DR' => 'DR', // Daily Reflections
	'GV' => 'GR', // Grapevine
	'LIT' => 'LIT', // Literature
	'LS' => 'LS', // Living Sober
	'M' => 'M', // Mens
	'MC' => array('M', 'C'), // Mens, Closed
	'MED' => 'MED', // Meditation
	'MEDITATION' => 'MED', // Meditation
	'O' => 'O', // Open
	'OB' => array('O', 'BE'), // Open, Newcomer
	'OBB' => array('O', 'B'), // Open, Big Book
	'OD' => array('O', 'D'), // Open, Discussion
	'ODB' => array('O', 'D', 'BE'), // Open, Discussion, Newcomer
	'OS' => array('O', 'ST'), // Open, Step Meeting
	//[24] => PROMISES = Promises
	'S' => 'ST', // Step
	'SP' => 'S', // Spanish
	'T' => 'TR', // Tradition
	'TOPIC' => 'D', // Topic
	'W' => 'W', // Womens
	'WC' => array('W', 'C'), // Womens, Closed
	'WCD' => array('W', 'C', 'D'), // Womens, Closed, Discussion
	'WS' => array('W', 'ST'), // Womens, Step Meeting
	'YP' => 'YP', // Young People
);

$type_keys = array_keys($type_lookup);

$day_lookup = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

$all_types = array();

//fetch data
$return = array();
foreach ($result as $row) {

	//types
	$row['new_type'] = str_replace(array(',', ';', ':'), ' ', strtoupper($row['new_type']));
	$types = explode(' ', $row['new_type']);
	//$all_types = array_merge($all_types, $types);
	$types = array_filter($types, function($type) use ($type_keys) {
		return in_array($type, $type_keys);
	});
	$types = array_map(function($type) use ($type_lookup) {
		return $type_lookup[$type];
	}, $types);
	$types = array_flatten($types);

	//add C whenever meeting is not O
	//if (!in_array($types, 'O')) $types[] = 'C';

	//build array
	$return[] = array(
		'slug' => $row['meeting_id'],
		'day' => array_search($row['new_day'], $day_lookup),
		'time' => date('H:i', strtotime($row['new_time'])),
		'name' => $row['group_name'],
		'types' => $types,
		'address' => $row['locationAddress'],
		'city' => $row['locationCity'],
		'state' => $row['locationState'],
		'postal_code' => $row['locationZip'],
		'country' => 'US',
		'notes' => $row['new_note'],
	);
}

/*
$all_types = array_unique($all_types);
sort($all_types);
dd($all_types);
*/

//encode JSON
$return = json_encode($return);
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
