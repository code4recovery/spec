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

function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

$meetings = array();

$day_lookup = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

foreach ($result as $r) {

	$types = array();

	$address = $r['address'];

	if ($r['day'] == 'Everyday') {
		$day = range(0, 6);
	} elseif (in_array($r['day'], $day_lookup)) {
		$day = array_search($r['day'], $day_lookup);
	} else {
		continue;
	}

	$meetings[] = array(
		'slug' => $r['id'],
		'city' => $r['city'],
		'day' => $day,
		'time' => date('H:i', strtotime($r['timeampm'])),
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