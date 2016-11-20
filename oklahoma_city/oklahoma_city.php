<?php

//database connection info, edit me!
$host		= '127.0.0.1';
$user		= 'root';
$password	= '';
$database	= 'oklahoma_city';

//sql query
$sql		= 'SELECT * FROM meetingsfordisplay';

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
	'As Bill Sees It' => 'LIT',	
	'Beginners' => 'BE',	
	'Big Book' => 'B',	
	'Came to Believe' => 'LIT',	
	'Daily Reflections' => 'LIT',	
	'Discussion' => 'D',	
	'Grapevine' => 'GR',	
	'Literature' => 'LIT',	
	'Living Sober' => 'LIT',	
	'Speaker' => 'SP',	
	'Step Study' => 'ST',	
	'Topic' => 'D',	
	'Traditions' => 'TR',	
);

//fetch data
$return = array();
foreach ($result as $row) {
	
	//trim all values
	$row = array_map('trim', $row);
	
	//make sure day is valid
	if (!array_key_exists($row['Day'], $decode_days)) continue;
	
	//set types
	$types = array();
	$types[] = ($row['Open'] == 'Y') ? 'O' : 'C';
	if ($row['Smoking'] == 'Y') $types[] = 'SM';
	if ($row['Sex'] == 'W') $types[] = 'W';
	if ($row['Sex'] == 'M') $types[] = 'M';
	if (!empty($row['Spanish'])) $types[] = 'S';
	if (!empty($row['Disability'])) $types[] = 'X';
	if (!empty($row['Babysit'])) $types[] = 'BA';

	//build array
	$return[] = array(
		'slug' => md5(implode('-', array($row['GroupName'], $row['Day'], $row['Time'], $row['Add1']))),
		'day' => $decode_days[$row['Day']],
		'name' => $row['GroupName'],
		'notes' => $row['Add2'],
		'time' => $row['Time'],
		'types' => $types,
		'address' => $row['Add1'],
		'city' => $row['City'],
		'postal_code' => $row['ZIP'],
		'region' => $row['City'],
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
