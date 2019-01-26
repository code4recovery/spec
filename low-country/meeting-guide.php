<?php

//database connection info, edit me!
$host		= 'localhost';
$user		= 'root';
$password	= '';
$database	= 'low_country';

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
	$result = $pdo->query('SELECT * FROM data');
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

//fetch data
$return = array();
foreach ($result as $row) {

	//build array
	$return[] = array(
		'slug' => $row['id'],
		'name' => $row['name'],
		'day' => $row['day'],
		'time' => $row['time'],
		'end_time' => $row['end_time'],
		'types' => explode(' ', $row['types']),
		'notes' => $row['notes'],
		'location' => $row['location'],
		'address' => $row['address'],
		'city' => $row['city'],
		'state' => 'SC',
		'postal_code' => $row['postal_code'],
		'country' => 'USA',
		'location_notes' => $row['location_notes'],
		'region' => $row['region'],
		'updated' => $row['updated'],
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
