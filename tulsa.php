<?php
//simple PHP script to output API for aaneok.org

//database connection info, edit me
$server = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'aa_tulsa';

//create some variables we're going to need
$meetings = array();
$conversion = array('123' => 'ST', 'ABSI' => 'LIT', 'BBS' => 'B', 'Beg' => 'BE', 'CTB' => 'LIT', 'Cndl' => 'CAN', 'DR' => 'LIT',
	'Dis' => 'D', 'GV' => 'GR', 'LS' => 'LIT', 'Lit' => 'LIT', 'New' => 'B', 'Spk' => 'SP', 'St11' => 'ST', 'Step' => 'ST',
	'Top' => 'D', 'Trad' => 'TR',
);

//connect to mysql
$db = mysql_connect($server, $username, $password) or die('Unable to connect to MySQL!');
mysql_select_db($database, $db) or die('Could not select database!');
	
//select meetings
$result = mysql_query('SELECT
		m.ID AS slug,
		m.day,
		m.time,
		m.isSmoking AS smoking,
		m.isMens AS men,
		m.isWomens AS women,
		m.isOpen AS open,
		m.Meeting_Comments AS notes,
		m.Group_ID AS location_slug,
		GREATEST(m.Meeting_Last_Update, l.Group_Last_Update) updated,
		l.Group_Name AS location,
		l.Group_Comments AS location_notes,
		l.Group_Address_1 AS address,
		l.Group_Address_2,
		l.Group_City AS city,
		l.Group_State AS state,
		l.Group_Zip AS postal_code,
		l.latitude,
		l.longitude,
		GROUP_CONCAT(m2t.MeetingType SEPARATOR ",") types
	FROM meetings m
	JOIN groups l ON m.Group_id = l.ID
	JOIN meetings_map_to_type m2t ON m.ID = m2t.MeetingID
	GROUP BY m.ID');

//loop through once and process each meeting
while ($row = mysql_fetch_assoc($result)) {
	
	//append Group_Address_2 to location_notes if present
	if (!empty($row['Group_Address_2'])) {
		$row['location_notes'] .= PHP_EOL . $row['Group_Address_2'];
	}
	
	//handle types
	$row['types'] = explode(',', $row['types']);
	$types = array();
	foreach ($row['types'] as $type) {
		if (array_key_exists($type, $conversion)) {
			$types[] = $conversion[$type];
		}
	}
	if ($row['smoking']) $types[] = 'SM';
	if ($row['men']) $types[] = 'M';
	if ($row['women']) $types[] = 'W';
	if ($row['open']) $types[] = 'O';
	
	$meetings[] = array(
		'name' => null,
		'slug' => $row['slug'],
		'notes' => $row['notes'],
		'updated' => $row['updated'],
		'url' => null,
		'time' => $row['time'],
		'day' => --$row['day'],
		'types' => $types,
		'address' => $row['address'],
		'city' => $row['city'],
		'state' => $row['state'],
		'postal_code' => $row['postal_code'],
		'country' => 'US',
		'latitude' => $row['latitude'],
		'longitude' => $row['longitude'],
		'region' => $row['city'],
		'timezone' => 'America/Chicago',
		'location' => $row['location'],
		'location_slug' => $row['location_slug'],
		'location_notes' => trim($row['location_notes']),
	);
}

//output json
header('Content-type: application/json; charset=utf-8');
echo json_encode($meetings);