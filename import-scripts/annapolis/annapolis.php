<?php

//database connection info, edit me!
$host		= '127.0.0.1';
$user		= 'root';
$password	= '';
$database	= 'annapolis';

//sql query
$sql		= 'SELECT 
	meeting.id,
	meeting.day,
	meeting.time,
	meeting.language,
	meeting.mtg_notes,
	meeting.Code,
	type.mtg_type,
	aagroup.gp_name,
	location.loc_name,
	location.addr1,
	location.addr2,
	location.city,
	location.notes
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

//type definitions
$decode_types = array(
	'A' => 'AL-AN',	//concurrent with al-anon
	'C' => 'C',		//closed
	'G' => 'W',		//women
	'M' => 'M',		//men (doesn't exist yet)
	//'S' => 'S', 	//no court slips (don't have a type for this)
	'T' => 'AL',	//alateen
	'W' => 'X',		//wheelchair accessible
	'Beginner' => 'BE',
	'Beginners Step' => 'BE',
	'Discussion' => 'D',
	'Discussion - Grapevine' => 'GR',
	'Korean' => 'KOR',
	'Lit-As Bill Sees It' => 'LIT',
	'Lit-Big Book' => 'LIT',
	'Lit-Came To Believe' => 'LIT',
	'Lit-Daily Reflections' => 'LIT',
	'Lit-How It Works' => 'LIT',
	'Literature' => 'LIT',
	'Meditation' => 'MED',
	'Spanish' => 'S',
	'Speaker' => 'SP',
	'Step' => 'ST',
	'Spanish' => 'S',
);

//fetch data
$return = array();
foreach ($result as $row) {
	
	//trim all values
	$row = array_map('trim', $row);
	
	//set types
	$types = array();
	if (array_key_exists($row['language'], $decode_types)) $types[] = $decode_types[$row['language']];
	if (array_key_exists($row['mtg_type'], $decode_types)) $types[] = $decode_types[$row['mtg_type']];
	$codes = str_split($row['Code']);
	foreach ($codes as $code) {
		if (array_key_exists($code, $decode_types)) $types[] = $decode_types[$code];
	}
	
	//append address 2 to location notes
	if (!empty($row['addr2'])) {
		if (!empty($row['notes'])) $row['notes'] .= '<br>';
		$row['notes'] .= $row['addr2'];
	}
	
	//build array
	$return[] = array(
		'slug' => $row['id'],
		'day' => $row['day'],
		'time' => substr($row['time'], 0, 2) . ':' . substr($row['time'], 2, 2),
		'notes' => $row['mtg_notes'],
		'types' => $types,
		'name' => $row['gp_name'],
		'location' => $row['loc_name'],
		'address' => $row['addr1'],
		'city' => $row['city'],
		'state' => 'MD',
		'country' => 'US',
		'location_notes' => $row['notes'],
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
