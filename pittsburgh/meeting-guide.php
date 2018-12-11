<?php

//database connection info, edit me!
$host		= 'localhost';
$user		= 'root';
$password	= '';
$database	= 'pittsburgh';

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
	$result = $pdo->query('SELECT * FROM meetings');
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

$day_lookup = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');


//fetch data
$return = array();
foreach ($result as $row) {

	//format time
	$time = str_pad($row['MTGTIME'], 4, '0', STR_PAD_LEFT);

	//prepare types
	$types = array();
	if (stristr($row['TYPE1'], 'closed')) {
		$types[] = 'C';
	} elseif (stristr($row['TYPE1'], 'open')) {
		$types[] = 'O';
	}
	if (stristr($row['TYPE1'], 'discussion')) $types[] = 'D';
	if (stristr($row['TYPE1'], '12&12')) $types[] = '12x12';
	if (stristr($row['TYPE1'], 'as bill sees it')) $types[] = 'ABSI';
	if (stristr($row['TYPE1'], 'beginner')) $types[] = 'BE';
	if (stristr($row['TYPE1'], 'big book')) $types[] = 'BB';
	if (stristr($row['TYPE1'], 'speaker')) $types[] = 'SP';
	if (stristr($row['TYPE1'], 'step')) $types[] = 'ST';
	if (stristr($row['TYPE1'], 'tradition')) $types[] = 'T';
	if (stristr($row['TYPE1'], 'women') || $row['ATTR3'] == 'Women' || stristr($row['MTGNAME'], 'women')) {
		$types[] = 'W';
	} elseif (stristr($row['TYPE1'], 'men') || $row['ATTR3'] == 'Men' || stristr($row['TYPE1'], 'men`s')) {
		$types[] = 'M';
	}
	if (stristr($row['ATTR3'], 'young people')) $types[] = 'Y';
	if (stristr($row['ATTR2'], 'american indian') || stristr($row['ATTR3'], 'american indian')) $types[] = 'N';
	if (stristr($row['ATTR2'], 'accessible')) $types[] = 'X';
	if (stristr($row['ATTR2'], 'babysitting')) $types[] = 'BA';
	if (stristr($row['ATTR2'], 'freethinkers')) $types[] = 'A';

	//prepare notes
	$notes = [];
	if ($row['TYPE2']) $notes[] = $row['TYPE2'];
	if ($row['TYPE3']) $notes[] = $row['TYPE3'];
	if ($row['ADDR2']) $notes[] = $row['ADDR2'];
	if ($row['ADDR3']) $notes[] = $row['ADDR3'];

	//build array
	$return[] = array(
		'slug' => $row['SLUG'],
		'day' => array_search($row['MTGDAY'], $day_lookup),
		'time' => substr($time, 0, 2) . ':' . substr($time, 2, 2),
		'name' => $row['MTGNAME'],
		'types' => $types,
		'location' => $row['MTGLOC'],
		'address' => $row['ADDR1'],
		'city' => $row['MTGAREA'],
		'postal_code' => $row['ZIP'],
		'country' => 'US',
		'notes' => trim(implode("\n", $notes)),
		'updated' => $row['UPDATED'],
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
