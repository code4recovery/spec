<?php

//database connection info, edit me!
$host		= 'localhost';
$user		= 'root';
$password	= '';
$database	= 'aa_san_antonio';

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
		m.vid "slug",
		(SELECT GROUP_CONCAT(t.name) FROM csosataxonomy_term_data t JOIN csosataxonomy_index i ON t.tid = i.tid WHERE t.vid = 3 AND i.nid = m.nid) day,
		(SELECT GROUP_CONCAT(t.name) FROM csosataxonomy_term_data t JOIN csosataxonomy_index i ON t.tid = i.tid WHERE t.vid = 5 AND i.nid = m.nid) time,
		m.title "name",
		n.field_meeting_note_value "notes",
		(SELECT GROUP_CONCAT(t.name) FROM csosataxonomy_term_data t JOIN csosataxonomy_index i ON t.tid = i.tid WHERE t.vid = 2 AND i.nid = m.nid) types,
		m.changed "updated",
		l.name "location",
		l.street "address",
		l.city "city",
		l.province "state",
		l.postal_code,
		l.country,
		l.additional "location_notes",
		l.latitude,
		l.longitude
	FROM csosanode m
	JOIN csosalocation_instance li ON m.vid = li.vid
	JOIN csosalocation l on l.lid = li.lid
	LEFT JOIN csosafield_data_field_meeting_note n ON n.revision_id = m.vid
	WHERE m.type = "meeting"');
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

$type_lookup = array(
	'Babysitting' => 'BA',
	'Beginners' => 'BE',
	'Big Book' => 'B',
	//'Bilingual' => ''
	//'Book Study' => '',
	'Closed' => 'C',
	'Discussion' => 'D',
	'LGBT' => 'LGBTQ',
	'Literature Study' => 'LIT',
	'Men\'s' => 'M',
	'No Smoking' => 'NS',
	'Open' => 'O',
	//'Podium' => ''
	//'Senior'
	'Smoking' => 'SM',
	'Spanish' => 'S',
	'Speaker\'s' => 'SP',
	'Step Study' => 'ST',
	//'Study-Three Legacies' => 
	'Twelve & Twelve' => '12x12',
	'Wheelchair Accessible' => 'X',
	'Women' => 'W',
	'Young People\'s' => 'YP',
);

$day_lookup = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

//fetch data
$return = array();
foreach ($result as $row) {

	//turn comma-separated day into array of ints 0-6
	$row['day'] = array_map(function($day) use ($day_lookup) {
		return array_search($day, $day_lookup);
	}, explode(',', $row['day']));

	//turn comma-separated types into array of codes
	$row['types'] = array_map(function($type) use ($type_lookup) {
		return $type_lookup[$type];
	}, array_filter(explode(',', $row['types']), function($key) use ($type_lookup) {
		return array_key_exists($key, $type_lookup);
	}));

	//format time
	$row['time'] = date('G:i', strtotime($row['time']));

	//format updated
	$row['updated'] = date('c', $row['updated']);

	//clean up numeric keys
	foreach ($row as $key => $value) {
	    if (is_int($key)) {
	        unset($row[$key]);
	    }
	}

	//build array
	$return[] = $row;
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
