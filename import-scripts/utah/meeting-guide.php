<?php

//database connection info, edit me!
$host		= 'localhost';
$user		= 'root';
$password	= '';
$database	= 'utah';

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
	$result = $pdo->query('SELECT * FROM list 
		WHERE intergroup IS NULL OR intergroup NOT IN (
			"Salt Lake Central Office", 
			"Intergroup Services of Northern Utah",
			"Northern Utah",
			"Utah Valley Central Office"
		)');
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

$day_lookup = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

//fetch data
$return = array();

foreach ($result as $row) {

	$row = array_map('trim', $row);

	$types = array();

	//get types from accessible column
	if (!empty($row['accessible']) && strtoupper($row['accessible']) !== 'N') {
		$types[] = 'X'; //wheelchair access
	}

	//get types from sns column
	if (!empty($row['sns']) && strtoupper($row['sns']) == 'S') {
		$types[] = 'SM'; //smoking
	}

	//get types from type column
	if (!empty($row['type'])) {
		if (stristr($row['type'], 'book') || stristr($row['type'], 'bb')) $types[] = 'B'; //big book
		if (stristr($row['type'], 'medi')) $types[] = 'MED'; //meditation
		if (stristr($row['type'], 'new') || stristr($row['type'], 'begin')) $types[] = 'BE'; //meditation
		if (stristr($row['type'], 'open')) {
			$types[] = 'O'; //open
		} elseif (stristr($row['type'], 'closed')) {
			$types[] = 'C'; //women
		}
		if (stristr($row['type'], 'sign lang')) $types[] = 'ASL'; //sign language
		if (stristr($row['type'], 'spanish')) $types[] = 'S'; //spanish
		if (stristr($row['type'], 'speaker')) $types[] = 'SP'; //speaker
		if (stristr($row['type'], 'step')) $types[] = 'ST'; //step meeting
		if (stristr($row['type'], 'women')) {
			$types[] = 'W'; //women
		} elseif (stristr($row['type'], 'men')) {
			$types[] = 'M'; //men
		}
		if (stristr($row['type'], 'young')) $types[] = 'YP'; //step meeting
	}

	//split location notes from location
	$notes = array();

	if ($pos = strpos($row['address2'], '-')) {
		$notes[] = trim(substr($row['address2'], $pos + 1));
		$row['address2'] = trim(substr($row['address2'], 0, $pos));
	}

	if ($pos = strpos($row['address2'], ',')) {
		$notes[] = trim(substr($row['address2'], $pos + 1));
		$row['address2'] = trim(substr($row['address2'], 0, $pos));
	}

	//build array
	$return[] = array(
		'slug' => $row['ID'],
		'day' => array_search($row['day'], $day_lookup),
		'time' => substr($row['timesort'], 0, 5),
		'name' => $row['name'],
		'types' => $types,
		'address' => $row['address'],
		'location' => $row['address2'],
		'city' => $row['city'],
		'state' => 'UT',
		'country' => 'US',
		'notes' => implode("\n", $notes),
	);
}


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
