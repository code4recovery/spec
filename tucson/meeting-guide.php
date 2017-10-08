<?php

//database connection info, edit me!
$host		= 'localhost';
$user		= 'root';
$password	= '';
$database	= 'tucson';

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
	$result = $pdo->query('SELECT
			m.Meeting_ID `slug`,
			(m.Day_ID - 1) `day`,
			m.Time_24 `time`,
			m.group_name2 `name`,
			m.meeting_notes, 
			m.location_notes,
			m.Gender_ID,
			i.interest,
			m.format,
			m.open_closed,
			m.wc_access,
			m.smoking,
			l.location_name `location`,
			l.address,
			l.city,
			l.zip `postal_code`,
			l.area `region`,
			l.map_notes
		FROM meeting_schedule m
		LEFT JOIN locations l ON m.Location_ID = l.Location_ID
		LEFT JOIN interest i ON m.Interest_ID = i.Interest_ID');
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

//fetch data
$return = array();
foreach ($result as $row) {
	
	//trim all values
	$row = array_map('trim', $row);
	
	$types = array();
	if ($row['Gender_ID'] == '1') $types[] = 'M';
	if ($row['Gender_ID'] == '2') $types[] = 'W';
	if ($row['interest'] == 'Spanish Speaking') $types[] = 'S';	
	if ($row['interest'] == 'American Indian') $types[] = 'N';	
	if ($row['interest'] == 'Young People') $types[] = 'Y';	
	if ($row['interest'] == 'Gay') $types[] = 'G';
	if ($row['interest'] == 'Lesbian') $types[] = 'L';
	if ($row['interest'] == 'Gay/Lesbian') $types[] = 'LGBTQ';
	if ($row['interest'] == 'Newcomer') $types[] = 'BE';
	//if ($row['interest'] == 'Seniors 50-plus') $types[] = '';
	if (stristr($row['format'], '11th Step')) $types[] = '11';
	if (stristr($row['format'], 'BB')) $types[] = 'B';
	if (stristr($row['format'], 'Big Book')) $types[] = 'B';
	if (stristr($row['format'], '12 & 12')) $types[] = 'ST';
	if (stristr($row['format'], 'Discussion')) $types[] = 'D';
	if (stristr($row['format'], 'As Bill Sees It')) $types[] = 'ASBI';
	if (stristr($row['format'], 'Newcomers')) $types[] = 'BE';
	if (stristr($row['format'], 'Literature')) $types[] = 'LIT';
	if (stristr($row['format'], 'Living Sober')) $types[] = 'LS';
	if (stristr($row['format'], 'Meditation')) $types[] = 'MED';
	if (stristr($row['format'], 'Speaker')) $types[] = 'SP';
	if (stristr($row['format'], 'Step Study')) $types[] = 'ST';
	if (stristr($row['format'], 'Traditions')) $types[] = 'T';
	if ($row['wc_access'] == '(WH)') $types[] = 'X';	
	if ($row['open_closed'] == '(O)') $types[] = 'O';	
	if ($row['open_closed'] == '(C)') $types[] = 'C';	
	if ($row['smoking'] == '(S)') $types[] = 'SM';	
	if ($row['smoking'] == '(NS)') $types[] = 'NS';	
	$types = array_unique($types);
		
	//notes
	$notes = array();
	if (!empty($row['meeting_notes'])) $notes[] = $row['meeting_notes'];
	if (!empty($row['map_notes'])) $notes[] = $row['map_notes'];
	if (!empty($row['location_notes'])) $notes[] = $row['location_notes'];
	$notes = implode("\n", $notes);
	
	//build array
	$return[] = array(
		'slug' => $row['slug'],
		'day' => intval($row['day']),
		'name' => $row['name'],
		'time' => $row['time'],
		'types' => $types,
		'address' => $row['address'],
		'city' => $row['city'],
		'state' => 'AZ',
		'postal_code' => $row['postal_code'],
		'country' => 'US',
		'region' => $row['region'],
		'notes' => $notes,
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
