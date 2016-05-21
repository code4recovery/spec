<?php
//simple PHP script to output API for a basic database

//database connection info, edit me!
$server		= '127.0.0.1';
$username	= 'root';
$password	= '';
$database	= 'your_database_name';
$sql		= 'SELECT meetings.*, locations.* FROM meetings JOIN locations ON meetings.location_id = locations.id';

//send header and turn off error reporting so we can get a proper HTTP result
header('Content-type: application/json; charset=utf-8');
error_reporting(0);

//connect to database
if (empty($server)) error('$server variable is empty');
$link = mysql_connect($server, $username, $password) or error('could not connect to database server');
mysql_select_db($database, $link) or error('could not select database');
mysql_set_charset('UTF-8', $link);

//select data
if (empty($sql)) error('$sql variable is empty');
$result = mysql_query($sql, $link);
if (!$result) error(mysql_error($link));

//fetch data into array
$return = array();
while ($row = mysql_fetch_assoc($result)) $return[] = $row;
mysql_free_result($result);
mysql_close($link);

//return JSON
output($return);

//function to handle errors
function error($string) {
	output(array(
		'error' => $string,
	));
}

//function to output json
function output($array) {
	die(json_encode($array));
}