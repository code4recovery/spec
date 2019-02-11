<?php

//database connection info, edit me!
$host		= 'localhost';
$user		= 'root';
$password	= '';
$database	= 'suffolk_county';

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
	$result = $pdo->query('SELECT * FROM `Table 1`');
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

$day_lookup = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

$separators = array('STE', 'SUITE', '#');

//fetch data
$return = array();
foreach ($result as $row) {

	//types
	$types = array();

	//build array
	$return[] = array(
		'slug' => $row['meeting_id'],
		'day' => array_search($row['new_day'], $day_lookup),
		'time' => $row['new_time'],
		'name' => $row['group_name'],
		'types' => $types,
		'address' => $row['locationAddress'],
		'city' => $row['locationCity'],
		'state' => $row['locationState'],
		'postal_code' => $row['locationZip'],
		'country' => 'US',
		'notes' => $row['new_note'],
	);

	dd($return);

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
