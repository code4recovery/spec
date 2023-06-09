<?php

//database connection info, edit me!
$host		= 'localhost';
$user		= 'root';
$password	= '';
$database	= 'nashville';

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
		m.groupid,
		m.wkday day,
		m.times time,
		m.type types,
		m.mcomments notes,
		GREATEST(m.modified, g.modified) updated,
		g.groupname name,
		g.address1 location,
		g.address2 address,
		g.city,
		g.state,
		g.zip postal_code,
		g.comments location_notes
	FROM meetings m
	JOIN groups g ON m.groupid = g.groupid');
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

$decode_days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

$decode_types = array(
	'0D' => array('O', 'D'),
	'OD' => array('O', 'D'),
	'OD OD' => array('O', 'D'),
	'OD  OD' => array('O', 'D'),
	'OD CD' => array('C', 'D'),
	'OD ALN' => array('D', 'AL-AN'),
	'LIT' => 'LIT',
	'CD' => 'C',
	'WMN' => 'W',
	'WMN BEG' => array('W', 'BE'),
	'OD AND SEPARATE BEG' => array('O', 'D', 'BE'),
	'AND BEG' => 'BE',
	'PLUS BEG MEETING' => 'BE',
	'PLUS ALN' => 'AL-AN',
	'BEG ALN' => array('BE', 'AL-AN'),
	'SP' => 'S',
	'TT' => 'ST',
	'BEG' => 'BE',
	'OS' => array('O', 'SP'),
	'OS & OD' => array('O', 'SP', 'D'),
	'OS-3RD SUNDAY' => array('O', 'SP'),
	'BB' => 'B',
	'LGBT, ALL WELCOME' => 'LGBTQ',
	'CS' => array('C', 'SP'),
	'MEN' => 'M',
	'MENS' => 'M',
	'ALN' => 'AL-AN',
	'GV' => 'GR',
	'GV ALN' => array('GR', 'AL-AN'),
	'BB ALN' => array('B', 'AL-AN'),
	'BEG WMN' => array('BE', 'W'),
	'LIT ALN' => array('LIT', 'AL-AN'),
	'OD ALANON AT 7:30' => array('LIT', 'AL-AN'),
	'MEDITATION' => 'MED',
	'BD' => array('H'),
	'WOMEN' => 'W',
	'WMN OD' => array('W', 'O', 'D'),
	'11TH STEP' => '11',
	'CDLT' => 'CAN',
	'BR' => '', //back room
	'SS' => 'ST',
	'DN' => '', //downstairs
	'GAY' => 'G',
	'DAILY REFLECTION' => 'DR',
	'DAILY REFLECTIONS LT' => 'DR',
	'LIVING SOBER' => 'LS',
	'GRAPEVINE' => 'GR',
	'AS BILL SEES IT' => 'ABSI',
);

//fetch data
$return = array();
foreach ($result as $row) {
	
	//trim all values
	$row = array_map('trim', $row);

	//format name in title case
	$name = ucwords(strtolower($row['name']));
	$name = str_replace('Aa ', 'AA ', $name);
	$name = str_replace('S.a.s./sober ', 'S.A.S./Sober ', $name);

	//day
	$row['day'] = array_search($row['day'], $decode_days);
	
	//types
	$types = array();
	$row['types'] = str_replace('+', ' ', $row['types']);
	$row['types'] = str_replace('*', '', $row['types']);
	$row['types'] = str_replace('.', '', $row['types']);
	$row['types'] = explode('/', $row['types']);
	foreach ($row['types'] as $type) {
		$type = trim($type);
		if (empty($type)) continue;
		if (!array_key_exists($type, $decode_types)) die($type);
		if (is_array($decode_types[$type])) {
			$types = array_merge($types, $decode_types[$type]);
		} else {
			$types[] = $decode_types[$type];
		}
	}

	//address
	if (empty($row['address'])) {
		$row['address'] = $row['location'];
	}

	//handle time
	if (strlen($row['time']) == 3) {
		$row['time'] = substr($row['time'], 0, 1) . ':' . substr($row['time'], 1, 2);
	} elseif (strlen($row['time']) == 4) {
		$row['time'] = substr($row['time'], 0, 2) . ':' . substr($row['time'], 2, 2);
	} else {
		continue;
	}

	//build array
	$return[] = array(
		'slug' => $row['groupid'] . $row['day'] . $row['time'],
		'day' => $row['day'],
		'name' => $name,
		'time' => $row['time'],
		'types' => array_unique($types),
		'location' => $row['location'],
		'address' => $row['address'],
		'city' => $row['city'],
		'state' => $row['state'],
		'postal_code' => $row['postal_code'],
		'country' => 'US',
		'region' => $row['city'],
		'notes' => $row['notes'],
		'updated' => date('Y-m-d H:i:s', strtotime($row['updated'])),
		'location_notes' => $row['location_notes'],
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
