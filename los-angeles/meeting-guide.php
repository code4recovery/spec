<?php

//database connection info, edit me!
$host		= 'localhost';
$user		= 'root';
$password	= '';
$database	= 'lacoaa';

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
	$result = $pdo->query('SELECT * FROM meetings');
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

//fetch data
$return = array();
foreach ($result as $row) {
	
	//trim all values
	$row = array_map('trim', $row);
	
	//types
	$types = array();
	if ($row['CLOSED'] == '1') {
		$types[] = 'C';
	} else {
		$types[] = 'O';
	}
	if ($row['MEN'] == '1') 		$types[] = 'M';
	if ($row['WOMEN'] == '1')	$types[] = 'W';
	if ($row['GAY'] == '1')		$types[] = 'LGBTQ';
	//if ($row['YOUNG'] == '1')	$types[] = 'Y';
	
	//build array
	$return[] = array(
		'slug' => $row['MEET_NUM'],
		'day' => intval($row['MtgDow']) - 1,
		'name' => $row['Group_Name'],
		'time' => date('H:i', strtotime($row['MtgTime'])),
		'types' => $types,
		'address' => $row['Address'],
		'city' => $row['City'],
		'state' => 'CA',
		'postal_code' => $row['Thomasbros'],
		'country' => 'US',
		'region' => $row['Location'],
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
