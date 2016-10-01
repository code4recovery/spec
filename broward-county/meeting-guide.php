<?php
//outputs meeting data for meeting guide app

//ini_set('display_errors', true);

$db_name = __DIR__ . '\fpdb\aa.mdb';

if (!file_exists($db_name)) die('database file ' . $db_name . ' does not exist!');

$connection = odbc_connect('Driver={Microsoft Access Driver (*.mdb)};Dbq=' . $db_name, null, null);

$result = odbc_exec($connection, 'SELECT
		meetings.meeting_id AS slug,
		time,
		dow AS day,
		meeting_type,
		notes,
		meeting_code,
		Name AS location,
		Add1 AS address,
		city,
		state,
		Zip AS postal_code,
		wheelChair,
		smoking,
		area_name AS region,
		mname AS name 
	FROM meeting_data');

$meetings = array();

$decode_types = array(
	
);

while ($row = odbc_fetch_array($result)) { 

	//decrement day by one
	$row['day'] = $row['day'] - 1;

	//build array of meeting codes
	$row['types'] = array();

	if ($row['meeting_type'] == '1') $row['types'][] = 'O';
	if ($row['meeting_type'] == '2') $row['types'][] = 'C';
	if ($row['wheelChair'] == '1') $row['types'][] = 'X';
	if ($row['smoking'] == '1') $row['types'][] = 'SM';
	$row['meeting_code'] = explode(' ', $row['meeting_code']);
	foreach ($row['meeting_code'] as $code) {
		switch ($code) {
			case 'AB':
				$row['types'][] = 'LIT';
				break;
			case 'BB':
				$row['types'][] = 'B';
				break;
			case 'BG':
				$row['types'][] = 'BE';
				break;
			case 'CB':
				$row['types'][] = 'LIT';
				break;
			case 'D':
				$row['types'][] = 'D';
				break;
			case 'DR':
				$row['types'][] = 'LIT';
				break;
			case 'FR':
				$row['types'][] = 'FR';
				break;
			case 'g':
				$row['types'][] = 'G';
				break;
			case 'GV':
				$row['types'][] = 'GR';
				break;
			case 'LS':
				$row['types'][] = 'LIT';
				break;
			case 'LT':
				$row['types'][] = 'LIT';
				break;
			case 'M':
				$row['types'][] = 'MED';
				break;
			case 'm':
				$row['types'][] = 'M';
				break;
			case 'PG':
				$row['types'][] = 'PG';
				break;
			case 'SH':
				$row['types'][] = 'S';
				break;
			case 'SP':
				$row['types'][] = 'SP';
				break;
			case 'SPD':
				$row['types'][] = 'SP';
				$row['types'][] = 'D';
				break;
			case 'SS':
				$row['types'][] = 'ST';
				break;
			case 'ST':
				$row['types'][] = 'ST';
				break;
			case 'STR':
				$row['types'][] = 'ST';
				$row['types'][] = 'TR';
				break;
			case 'TR':
				$row['types'][] = 'TR';
				break;
			case 'w':
				$row['types'][] = 'W';
				break;
			case 'YP':
				$row['types'][] = 'Y';
				break;
		}
	}
	
	unset($row['meeting_type']);
	unset($row['wheelChair']);
	unset($row['smoking']);
	unset($row['meeting_code']);
	$meetings[] = $row;
}

//$meetings = array_slice($meetings, 0, 5);

odbc_close($connection);

header('Content-type: application/json; charset=utf-8');

echo json_encode($meetings);