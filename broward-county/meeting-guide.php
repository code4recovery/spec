<?php

//debug function
function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

if (file_exists('wp-config.php')) {
	include('wp-config.php');
} else {
	define('DB_NAME', 'broward');
	define('DB_USER', 'root');
	define('DB_PASSWORD', '');
	define('DB_HOST', 'localhost');
	$table_prefix  = 'aabci_';
}

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
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

//select data
try {
	$meetings = $pdo->query('SELECT ID, post_title, post_modified_gmt FROM ' . $table_prefix . 'posts WHERE post_type = "meetings" AND post_status = "publish"')->fetchAll();
	$locations = $pdo->query('SELECT ID, post_title, post_modified_gmt FROM ' . $table_prefix . 'posts WHERE post_type = "locations" AND post_status = "publish"')->fetchAll();
	$meta = $pdo->query('SELECT post_id, meta_key, meta_value FROM ' . $table_prefix . 'postmeta WHERE post_id IN (SELECT ID FROM ' . $table_prefix . 'posts WHERE post_type IN ("meetings", "locations"))')->fetchAll();
} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

$meetings = array_map(function($meeting){
	return $meeting;
}, $meetings);

dd($meetings);

