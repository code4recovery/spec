<?php
//outputs meeting data for meeting guide app

ini_set('display_errors', true);

$db_name = '/path/to/database.mdb';

if (!file_exists($db_name)) die('database file ' . $db_name . ' does not exist!');

if (!function_exists('odbc_connect')) die('ODBC driver not installed');

$connection = odbc_connect('Driver={Microsoft Access Driver (*.mdb)};Dbq=' . $db_name, null, null);

$delimiters = array(
	' suite',
	' (btw',
	' btw',
	' c/s',
	' @',
	' (',
	' between',
);

$result = odbc_exec($connection, 'SELECT
		DK AS day,
		city,
		time,
		TYPE AS types,
		[MEETING NAME] AS [name],
		address,
		ZIP AS postal_code,
		[LAST CHANGE] AS updated,
		[Hndcpd Eqpd] AS handicapped
	FROM [JAN99_981213]
	WHERE [address] IS NOT NULL');

$meetings = $slugs = array();

while ($row = odbc_fetch_array($result)) { 

	//decrement day by one
	$row['day'] = $row['day'] - 1;

	//format time
	$row['time'] = substr($row['time'], 11, 5);

	//build array of meeting codes
	if ($row['types'] == '(C)') {
		$row['types'] = array('C');
	} elseif ($row['types'] == '(G)') {
		$row['types'] = array('G');
	} elseif ($row['types'] == '(I)') {
		//$row['types'] = array('C');
	} elseif ($row['types'] == '(Y)') {
		$row['types'] = array('Y');
	} elseif ($row['types'] == '(~)') {
		$row['types'] = array('S');
	} else {
		$row['types'] = array('O');
	}

	if ($row['handicapped'] == 1) {
		$row['types'][] = 'X';
	}
	unset($row['handicapped']);

	//title case name
	$row['name'] = ucwords(strtolower($row['name']));
	$row['name'] = str_replace('Aa', 'AA', $row['name']);
	$row['name'] = str_replace('Dp ', 'DP ', $row['name']);

	//title case city
	$row['city'] = ucwords(strtolower($row['city']));
	$row['region'] = $row['city'];

	//extra information for geocoder
	$row['state'] = 'CA';
	$row['country'] = 'USA';

	//create a quasi-unique slug from day, time, city, name, and address
	$row['slug'] = md5(serialize(array($row['day'], $row['time'], $row['city'], $row['name'], $row['address'])));
	$slugs[] = $row['slug'];

	//parse extra stuff from address
	foreach ($delimiters as $delimiter) {
		$pos = stripos($row['address'], $delimiter);
		if ($pos !== false) {
			$row['notes'] = substr($row['address'], $pos + 1);
			$row['address'] = substr($row['address'], 0, $pos);
		}
	}

	//handle special cases
	if (($row['name'] == 'Right Start') && in_array($row['city'], array('Placentia', 'Yorba Linda'))) {
		continue; //added after
	}

	$meetings[] = $row;
}

odbc_close($connection);

//add 'right start' meeting
if ((date('n') > 4) && (date('n') < 10)) {
	$meetings[] = array(
		'day' => 0,
		'time' => '08:30',
		'name' => 'Right Start',
		'city' => 'Placentia',
		'region' => 'Placentia',
		'state' => 'CA',
		'country' => 'USA',
		'address' => '1200 Carlsbad St.',
		'location' => 'Los Vaqueros Park',
		'notes' => 'October - April at 19045 E. Yorba Linda Bl., Yorba Linda, CA (Congregation Beth Meir Synagogue), May - September at Los Vaqueros Park @ 1200 Carlsbad St.',
	);
} else {
	$meetings[] = array(
		'day' => 0,
		'time' => '08:30',
		'name' => 'Right Start',
		'city' => 'Yorba Linda',
		'region' => 'Yorba Linda',
		'state' => 'CA',
		'country' => 'USA',
		'address' => '19045 E. Yorba Linda Bl.',
		'location' => 'Congregation Beth Meir Synagogue',
		'notes' => 'October - April at 19045 E. Yorba Linda Bl., Yorba Linda, CA (Congregation Beth Meir Synagogue), May - September at Los Vaqueros Park @ 1200 Carlsbad St.',
	);
}

if (!headers_sent()) {
	header('Content-type: application/json; charset=utf-8');
}

echo json_encode($meetings);

//debugging
function dd($content) {
	echo '<pre>';
	print_r($content);
	exit;
}