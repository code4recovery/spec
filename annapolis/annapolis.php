<?php

//database connection info, edit me!
$host		= '127.0.0.1';
$user		= 'root';
$password	= '';
$database	= 'annapolis';

//sql query
$sql		= 'SELECT 
	meeting.id AS `slug`,
	meeting.day,
	meeting.time,
	meeting.language,
	meeting.mtg_notes AS `notes`,
	type.mtg_type AS `types`,
	aagroup.gp_name AS `name`,
	location.loc_name AS `location`,
	location.addr1 AS `address`,
	location.addr2,
	location.city,
	location.notes AS `location_notes`
FROM meeting
JOIN type ON meeting.mtg_type = type.id
JOIN aagroup ON meeting.group_id = aagroup.id
JOIN location ON meeting.location_id = location.id';

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
	$result = $pdo->query($sql);
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

//fetch data
$return = array();
foreach ($result as $row) {
	//do any processing here
	
	
	
	
	$return[] = $row;
}


//encode JSON
$return = json_encode($array);
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
