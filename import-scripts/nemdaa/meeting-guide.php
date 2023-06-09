<?php

//database connection info, edit me!
$host		= 'localhost';
$user		= 'root';
$password	= '';
$database	= 'nemdaa';

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
	$result = $pdo->query('SELECT * FROM meetingsfordisplay');
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

$type_lookup = array(
	'As Bill Sees It' => 'ABSI',
	'Beginners' => 'BE',
	'Big Book' => 'B',
	'Discussion' => 'D',
	'Grapevine' => 'GR',
	'Speaker' => 'SP',
	'Step Study' => 'ST',
);

$day_lookup = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

//fetch data
$return = array();
foreach ($result as $row) {

	$types = array();

	if (!empty($row['Open'])) $types[] = 'O';
	if (!empty($row['Sex'])) {
		if ($row['Sex'] == 'W') {
			$types[] = 'W';
		} elseif ($row['Sex'] == 'M') {
			$types[] = 'M';
		}
	}
	if (!empty($row['Spanish'])) $types[] = 'S';
	if (!empty($row['Disability'])) $types[] = 'X';
	if (!empty($row['Babysit'])) $types[] = 'BA';
	if (!empty($row['Type']) && array_key_exists($row['Type'], $type_lookup)) {
		$types[] = $type_lookup[$row['Type']];
	}

	//build array
	$return[] = array(
		'slug' => $row['ID'],
		'day' => array_search($row['Day'], $day_lookup),
		'time' => $row['Time'],
		'name' => $row['GroupName'],
		'types' => $types,
		'location' => $row['Add2'],
		'address' => $row['Add1'],
		'city' => $row['City'],
		'state' => 'MD',
		'postal_code' => $row['ZIP'],
		'country' => 'US',
		'updated' => $row['Updated'],
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
