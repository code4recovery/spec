<?php

//database connection info, edit me!
$host		= '127.0.0.1';
$user		= 'root';
$password	= '';
$database	= 'winston_salem';

//sql query
$sql		= 'SELECT 
		meetings_table.id,
		meetings_table.name,
		meetings_table.day,
		meetings_table.time,
		meetings_table.open_closed,
		meetings_table.type,
		meetings_table.spanish,
		meetings_table.haccess,
		meetings_table.affiltn,
		meetings_table.nosmkg,
		meetings_table.notes,
		locations_table.name `group`,
		locations_table.location,
		locations_table.address,
		locations_table.city,
		locations_table.state,
		locations_table.zipcode
	FROM meetings_table JOIN locations_table ON meetings_table.mtg_id = locations_table.id';

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

//type definitions
$decode_days = array(
	'Sunday' => 0,
	'Monday' => 1,
	'Tuesday' => 2,
	'Wednesday' => 3,
	'Thursday' => 4,
	'Friday' => 5,
	'Saturday' => 6,
);

$decode_types = array(
	'Beginners' => 'BE',
	'Child friendly; well-behaved children are welcome. No nursery provided.' => 'CF',
	'Discussion' => 'D',
	'Literature' => 'LIT',
	'Speaker' => 'SP',
);

//fetch data
$return = array();
foreach ($result as $row) {
	
	//trim all values
	$row = array_map('trim', $row);
	
	//make sure day is valid
	if (!array_key_exists($row['day'], $decode_days)) continue;
	
	//set types
	$types = array();
	if (array_key_exists($row['type'], $decode_types)) $types[] = $decode_types[$row['type']];
	if ($row['open_closed'] == 'Open') $types[] = 'O';
	if ($row['open_closed'] == 'Closed') $types[] = 'C';
	if (!empty($row['spanish'])) $types[] = 'S';
	if (!empty($row['haccess'])) $types[] = 'X';
	if (stristr($row['affiltn'], 'women')) $types[] = 'W';
	
	//notes
	if (!empty($row['affiltn'])) {
		if (!empty($row['notes'])) $row['notes'] .= '<br>';
		$row['notes'] .= $row['affiltn'];
	}

	//build array
	$return[] = array(
		'slug' => $row['id'],
		'day' => $decode_days[$row['day']],
		'name' => $row['name'],
		'location' => $row['location'],
		'group' => $row['group'],
		'notes' => $row['notes'],
		'time' => date('H:i', strtotime($row['time'])),
		'types' => $types,
		'address' => $row['address'],
		'city' => $row['city'],
		'state' => $row['state'],
		'postal_code' => $row['zipcode'],
		'region' => $row['city'],
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
