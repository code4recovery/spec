<?php

//customize these
define('DB_HOST', 'localhost');
define('DB_NAME', 'georgia');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

//make sure errors are being reported
error_reporting(E_ALL);

//connect to database
try {
    $pdo = new PDO('mysql:charset=UTF8;dbname=' . DB_NAME . ';host=' . DB_HOST, DB_USER, DB_PASSWORD);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

//error handling
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//select data
try {
  $meetings = $pdo->query('SELECT * FROM mg_meeting_export_V')
    ->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

//debug helper
function dd($obj) {
  echo '<pre>';
  print_r($obj);
  exit;
}

//make arrays from day and types
$meetings = array_map(function($meeting){
  $meeting['day'] = explode(',', $meeting['day']);
  $meeting['types'] = explode(',', $meeting['types']);
  return $meeting;
}, $meetings);

//encode JSON
$meetings = json_encode($meetings);
if (json_last_error()) {
  die('JSON error: ' . json_last_error_msg());
}

//make sure headers haven't already been sent, will cause error
if (headers_sent()) {
  die('Error: headers already sent!');
}

//output
header('Content-type: application/json; charset=utf-8');
echo $meetings;
