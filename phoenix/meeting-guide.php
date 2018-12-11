<?php

//database connection info, edit me!
$host		= 'localhost';
$user		= 'root';
$password	= '';
$database	= 'phoenix';

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
	$result = $pdo->query('SELECT * FROM wp_SRI_MEETINGS');
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

/*
CODES:
[O]=Open;
[C]=Closed;
[BB]=Big Book;
[BG]=Newcomer's;
[GL]=Gay-Lesbian;
[SF]=Senior-Friendly;
[YP]=Young People;
[M]=Men;
[W]=Women;
[K]=Smoking OK;
[NA]=Native American;
[D]=Discussion;
[S]=Speaker;
[T]=Step Study;
[X]=12+12 Study;
[%]=Wheelchair Access
*/

$type_lookup = array(
	'O' => 'O',
	'C' => 'C',
	'BB' => 'BB',
	'BG' => 'BE',
	'GL' => 'LGBTQ',
	'SF' => 'SF',
	'YP' => 'Y',
	'M' => 'M',
	'W' => 'W',
	'K' => 'SM',
	'NA' => 'N',
	'D' => 'D',
	'S' => 'SP',
	'T' => 'ST',
	'X' => '12x12',
	'%' => 'X',
);

$day_lookup = array('SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT');

$separators = array('STE', 'SUITE', '#');

//fetch data
$return = array();
foreach ($result as $row) {

	//split the types on a space and look them up
	$types = array_map(function($key) use ($type_lookup) {
		return @$type_lookup[$key];
	}, explode(' ', $row['accomodations']));

	//see if it's spanish
	if (stristr($row['notes'], 'spanish')) {
		$types[] = 'S';
	}

	//move any suite or room numbers to the notes for proper geocoding
	foreach ($separators as $separator) {
		if ($start = strpos($row['address'], $separator)) {
			$row['notes'] = substr($row['address'], $start) . "\n" . $row['notes'];
			$row['address'] = trim(substr($row['address'], 0, $start));
		}
	}

	//build array
	$return[] = array(
		'slug' => $row['id'],
		'day' => array_search($row['weekday'], $day_lookup),
		'time' => $row['time_of_day'],
		'name' => $row['title'],
		'types' => $types,
		'address' => $row['address'],
		'city' => $row['city'],
		'postal_code' => $row['zip_code'],
		'country' => 'US',
		'notes' => $row['notes'],
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
